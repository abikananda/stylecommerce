<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;
class AppServiceProvider extends ServiceProvider {
    public function boot(): void {
        View::composer('*', function ($view) {
            $view->with('brand', Setting::valueOf('brand_name', 'AURÉ Jewellery'));
        });
    }
}
