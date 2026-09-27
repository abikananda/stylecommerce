<?php
namespace App\Http\Controllers;
use App\Models\{GatewayEvent,Order};
use App\Services\Payments;
use App\Services\Refunds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentController extends Controller {
    private function authorizeOrder(Request $request,Order $order):void {abort_unless(($order->user_id && $order->user_id===$request->user()?->id) || $request->session()->get('order_access_'.$order->id),403);}
    public function show(Request $request,Order $order) {$this->authorizeOrder($request,$order);if($order->payment_method==='cod')return redirect()->route('order.status',$order);return view('store.payment',compact('order'));}
    public function status(Request $request,Order $order) {$this->authorizeOrder($request,$order);return view('store.status',['order'=>$order->load('items','events')]);}
    public function confirm(Request $request,Order $order,Payments $payments) {
        $this->authorizeOrder($request,$order);
        $data=$request->validate(['razorpay_order_id'=>'required|string','razorpay_payment_id'=>'required|string','razorpay_signature'=>'required|string']);
        abort_unless($data['razorpay_order_id']===$order->gateway_order_id && $payments->verifyCallback($order,$data['razorpay_payment_id'],$data['razorpay_signature']),403);
        $payments->confirmCaptured($order,$data['razorpay_payment_id']);
        return redirect()->route('order.status',$order);
    }
    public function webhook(Request $request,Payments $payments,Refunds $refunds) {
        $raw=$request->getContent();
        abort_unless($payments->verifyWebhook($raw,$request->header('X-Razorpay-Signature','')),403);
        $eventId=$request->header('X-Razorpay-Event-Id'); abort_unless($eventId,400);
        $event=$request->input('event');
        $payment=$request->input('payload.payment.entity');
        if ($event==='payment.captured' && $payment && ($payment['status']??'')==='captured') {
            $order=Order::where('gateway_order_id',$payment['order_id']??'')->first();
            if ($order) $payments->confirmCaptured($order,$payment['id']);
        }
        if (in_array($event,['refund.processed','refund.failed'],true)) {
            $refund=$request->input('payload.refund.entity');
            if (!empty($refund['id'])) $refunds->sync($refund['id']);
        }
        GatewayEvent::firstOrCreate(['gateway_event_id'=>$eventId],['event_type'=>$event??'unknown']);
        return response()->json(['ok'=>true]);
    }
}
