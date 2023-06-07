<?php

namespace Api\Providers;

use Api\Service\ProductService;
use App\Application\Actions;
use App\Application\Handlers\FetchProduct;
use App\Application\Handlers\RegisterProduct;
use Frete\Core\Application\ActionFactory;
use Frete\Core\Application\IActionFactory;
use Frete\Core\Domain\AbstractFactory;
use Illuminate\Contracts\Foundation\Application;
use Api\Infrastructure\EcotoneDispatcher;
use Api\Infrastructure\EloquentProductRepository;
use App\Domain\ProductFactory;
use App\Domain\ProductRepository;
use Frete\Core\Application\IDispatcher;
use Illuminate\Support\ServiceProvider;

class ProductProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(IActionFactory::class, function (Application $app) {
            return new ActionFactory(Actions::class);
        });

        $this->app->singleton(IDispatcher::class, EcotoneDispatcher::class);

        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);

        $this->app->bind(AbstractFactory::class, ProductFactory::class);

        $this->app->singleton(RegisterProduct::class, function (Application $app) {
            return new RegisterProduct(
                $app->make(AbstractFactory::class),
                $app->make(ProductRepository::class),
                $app->make(IDispatcher::class)
            );
        });

        $this->app->singleton(FetchProduct::class, function (Application $app) {
            return new FetchProduct(
                $app->make(ProductRepository::class)
            );
        });

        $this->app->singleton(ProductService::class, function (Application $app) {
            return new ProductService(
                $app->make(RegisterProduct::class),
                $app->make(FetchProduct::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
