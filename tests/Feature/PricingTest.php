<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Coupon,Setting};
use App\Services\Pricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
class PricingTest extends TestCase {
    use RefreshDatabase;
    public function test_discount_shipping_and_tax_are_calculated_in_minor_units():void {
        Setting::create(['key'=>'shipping_paise','value'=>'9900']);Setting::create(['key'=>'free_shipping_threshold_paise','value'=>'150000']);Setting::create(['key'=>'tax_rate_bps','value'=>'500']);
        $coupon=Coupon::create(['code'=>'SAVE10','type'=>'percent','value'=>10,'min_order_paise'=>0])->refresh();
        $items=collect([(object)['variant'=>(object)['price_paise'=>100000],'quantity'=>2]]);
        $total=app(Pricing::class)->calculate($items,$coupon,'411001');
        $this->assertSame(['subtotal_paise'=>200000,'discount_paise'=>20000,'shipping_paise'=>0,'tax_paise'=>9000,'total_paise'=>189000],$total);
    }
}
