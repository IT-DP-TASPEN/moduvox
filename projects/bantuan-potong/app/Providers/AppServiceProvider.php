<?php

namespace App\Providers;

use App\Models\BanpotMaster;
use App\Models\PermintaanChecking;
use App\Models\PermintaanEstimasi;
use App\Models\PermintaanFlaggingTif;
use App\Observers\BanpotMasterObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\PermintaanOpenFlaggingTif;
use App\Models\PermintaanCheckingInternal;
use App\Models\PermintaanEstimasiInternal;
use App\Models\PermintaanFlaggingMutasiTif;
use App\Observers\PermintaanCheckingObserver;
use App\Observers\PermintaanEstimasiObserver;
use App\Observers\PermintaanFlaggingObserver;
use App\Observers\PermintaanOpenFlaggingObserver;
use App\Models\PermintaanFlaggingMutasiTifInternal;
use App\Models\PermintaanFlaggingTifInternal;
use App\Observers\PermintaanFlaggingMutasiObserver;
use App\Observers\PermintaanCheckingInternalObserver;
use App\Observers\PermintaanEstimasiInternalObserver;
use App\Observers\PermintaanFlaggingInternalObserver;
use App\Observers\PermintaanFlaggingMutasiInternalObserver;
use App\Observers\PermintaanOpenFlaggingInternalObserver;
use App\Models\PermintaanOpenFlaggingInternal;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // Tambahan untuk memastikan semua URL menggunakan HTTPS
        if ($this->app->environment('production')) {
            $this->app['request']->server->set('HTTPS', 'on');
        }
        BanpotMaster::observe(BanpotMasterObserver::class);
        PermintaanChecking::observe(PermintaanCheckingObserver::class);
        PermintaanEstimasi::observe(PermintaanEstimasiObserver::class);
        PermintaanOpenFlaggingTif::observe(PermintaanOpenFlaggingObserver::class);
        PermintaanFlaggingMutasiTif::observe(PermintaanFlaggingMutasiObserver::class);
        PermintaanFlaggingTif::observe(PermintaanFlaggingObserver::class);
        PermintaanCheckingInternal::observe(PermintaanCheckingInternalObserver::class);
        PermintaanEstimasiInternal::observe(PermintaanEstimasiInternalObserver::class);
        PermintaanOpenFlaggingInternal::observe(PermintaanOpenFlaggingInternalObserver::class);
        PermintaanFlaggingMutasiTifInternal::observe(PermintaanFlaggingMutasiInternalObserver::class);
        PermintaanFlaggingTifInternal::observe(PermintaanFlaggingInternalObserver::class);
    }
}
