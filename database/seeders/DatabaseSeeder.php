<?php
namespace Database\Seeders;
use App\Models\{Category,Collection,Product,Setting,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        $earrings=Category::firstOrCreate(['slug'=>'earrings'],['name'=>'Earrings','description'=>'Studs, hoops and statement pieces']);
        $bangles=Category::firstOrCreate(['slug'=>'bangles'],['name'=>'Bangles','description'=>'Single bangles and curated sets']);
        $collection=Collection::firstOrCreate(['slug'=>'everyday-edit'],['name'=>'The everyday edit']);
        $samples=[
            [$earrings,'Solstice Pearl Drops','Delicate faux-pearl drops with a warm gold-toned finish.','Pearl','Ivory','Drops','Everyday',129900,['Ivory','Champagne']],
            [$earrings,'Arc Mini Hoops','Compact curved hoops for everyday styling.','Alloy','Gold','Hoops','Everyday',89900,['Gold','Silver']],
            [$earrings,'Noor Statement Studs','Light-catching studs for evenings and celebrations.','Alloy','Gold','Studs','Occasion',149900,['Gold','Rose gold']],
            [$bangles,'Nira Gold Bangles','A slim, stackable bangle set with a polished finish.','Alloy','Gold','Stack','Occasion',189900,['2.4','2.6','2.8']],
            [$bangles,'Meera Everyday Cuff','An open cuff with a clean, modern profile.','Alloy','Gold','Cuff','Everyday',159900,['Adjustable']],
            [$bangles,'Tara Bangle Set','A paired set of textured bangles.','Alloy','Rose gold','Set','Celebration',229900,['2.4','2.6','2.8']],
        ];
        $demoImageAlts=[
            'solstice-pearl-drops'=>'Gold-toned drop earrings with ivory faux pearls',
            'arc-mini-hoops'=>'Pair of polished gold-toned mini hoop earrings',
            'noor-statement-studs'=>'Pair of floral gold-toned statement stud earrings',
            'nira-gold-bangles'=>'Three slim polished gold-toned bangles',
            'meera-everyday-cuff'=>'Open polished gold-toned cuff bangle',
            'tara-bangle-set'=>'Pair of textured rose gold-toned bangles',
        ];
        foreach($samples as [$category,$name,$description,$material,$colour,$style,$occasion,$price,$options]){
            $product=Product::firstOrCreate(['slug'=>Str::slug($name)],['category_id'=>$category->id,'collection_id'=>$collection->id,'name'=>$name,'description'=>$description,'care'=>'Keep dry; store separately and clean gently with a soft cloth.','material'=>$material,'colour'=>$colour,'style'=>$style,'occasion'=>$occasion,'published'=>true]);
            foreach($options as $option){$isBangle=$category->id===$bangles->id;$product->variants()->firstOrCreate(['sku'=>strtoupper(Str::slug($name.'-'.$option))],['size'=>$isBangle?$option:null,'colour'=>$isBangle?$colour:$option,'material'=>$material,'set_size'=>$name==='Tara Bangle Set'?'Pair':null,'price_paise'=>$price,'stock'=>12]);}
            $path='demo/'.$product->slug.'.webp';
            if ($product->images()->doesntExist() || $product->images()->where('path',$path)->exists()) {
                if (!Storage::disk('public')->exists($path)) {
                    $source=public_path('images/demo/'.$product->slug.'.webp');
                    if (!is_file($source) || !Storage::disk('public')->put($path,file_get_contents($source))) {
                        throw new \RuntimeException('Unable to seed demo image for '.$product->slug);
                    }
                }
                $product->images()->firstOrCreate(['path'=>$path],['alt'=>$demoImageAlts[$product->slug],'position'=>0]);
            }
        }
        foreach(['brand_name'=>'AURÉ Jewellery','shipping_paise'=>'9900','free_shipping_threshold_paise'=>'199900','tax_rate_bps'=>'0','cod_enabled'=>'0','page_shipping'=>'Shipping charges and serviceability are shown before payment. Tracking details will appear in your account after dispatch.','page_returns'=>'Contact us to request a return. Eligibility, timeframe and refund terms must be configured before launch.','page_about'=>'Jewellery for everyday moments and special occasions.','page_contact'=>'We would love to hear from you.','page_privacy'=>'Configure this policy to explain your data processing before launch.','page_terms'=>'Configure your store terms before launch.','page_size_guide'=>'Measure the inner diameter of an existing bangle and compare sizes before ordering.'] as $key=>$value) Setting::firstOrCreate(['key'=>$key],['value'=>$value]);
        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) { $admin=User::firstOrCreate(['email'=>env('ADMIN_EMAIL')],['name'=>'Store administrator','password'=>Hash::make(env('ADMIN_PASSWORD'))]);$admin->is_admin=true;$admin->save(); }
    }
}
