<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, Product>  $products
     */
    public function __construct(
        public readonly Collection $products,
        public readonly int $threshold = 10,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Low Stock Alert — ' . $this->products->count() . ' product(s) need restocking',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low_stock_alert',
        );
    }
}
