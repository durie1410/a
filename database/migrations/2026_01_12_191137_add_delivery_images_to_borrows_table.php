<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryImagesToBorrowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            // Thêm các cột lưu ảnh khi khách nhận sách
            if (!Schema::hasColumn('borrows', 'anh_bia_truoc')) {
                $table->text('anh_bia_truoc')->nullable()->comment('Ảnh bìa trước khi nhận sách');
            }
            if (!Schema::hasColumn('borrows', 'anh_bia_sau')) {
                $table->text('anh_bia_sau')->nullable()->comment('Ảnh bìa sau khi nhận sách');
            }
            if (!Schema::hasColumn('borrows', 'anh_gay_sach')) {
                $table->text('anh_gay_sach')->nullable()->comment('Ảnh gáy sách hoặc trang lỗi khi nhận sách');
            }
            // Thêm cột lưu thời gian chuyển sang trạng thái "Chờ khách xác nhận"
            if (!Schema::hasColumn('borrows', 'ngay_cho_xac_nhan_nhan')) {
                $table->timestamp('ngay_cho_xac_nhan_nhan')->nullable()->comment('Thời gian chuyển sang trạng thái chờ khách xác nhận nhận sách');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['anh_bia_truoc', 'anh_bia_sau', 'anh_gay_sach', 'ngay_cho_xac_nhan_nhan']);
        });
    }
}
