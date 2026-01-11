<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;

interface TemplateHandler
{
    public function buildContext(object $command): TemplateContext;
}
