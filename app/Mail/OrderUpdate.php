<?php
namespace App\Mail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class OrderUpdate extends Mailable {
    use Queueable, SerializesModels;
    public function __construct(public int $orderId) {}
    public function build(): static { $order=Order::with('items')->findOrFail($this->orderId); return $this->subject('Order #'.$order->id.' — '.str_replace('_',' ',$order->status))->view('emails.order',['order'=>$order]); }
}
