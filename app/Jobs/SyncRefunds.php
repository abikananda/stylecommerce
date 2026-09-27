<?php
namespace App\Jobs;
use App\Models\Refund;
use App\Services\Refunds;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
class SyncRefunds implements ShouldQueue {
    use Queueable;
    public function handle(Refunds $refunds):void {Refund::where('status','pending')->whereNotNull('gateway_refund_id')->limit(50)->pluck('gateway_refund_id')->each(fn($id)=>$refunds->sync($id));}
}
