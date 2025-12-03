<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingLog extends Model
{
    protected $table = 'shipping_logs';

    protected $fillable = [
        'borrow_id',
        'status',
        'receiver_note',
        'shipper_note',
        'proof_image',
        'delivered_at',
        'shipper_name',
        'shipper_phone',
    ];

    public function borrow()
    {
        return $this->belongsTo(Borrow::class, 'borrow_id');
    }

    public function item()
    {
        return $this->belongsTo(BorrowItem::class, 'borrow_item_id');
    }
}

