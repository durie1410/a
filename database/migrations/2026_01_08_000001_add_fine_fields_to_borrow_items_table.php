<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('borrow_items', function (Blueprint $row) {
            if (!Schema::hasColumn('borrow_items', 'tien_phat')) {
                $row->decimal('tien_phat', 15, 2)->default(0)->after('tien_ship');
            }
            if (!Schema::hasColumn('borrow_items', 'tinh_trang_sach_cuoi')) {
                $row->string('tinh_trang_sach_cuoi')->nullable()->after('tien_phat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_items', function (Blueprint $row) {
            $row->dropColumn(['tien_phat', 'tinh_trang_sach_cuoi']);
        });
    }
};
