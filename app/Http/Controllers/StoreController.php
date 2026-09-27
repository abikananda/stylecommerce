<?php
namespace App\Http\Controllers;
use App\Models\{Category,Collection,Enquiry,Product,Setting};
use Illuminate\Http\Request;
class StoreController extends Controller {
    public function home() {return view('store.home',['featured'=>Product::with('images','variants')->where('published',true)->latest()->take(8)->get(),'categories'=>Category::all()]);}
    public function index(Request $request) {
        $q=Product::query()->with('images','variants','category')->where('published',true)->whereHas('variants');
        foreach (['material','colour','style','occasion'] as $field) if ($request->filled($field)) $q->where($field,$request->query($field));
        if ($request->filled('category')) $q->whereHas('category',fn($x)=>$x->where('slug',$request->query('category')));
        if ($request->filled('collection')) $q->whereHas('collection',fn($x)=>$x->where('slug',$request->query('collection')));
        if ($request->filled('q')) $q->where(fn($x)=>$x->where('name','like','%'.addcslashes($request->query('q'),'%_\\').'%')->orWhere('description','like','%'.addcslashes($request->query('q'),'%_\\').'%'));
        if ($request->filled('min')) $q->whereHas('variants',fn($x)=>$x->where('price_paise','>=',(int)round((float)$request->query('min')*100)));
        if ($request->filled('max')) $q->whereHas('variants',fn($x)=>$x->where('price_paise','<=',(int)round((float)$request->query('max')*100)));
        if ($request->boolean('available')) $q->whereHas('variants',fn($x)=>$x->whereColumn('stock','>','reserved'));
        if ($request->query('sort')==='price_asc' || $request->query('sort')==='price_desc') $q->orderBy(Product::selectRaw('MIN(price_paise)')->from('product_variants')->whereColumn('product_id','products.id'),$request->query('sort')==='price_asc'?'asc':'desc');
        else $q->orderBy('products.created_at','desc');
        return view('store.shop',['products'=>$q->paginate(12)->withQueryString(),'categories'=>Category::all(),'filters'=>Product::where('published',true)->get(['material','colour','style','occasion'])]);
    }
    public function category(Category $category,Request $request) {$request->query->set('category',$category->slug);return $this->index($request);}
    public function collection(Collection $collection,Request $request) {$request->query->set('collection',$collection->slug);return $this->index($request);}
    public function show(Product $product) {
        abort_unless($product->published,404);
        return view('store.product',['product'=>$product->load('images','variants','category'),'related'=>Product::with('images','variants','category')->where('published',true)->where('category_id',$product->category_id)->where('id','!=',$product->id)->limit(4)->get()]);
    }
    public function page(string $slug) {
        abort_unless(in_array($slug,['about','contact','shipping','returns','privacy','terms']),404);
        return view('store.page',['slug'=>$slug,'content'=>Setting::valueOf('page_'.$slug,'Please contact us for details.')]);
    }
    public function sizeGuide() {return view('store.page',['slug'=>'Bangle size guide','content'=>Setting::valueOf('page_size_guide','Measure the inner diameter of a bangle that fits you, then compare it with each product variant size.')]);}
    public function contact(Request $request) {Enquiry::create($request->validate(['name'=>'required|string|max:100','email'=>'required|email|max:255','message'=>'required|string|max:3000']));return back()->with('message','Thanks, we received your message.');}
    public function sitemap() {return response()->view('store.sitemap',['products'=>Product::where('published',true)->get(['slug','updated_at']),'categories'=>Category::all()])->header('Content-Type','application/xml');}
}
