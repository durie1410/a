<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFailureFieldsToShippingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_logs', function (Blueprint $table) {
            $table->string('failure_reason')->nullable()->after('ngay_that_bai_giao_hang')->comment('Lý do thất bại: loi_khach_hang hoặc loi_thu_vien');
            $table->string('failure_proof_image')->nullable()->after('failure_reason')->comment('Ảnh minh chứng giao hàng thất bại');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_logs', function (Blueprint $table) {
            $table->dropColumn(['failure_reason', 'failure_proof_image']);
        });
    }
}
