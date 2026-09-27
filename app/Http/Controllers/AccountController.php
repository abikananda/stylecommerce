<?php
namespace App\Http\Controllers;
use App\Models\{Address,Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AccountController extends Controller {
    public function index(Request $request){return view('store.account',['orders'=>$request->user()->orders()->latest()->with('items')->paginate(10),'addresses'=>$request->user()->addresses]);}
    public function address(Request $request){
        $data=$request->validate(['name'=>'required|string|max:100','phone'=>'required|regex:/^[6-9][0-9]{9}$/','line1'=>'required|string|max:200','line2'=>'nullable|string|max:200','city'=>'required|string|max:100','state'=>'required|string|max:100','pincode'=>'required|regex:/^[1-9][0-9]{5}$/']);
        DB::transaction(function()use($request,$data){$request->user()->addresses()->update(['is_default'=>false]);$request->user()->addresses()->create($data+['is_default'=>true]);});return back();
    }
    public function deleteAddress(Request $request,Address $address){abort_unless($address->user_id===$request->user()->id,403);$address->delete();return back();}
    public function wishlist(Request $request){return view('store.wishlist',['products'=>Product::with('images','variants')->whereIn('id',DB::table('wishlists')->where('user_id',$request->user()->id)->pluck('product_id'))->where('published',true)->get()]);}
    public function toggleWishlist(Request $request,Product $product){$query=DB::table('wishlists')->where('user_id',$request->user()->id)->where('product_id',$product->id);if($query->exists())$query->delete();else DB::table('wishlists')->insert(['user_id'=>$request->user()->id,'product_id'=>$product->id]);return back();}
}
