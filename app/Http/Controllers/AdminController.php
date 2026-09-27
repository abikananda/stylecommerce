<?php
namespace App\Http\Controllers;
use App\Models\{Category,Collection,Coupon,Enquiry,Order,OrderEvent,PaymentAttempt,Product,ProductImage,ProductVariant,Setting,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\OrderUpdate;
use App\Services\Refunds;
use App\Services\Images;
class AdminController extends Controller {
    public function index(){return view('admin.index',['orders'=>Order::latest()->limit(10)->get(),'revenue'=>Order::whereIn('status',['paid','processing','shipped','delivered'])->sum('total_paise'),'lowStock'=>ProductVariant::whereRaw('stock - reserved <= 5')->with('product')->get(),'customers'=>User::where('is_admin',false)->count(),'enquiries'=>Enquiry::latest()->limit(10)->get()]);}
    public function products(){return view('admin.products',['products'=>Product::with('category','variants')->latest()->paginate(30)]);}
    public function editProduct(?Product $product=null){return view('admin.product',['product'=>$product?->load('variants','images'),'categories'=>Category::all(),'collections'=>Collection::all()]);}
    public function saveProduct(Request $request,?Product $product=null){
        $data=$request->validate(['category_id'=>'required|exists:categories,id','collection_id'=>'nullable|exists:collections,id','name'=>'required|string|max:180','slug'=>'required|alpha_dash|max:180|unique:products,slug,'.($product?->id??'NULL'),'description'=>'required|string|max:10000','care'=>'nullable|string|max:2000','material'=>'nullable|string|max:100','colour'=>'nullable|string|max:100','style'=>'nullable|string|max:100','occasion'=>'nullable|string|max:100','published'=>'nullable|boolean','variants'=>'required|array|min:1','variants.*.id'=>'nullable|integer','variants.*.sku'=>'required|string|max:100|distinct','variants.*.size'=>'nullable|string|max:60','variants.*.colour'=>'nullable|string|max:60','variants.*.material'=>'nullable|string|max:60','variants.*.set_size'=>'nullable|string|max:60','variants.*.price_paise'=>'required|integer|min:1','variants.*.stock'=>'required|integer|min:0']);
        DB::transaction(function()use($data,$product){
            $product ??=new Product;
            $product->fill(collect($data)->except('variants')->all());$product->published=(bool)($data['published']??false);$product->save();
            $keep=[];
            foreach($data['variants'] as $v){
                $variant=isset($v['id']) ? $product->variants()->whereKey($v['id'])->firstOrFail() : new ProductVariant(['product_id'=>$product->id]);
                if($variant->exists && $v['stock']<$variant->reserved) throw \Illuminate\Validation\ValidationException::withMessages(['variants'=>'Stock cannot be below reserved quantity.']);
                if (ProductVariant::where('sku',$v['sku'])->where('id','!=',$variant->id??0)->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['variants'=>'SKU '.$v['sku'].' is already in use.']);
                $variant->fill(collect($v)->except('id')->all());$variant->save();$keep[]=$variant->id;
            }
            if($product->variants()->whereNotIn('id',$keep)->where('reserved','>',0)->exists()) throw \Illuminate\Validation\ValidationException::withMessages(['variants'=>'Reserved variants cannot be removed.']);
            $product->variants()->whereNotIn('id',$keep)->delete();
        });return redirect()->route('admin.products')->with('message','Product saved.');
    }
    public function uploadImages(Request $request,Product $product,Images $images){
        $data=$request->validate(['images'=>'required|array|max:8','images.*'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096','alt'=>'required|string|max:200']);
        foreach($data['images'] as $file){$path=$images->store($file);$product->images()->create(['path'=>$path,'alt'=>$data['alt'],'position'=>($product->images()->max('position')??0)+1]);}
        return back()->with('message','Images uploaded.');
    }
    public function deleteImage(ProductImage $image){Storage::disk('public')->delete($image->path);$image->delete();return back();}
    public function reorderImage(Request $request,ProductImage $image){$image->update($request->validate(['position'=>'required|integer|min:0|max:1000','alt'=>'required|string|max:200']));return back();}
    public function orders(){return view('admin.orders',['orders'=>Order::with('items')->latest()->paginate(30)]);}
    public function order(Order $order){return view('admin.order',['order'=>$order->load('items','attempts','events')]);}
    public function orderStatus(Request $request,Order $order){
        $data=$request->validate(['status'=>'required|in:processing,shipped,delivered,return_requested,returned','tracking_number'=>'nullable|string|max:100']);
        $allowed=['paid'=>['processing'],'processing'=>['shipped'],'shipped'=>['delivered'],'delivered'=>['return_requested'],'return_requested'=>['returned']];
        DB::transaction(function()use($order,$data,$allowed){$locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();abort_unless(in_array($data['status'],$allowed[$locked->status]??[],true),422);$from=$locked->status;$locked->update(['status'=>$data['status'],'tracking_number'=>$data['tracking_number']??$locked->tracking_number]);OrderEvent::create(['order_id'=>$locked->id,'actor_id'=>auth()->id(),'from_status'=>$from,'to_status'=>$data['status']]);DB::afterCommit(fn()=>Mail::to($locked->email)->queue(new OrderUpdate($locked->id)));});return back();
    }
    public function refund(Order $order,Refunds $refunds){$refunds->initiate($order);return back()->with('message','Refund requested. Final status follows Razorpay confirmation.');}
    public function settings(){return view('admin.settings',['settings'=>Setting::pluck('value','key'),'categories'=>Category::all(),'collections'=>Collection::all()]);}
    public function saveSettings(Request $request){
        $data=$request->validate(['brand_name'=>'required|string|max:100','brand_email'=>'nullable|email','brand_phone'=>'nullable|string|max:30','instagram_url'=>'nullable|url','shipping_paise'=>'required|integer|min:0','free_shipping_threshold_paise'=>'required|integer|min:0','tax_rate_bps'=>'required|integer|min:0|max:10000','shipping_pincode_prefixes'=>'nullable|string|max:500','cod_enabled'=>'nullable|boolean','page_about'=>'nullable|string|max:10000','page_contact'=>'nullable|string|max:10000','page_shipping'=>'nullable|string|max:10000','page_returns'=>'nullable|string|max:10000','page_privacy'=>'nullable|string|max:10000','page_terms'=>'nullable|string|max:10000','page_size_guide'=>'nullable|string|max:10000','category_name'=>'nullable|string|max:100','collection_name'=>'nullable|string|max:100']);
        foreach(collect($data)->except('category_name','collection_name') as $key=>$value) Setting::updateOrCreate(['key'=>$key],['value'=>(string)$value]);
        Setting::updateOrCreate(['key'=>'cod_enabled'],['value'=>(string)$request->boolean('cod_enabled')]);
        if($request->filled('category_name')) Category::firstOrCreate(['slug'=>Str::slug($data['category_name'])],['name'=>$data['category_name']]);
        if($request->filled('collection_name')) Collection::firstOrCreate(['slug'=>Str::slug($data['collection_name'])],['name'=>$data['collection_name']]);
        return back()->with('message','Settings saved.');
    }
    public function coupons(){return view('admin.coupons',['coupons'=>Coupon::latest()->get()]);}
    public function saveCoupon(Request $request){$data=$request->validate(['code'=>'required|alpha_dash|max:40|unique:coupons,code','type'=>'required|in:fixed,percent','value'=>'required|integer|min:1','min_order_paise'=>'required|integer|min:0','usage_limit'=>'nullable|integer|min:1','expires_at'=>'nullable|date|after:now']);if($data['type']==='percent' && $data['value']>100) return back()->withErrors(['value'=>'Percent cannot exceed 100.']);Coupon::create($data+['active'=>true]);return back();}
    public function updateCoupon(Request $request,Coupon $coupon){$data=$request->validate(['active'=>'required|boolean']);$coupon->update($data);return back();}
    public function updateCategory(Request $request,Category $category){$data=$request->validate(['name'=>'required|string|max:100','slug'=>'required|alpha_dash|max:100|unique:categories,slug,'.$category->id,'description'=>'nullable|string|max:2000']);$category->update($data);return back();}
    public function deleteCategory(Category $category){abort_if($category->products()->exists(),422,'Move or remove products before deleting this category.');$category->delete();return back();}
    public function updateCollection(Request $request,Collection $collection){$data=$request->validate(['name'=>'required|string|max:100','slug'=>'required|alpha_dash|max:100|unique:collections,slug,'.$collection->id]);$collection->update($data);return back();}
    public function deleteCollection(Collection $collection){$collection->delete();return back();}
    public function customers(){return view('admin.customers',['customers'=>User::where('is_admin',false)->withCount('orders')->orderBy('created_at','desc')->paginate(30)]);}
}
