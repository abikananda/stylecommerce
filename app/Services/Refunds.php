<?php
namespace App\Services;
use App\Mail\OrderUpdate;
use App\Models\{Order,OrderEvent,PaymentAttempt,Refund};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
class Refunds {
    private function client() {return Http::withBasicAuth(config('services.razorpay.key_id'),config('services.razorpay.key_secret'))->baseUrl('https://api.razorpay.com/v1')->timeout(12)->acceptJson();}
    public function initiate(Order $order): void {
        DB::transaction(function()use($order){
            $locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->payment_method==='razorpay' && in_array($locked->status,['paid','processing','returned'],true),422);
            abort_if($locked->refund()->exists(),422,'Refund already requested.');
            $payment=PaymentAttempt::where('order_id',$locked->id)->where('status','captured')->firstOrFail();
            $refund=Refund::create(['order_id'=>$locked->id,'gateway_payment_id'=>$payment->gateway_payment_id,'idempotency_key'=>'order-'.$locked->id.'-full-refund','prior_status'=>$locked->status,'amount_paise'=>$locked->total_paise]);
            $result=$this->client()->withHeaders(['X-Refund-Idempotency'=>$refund->idempotency_key])->post('/payments/'.rawurlencode($payment->gateway_payment_id).'/refund',['amount'=>$refund->amount_paise,'speed'=>'normal','receipt'=>'refund-'.$locked->id])->throw()->json();
            if (($result['payment_id']??null)!==$payment->gateway_payment_id || (int)($result['amount']??0)!==$locked->total_paise || ($result['currency']??'')!=='INR') throw new \RuntimeException('Refund response mismatch');
            $refund->update(['gateway_refund_id'=>$result['id'],'status'=>in_array($result['status'],['processed','failed'],true)?$result['status']:'pending']);
            $locked->update(['status'=>match($result['status']){'processed'=>'refunded','failed'=>$refund->prior_status,default=>'refund_pending'}]);
            OrderEvent::create(['order_id'=>$locked->id,'actor_id'=>auth()->id(),'from_status'=>$refund->prior_status,'to_status'=>$locked->status,'note'=>'Full refund requested: '.$result['id']]);
            DB::afterCommit(fn()=>Mail::to($locked->email)->queue(new OrderUpdate($locked->id)));
        });
    }
    public function sync(string $refundId): void {
        $refund=Refund::where('gateway_refund_id',$refundId)->first();if(!$refund)return;
        $result=$this->client()->get('/refunds/'.rawurlencode($refundId))->throw()->json();
        if (($result['id']??'')!==$refundId || ($result['payment_id']??'')!==$refund->gateway_payment_id || (int)($result['amount']??0)!==$refund->amount_paise) throw new \RuntimeException('Refund verification mismatch');
        if (!in_array($result['status']??'',['processed','failed'],true)) return;
        DB::transaction(function()use($refund,$result){
            $order=Order::whereKey($refund->order_id)->lockForUpdate()->firstOrFail();
            $locked=Refund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            if ($locked->status===$result['status']) return;
            $status=$result['status']==='processed'?'refunded':$locked->prior_status;
            $from=$order->status;$order->update(['status'=>$status]);$locked->update(['status'=>$result['status']]);
            OrderEvent::create(['order_id'=>$order->id,'from_status'=>$from,'to_status'=>$status,'note'=>'Razorpay refund '.$result['status']]);
            DB::afterCommit(fn()=>Mail::to($order->email)->queue(new OrderUpdate($order->id)));
        });
    }
}
