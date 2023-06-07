<?php

declare(strict_types=1);

namespace Api\Infrastructure;

use Ecotone\Modelling\CommandBus;
use Ecotone\Modelling\EventBus;
use Ecotone\Modelling\QueryBus;
use Frete\Core\Application\Command;
use Frete\Core\Application\Errors\ApplicationError;
use Frete\Core\Application\IDispatcher;
use Frete\Core\Application\Query;
use Frete\Core\Domain\IEventStore;
use Frete\Core\Domain\Message;
use Frete\Core\Shared\Result;

class EcotoneDispatcher implements IDispatcher
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
        private readonly EventBus $eventBus
    ) {
    }

    /**
     * @param Message $message
     * @return Result
     */
    public function dispatch(Message $message): Result
    {
        $parents = class_parents($message);

        if (in_array('Frete\Core\Application\Command', $parents)) {
            return $this->commandBus->send($message);
        }

        if (in_array('Frete\Core\Application\Query', $parents)) {
            return $this->queryBus->send($message);
        }

        return Result::failure(new ApplicationError("Message type not supported"));
    }

    /**
     *
     * @param IEventStore $context
     */
    public function dispatchContextEvents(IEventStore $context): void
    {
        foreach ($context->getEvents() as $event) {
            $this->eventBus->publish($event);
            $context->commitEvent($event);
        }
    }
}
