<?php

namespace Zareismail\Bonchaq;
 
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;  
use Laravel\Nova\Nova as LaravelNova; 

class BonchaqServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Models\BoncahqSubject::class => Policies\ContractSubject::class,
        Models\BoncahqContract::class => Policies\Contract::class,
        Models\BoncahqMaturity::class => Policies\Maturity::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');  
        LaravelNova::serving([$this, 'servingNova']);
        $this->registerPolicies();
    }

    /**
     * Register any Nova services.
     *
     * @return void
     */
    public function servingNova()
    { 
        LaravelNova::resources([
            Nova\Subject::class,
            Nova\Contract::class,
            Nova\Maturity::class,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
