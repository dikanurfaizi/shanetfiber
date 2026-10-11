<?php

namespace App\Services\WhatsApp\CloudAPI\Templates\Runtime\Contracts;

use App\Services\WhatsApp\CloudAPI\Templates\Runtime\TemplateContext;

interface TemplateHandler
{
    public function buildContext(object $command): TemplateContext;
}
