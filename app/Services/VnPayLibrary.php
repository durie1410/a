<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VnPayLibrary
{
    private $requestData = [];
    private $responseData = [];

    /**
     * Thêm dữ liệu request
     */
    public function addRequestData($key, $value)
    {
        if (!empty($value)) {
            $this->requestData[$key] = $value;
        }
    }

    /**
     * Thêm dữ liệu response
     */
    public function addResponseData($key, $value)
    {
        if (!empty($value)) {
            $this->responseData[$key] = $value;
        }
    }

    /**
     * Lấy dữ liệu response theo key
     */
    public function getResponseData($key)
    {
        return $this->responseData[$key] ?? '';
    }

    /**
     * Lấy địa chỉ IP của client
     */
    public function getIpAddress(Request $request)
    {
        try {
            $ipAddress = $request->ip();
            
            // Kiểm tra nếu là IPv6 localhost, chuyển sang IPv4
            if ($ipAddress === '::1') {
                return '127.0.0.1';
            }
            
            // Nếu có X-Forwarded-For header (qua proxy)
            if ($request->header('X-Forwarded-For')) {
                $ipAddress = explode(',', $request->header('X-Forwarded-For'))[0];
            }
            
            return $ipAddress;
        } catch (\Exception $e) {
            Log::error('Error getting IP address: ' . $e->getMessage());
            return '127.0.0.1';
        }
    }

    /**
     * Tạo URL thanh toán
     */
    public function createRequestUrl($baseUrl, $hashSecret)
    {
        // Sắp xếp dữ liệu theo thứ tự alphabet
        ksort($this->requestData);
        
        $query = '';
        $hashData = '';
        $i = 0;
        
        foreach ($this->requestData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . $key . "=" . $value;
            } else {
                $hashData .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        
        // Bỏ dấu & thừa ở cuối query string
        $query = rtrim($query, '&');
        
        $vnpUrl = $baseUrl . "?" . $query;
        
        if (!empty($hashSecret)) {
            // Tạo secure hash từ raw data (không encode)
            $vnpSecureHash = hash_hmac('sha512', $hashData, $hashSecret);
            $vnpUrl .= '&vnp_SecureHash=' . $vnpSecureHash;
            
            // Debug logging
            Log::info('VNPay Create Payment URL', [
                'hash_data' => $hashData,
                'hash_secret_length' => strlen($hashSecret),
                'secure_hash' => $vnpSecureHash
            ]);
        }
        
        return $vnpUrl;
    }

    /**
     * Xác thực chữ ký từ VnPay
     */
    public function validateSignature($inputHash, $secretKey)
    {
        $rspRaw = $this->getResponseDataString();
        $myChecksum = hash_hmac('sha512', $rspRaw, $secretKey);
        
        // Debug logging
        Log::info('VNPay Validate Signature', [
            'response_data_string' => $rspRaw,
            'input_hash' => $inputHash,
            'my_checksum' => $myChecksum,
            'is_valid' => strcasecmp($myChecksum, $inputHash) === 0,
            'secret_key_length' => strlen($secretKey)
        ]);
        
        return strcasecmp($myChecksum, $inputHash) === 0;
    }

    /**
     * Lấy toàn bộ response data dưới dạng query string
     */
    private function getResponseDataString()
    {
        $data = $this->responseData;
        
        // Loại bỏ các trường không cần thiết
        unset($data['vnp_SecureHashType']);
        unset($data['vnp_SecureHash']);
        
        // Sắp xếp theo alphabet
        ksort($data);
        
        $query = '';
        $i = 0;
        
        foreach ($data as $key => $value) {
            if ($i == 1) {
                $query .= '&' . $key . "=" . $value;
            } else {
                $query .= $key . "=" . $value;
                $i = 1;
            }
        }
        
        return $query;
    }

    /**
     * Lấy toàn bộ response data từ VnPay callback
     */
    public function getFullResponseData(Request $request, $hashSecret)
    {
        $vnPay = new VnPayLibrary();
        
        foreach ($request->all() as $key => $value) {
            if (!empty($key) && substr($key, 0, 4) === 'vnp_') {
                $vnPay->addResponseData($key, $value);
            }
        }
        
        $orderId = $vnPay->getResponseData('vnp_TxnRef');
        $vnPayTranId = $vnPay->getResponseData('vnp_TransactionNo');
        $vnpResponseCode = $vnPay->getResponseData('vnp_ResponseCode');
        $orderInfo = $vnPay->getResponseData('vnp_OrderInfo');
        $vnpAmount = $vnPay->getResponseData('vnp_Amount') / 100; // Chia 100 vì VnPay nhân với 100
        
        // Hash của dữ liệu trả về
        $vnpSecureHash = $request->input('vnp_SecureHash');
        
        // Kiểm tra chữ ký
        $checkSignature = $vnPay->validateSignature($vnpSecureHash, $hashSecret);
        
        if (!$checkSignature) {
            return [
                'success' => false,
                'message' => 'Chữ ký không hợp lệ'
            ];
        }
        
        return [
            'success' => true,
            'payment_method' => 'VnPay',
            'order_description' => $orderInfo,
            'order_id' => $orderId,
            'payment_id' => $vnPayTranId,
            'transaction_id' => $vnPayTranId,
            'token' => $vnpSecureHash,
            'vnpay_response_code' => $vnpResponseCode,
            'amount' => $vnpAmount,
            'bank_code' => $vnPay->getResponseData('vnp_BankCode'),
            'card_type' => $vnPay->getResponseData('vnp_CardType'),
            'pay_date' => $vnPay->getResponseData('vnp_PayDate'),
        ];
    }
}

