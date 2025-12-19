<?php

namespace App\Models;

use App\Enums\BookingPayment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'type',
        'payment_method',
        'status',
        'reference',
        'note',
        'paid_at',
        'qr_image_url',
        'qr_transaction_id',
        'payment_number',
        'qr_response',
        'qr_callback_data',
    ];


    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'type' => PaymentType::class,
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'qr_response' => 'array',
        'qr_callback_data' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopePaid($query)
    {
        $query->where('status', PaymentStatus::PAID);
    }


    public static function booted(): void
    {
        static::saved(function (Payment $payment) {
            $booking = $payment->booking;
            $paidAmount = $booking->payments()->where('status', PaymentStatus::PAID)->sum('amount');

            $booking->update([
                'deposit_amount' => $paidAmount,
                'payment_status' =>$paidAmount > 0
                    ? ($paidAmount === $booking->total_price ? BookingPayment::PAID  : BookingPayment::PARTIALLY_PAID)
                    : BookingPayment::PENDING,
            ]);
        });
    }
}
