<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Xóa dữ liệu cũ để tránh trùng lặp
        DB::table('vouchers')->truncate();

        $vouchers = [
            [
                'ma' => 'WELCOME50',
                'loai' => 'percentage',
                'gia_tri' => 50, // 50%
                'so_luong' => 100,
                'mo_ta' => 'Giảm 50% cho đơn hàng đầu tiên',
                'don_toi_thieu' => 0,
                'ngay_bat_dau' => Carbon::now()->subDays(1),
                'ngay_ket_thuc' => Carbon::now()->addMonths(1),
                'kich_hoat' => 1,
                'trang_thai' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ma' => 'FREESHIP',
                'loai' => 'fixed',
                'gia_tri' => 20000, // 20k
                'so_luong' => 50,
                'mo_ta' => 'Giảm 20k phí vận chuyển',
                'don_toi_thieu' => 50000,
                'ngay_bat_dau' => Carbon::now()->subDays(1),
                'ngay_ket_thuc' => Carbon::now()->addMonths(1),
                'kich_hoat' => 1,
                'trang_thai' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ma' => 'TEST10',
                'loai' => 'percentage',
                'gia_tri' => 10, // 10%
                'so_luong' => 100,
                'mo_ta' => 'Mã test giảm 10%',
                'don_toi_thieu' => 10000,
                'ngay_bat_dau' => Carbon::now()->subDays(1),
                'ngay_ket_thuc' => Carbon::now()->addMonths(1),
                'kich_hoat' => 1,
                'trang_thai' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('vouchers')->insert($vouchers);

        $this->command->info('Đã tạo 3 voucher mẫu thành công!');
    }
}
