<?php
// app/Services/Messaging/MessageActionRegistry.php
namespace App\Services\Messaging;

use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Models\Whatsapp\WhatsappMessage;

class MessageActionRegistry
{
    /** @var array<string, MessageActionHandler> */
    private array $handlers = [];

    public function getCategory(string $action): string
    {
        $template = WhatsappMessage::where('slug', $action)->first();

        return $template->category ?? 'BROADCAST';
    }

    public function register(MessageActionHandler $handler): void
    {
        $this->handlers[$handler->action()] = $handler;
    }

    public function get(string $action): ?MessageActionHandler
    {
        return $this->handlers[$action] ?? null;
    }
}
