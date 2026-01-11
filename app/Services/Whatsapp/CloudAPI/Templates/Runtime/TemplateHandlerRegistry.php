<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler;
use RuntimeException;

class TemplateHandlerRegistry
{
    protected array $map = [];

    public function register(string $commandClass, string $handlerClass)
    {
        $this->map[$commandClass] = $handlerClass;
    }

    public function resolve(object $command): TemplateHandler
    {
        $class = get_class($command);

        if (!isset($this->map[$class])) {
            throw new RuntimeException("No handler for {$class}");
        }

        return app($this->map[$class]);
    }
}
