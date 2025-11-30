<?php
// app/Services/Messaging/MessageActionRegistry.php
namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\MessageActionHandler;

class MessageActionRegistry
{
    /** @var array<string, MessageActionHandler> */
    private array $handlers = [];

    public function register(MessageActionHandler $handler): void
    {
        $this->handlers[$handler->action()] = $handler;
    }

    public function get(string $action): ?MessageActionHandler
    {
        return $this->handlers[$action] ?? null;
    }
}
