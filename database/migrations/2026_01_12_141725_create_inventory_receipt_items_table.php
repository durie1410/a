<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryReceiptItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_id'); // ID phiếu nhập kho
            $table->unsignedBigInteger('book_id'); // ID sách
            $table->integer('quantity'); // Số lượng nhập
            $table->string('storage_location', 100); // Vị trí lưu trữ
            $table->enum('storage_type', ['Kho', 'Trung bay'])->default('Kho'); // Loại lưu trữ
            $table->decimal('unit_price', 10, 2)->nullable(); // Giá mua đơn vị
            $table->decimal('total_price', 10, 2)->nullable(); // Tổng giá
            $table->text('notes')->nullable(); // Ghi chú cho từng sách
            $table->timestamps();

            $table->foreign('receipt_id')->references('id')->on('inventory_receipts')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->index(['receipt_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_receipt_items');
    }
}
