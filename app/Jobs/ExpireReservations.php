<?php
namespace App\Jobs;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Services\Payments;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
class ExpireReservations implements ShouldQueue {
    use Queueable;
    public function handle(Payments $payments): void {
        Order::where('status','pending_payment')->where('reservation_expires_at','<=',now())->orderBy('id')->limit(50)->pluck('id')->each(function ($id) use ($payments) {
            $order=Order::find($id); if (!$order) return;
            // Gateway must be reachable before stock is released; a captured payment wins the race.
            $captured=collect($payments->orderPayments($order->gateway_order_id))->first(fn($p)=>($p['status']??'')==='captured');
            if ($captured) { $payments->confirmCaptured($order,$captured['id']); return; }
            DB::transaction(function () use ($id) {
                $locked=Order::whereKey($id)->lockForUpdate()->first();
                if (!$locked || $locked->status!=='pending_payment' || $locked->reservation_expires_at->isFuture()) return;
                foreach ($locked->items()->orderBy('variant_id')->get() as $item) {
                    if ($item->variant_id) $item->variant()->lockForUpdate()->first()?->decrement('reserved',$item->quantity);
                }
                $locked->update(['status'=>'cancelled','reservation_expires_at'=>null]);
                OrderEvent::create(['order_id'=>$locked->id,'from_status'=>'pending_payment','to_status'=>'cancelled','note'=>'Payment window expired']);
            });
        });
    }
}
