<?php

namespace Api\Http\Controllers;

use Api\Http\Requests\StoreProductRequest;
use Frete\Core\Application\IActionFactory;
use Frete\Core\Application\IDispatcher;
use Frete\Core\Infrastructure\Database\Errors\AlreadyExistError;
use Frete\Core\Infrastructure\Database\Errors\FetchError;
use Frete\Core\Infrastructure\Database\Errors\NotFoundError;
use Frete\Core\Infrastructure\Database\Errors\PersistenceError;
use Frete\Core\Shared\Result;

class ProductController extends Controller
{
    public function __construct(
        private readonly IActionFactory $actionFactory,
        private readonly IDispatcher $dispatcher
    ) {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $command = $this->actionFactory->create('REGISTER_PRODUCT', $request->all());
        $result = $this->dispatcher->dispatch($command);

        if ($result->isFailure()) {
            return $this->handleFailure($result);
        }

        return response()->json($result->getValue(), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $product)
    {
        $query = $this->actionFactory->create('FETCH_PRODUCT', ['productId' => $product]);
        $result = $this->dispatcher->dispatch($query);

        if ($result->isFailure()) {
            return $this->handleFailure($result);
        }

        return response()->json($result->getValue(), 200);
    }

    private function handleFailure(Result $result)
    {
        $error = $result->getError();

        return match (get_class($error)) {
            NotFoundError::class => response()->json([
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ], 404),
            AlreadyExistError::class => response()->json([
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ], 409),
            FetchError::class => response()->json([
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ], 503),
            PersistenceError::class => response()->json([
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ], 503),
            default => response()->json('', 500),
        };
    }
}
