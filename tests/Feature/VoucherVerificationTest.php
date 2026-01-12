<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reader;
use App\Models\Voucher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class VoucherVerificationTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we have a user and reader for testing
        $email = 'test_voucher_user@library.com';
        $this->user = User::where('email', $email)->first();

        if (!$this->user) {
            $this->user = User::create([
                'name' => 'Test Voucher User',
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'user'
            ]);

            Reader::create([
                'user_id' => $this->user->id,
                'ho_ten' => 'Test Voucher User',
                'email' => $email,
                'so_dien_thoai' => '0999888777',
                'so_the_doc_gia' => 'TEST' . time(),
                'trang_thai' => 'Hoat dong',
                'ngay_sinh' => '2000-01-01',
                'gioi_tinh' => 'Nam',
                'dia_chi' => '123 Test Street'
            ]);
        }
    }

    public function test_apply_voucher_successfully()
    {
        // 2. Mock a request to apply voucher
        $response = $this->actingAs($this->user)
            ->postJson(route('borrow-cart.apply-voucher'), [
                'voucher_code' => 'WELCOME50',
                'total_amount' => 100000
            ]);

        // 3. Assert response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'voucher' => [
                    'code' => 'WELCOME50',
                    'type' => 'percentage',
                    'discount_value' => 50,
                ],
            ]);

        // Check calculation logic
        $data = $response->json();
        $this->assertEquals(50000, $data['discount_amount']);
        $this->assertEquals(50000, $data['final_amount']);
    }

    public function test_apply_invalid_voucher()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('borrow-cart.apply-voucher'), [
                'voucher_code' => 'INVALID123',
                'total_amount' => 100000
            ]);

        $response->assertStatus(404);
    }

    public function test_apply_fixed_voucher()
    {
        // FREESHIP: Giảm 20k
        $response = $this->actingAs($this->user)
            ->postJson(route('borrow-cart.apply-voucher'), [
                'voucher_code' => 'FREESHIP',
                'total_amount' => 60000
            ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(20000, $data['discount_amount']);
        $this->assertEquals(40000, $data['final_amount']);
    }

    public function test_apply_voucher_min_order_failed()
    {
        // FREESHIP requires 50k
        $response = $this->actingAs($this->user)
            ->postJson(route('borrow-cart.apply-voucher'), [
                'voucher_code' => 'FREESHIP',
                'total_amount' => 40000
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);
    }
}
