<?php

declare(strict_types=1);

namespace Api\Service;
use App\Application\Handlers\FetchProduct;
use App\Application\Queries\FetchProduct as FetchProductQuery;
use App\Application\Handlers\RegisterProduct;
use App\Application\Commands\RegisterProduct as RegisterProductCommand;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\QueryHandler;
use Frete\Core\Shared\Result;

class ProductService
{
    public function __construct(
        private readonly RegisterProduct $registerProduct,
        private readonly FetchProduct $fetchProduct
    ) {
    }

    #[CommandHandler]
    public function registerProduct(RegisterProductCommand $command): Result
    {
        return $this->registerProduct->handle($command);
    }

    #[QueryHandler]
    public function fetchProduct(FetchProductQuery $query): Result
    {
        return $this->fetchProduct->handle($query);
    }
}
