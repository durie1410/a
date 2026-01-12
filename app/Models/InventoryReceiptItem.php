<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'book_id',
        'quantity',
        'storage_location',
        'storage_type',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function receipt()
    {
        return $this->belongsTo(InventoryReceipt::class, 'receipt_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
