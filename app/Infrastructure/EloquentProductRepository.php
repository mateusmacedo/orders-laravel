<?php

declare(strict_types=1);

namespace Api\Infrastructure;

use Api\Models\Product as ProductModel;
use App\Domain\Product;
use App\Domain\ProductRepository;
use DateTimeImmutable;
use Frete\Core\Infrastructure\Database\Errors\AlreadyExistError;
use Frete\Core\Infrastructure\Database\Errors\FetchError;
use Frete\Core\Infrastructure\Database\Errors\NotFoundError;
use Frete\Core\Infrastructure\Database\Errors\PersistenceError;
use Frete\Core\Infrastructure\Database\Errors\RepositoryError;
use Throwable;

class EloquentProductRepository implements ProductRepository
{
    public function __construct(private ProductModel $productModel)
    {
    }

    /**
     * @param string $productId
     * @return Product|RepositoryError
     */
    public function get(string $productId): Product|RepositoryError
    {
        try {
            $productModel = $this->productModel->where('product_id', $productId)->first();
            if (!$productModel) {
                return new NotFoundError('Product not found');
            }
            return new Product(
                $productModel->product_id,
                $productModel->name,
                $productModel->description,
                (float) $productModel->price,
                new DateTimeImmutable($productModel->registered_at)
            );
        } catch(Throwable $th) {
            return new FetchError('Error fetching product');
        }
    }

    /**
     *
     * @param array $filters
     * @return RepositoryError|array
     */
    public function find(array $filters): RepositoryError|array
    {
        $productCollection = $this->productModel->where($filters)->get();
        return array_map(
            fn ($productModel) => new Product(
                $productModel->product_id,
                $productModel->name,
                $productModel->description,
                $productModel->price,
                $productModel->registered_at
            ),
            $productCollection->toArray()
        );
    }

    /**
     *
     * @param Product $product
     * @return Product|RepositoryError
     */
    public function save(Product $product): Product|RepositoryError
    {
        try {
            $productModel = $this->productModel->create([
                'product_id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'registered_at' => $product->registeredAt,
            ]);

            return new Product(
                $productModel->product_id,
                $productModel->name,
                $productModel->description,
                $productModel->price,
                $productModel->registered_at
            );
        } catch (Throwable $th) {
            return match ($th->getCode()) {
                '23505' => new AlreadyExistError('Product already exists'),
                default => new PersistenceError('Error saving product'),
            };
        }
    }
}
