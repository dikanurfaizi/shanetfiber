<?php

namespace App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\ReplyMessageCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Contracts\TemplateHandler as ContractsTemplateHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateContext;
use InvalidArgumentException;

class ReplyMessageHandler implements ContractsTemplateHandler
{
    /**
     * Build context khusus untuk pesan reply/teks biasa.
     */
    public function buildContext(object $command): TemplateContext
    {
        if (!$command instanceof ReplyMessageCommand) {
            throw new InvalidArgumentException("Invalid command for ReplyMessageHandler");
        }

        return new TemplateContext(
            recipient: $command->recipient,
            templateName: 'REPLY_TEXT', // Penanda unik untuk MessagePayloadBuilder
            language: 'id',
            account: $command->account,

            // Karena ini bukan template, values kosongkan saja
            bodyValues: [],
            headerValues: [],
            buttonValues: [],

            // Isi data utama untuk pesan teks
            text: $command->text,
            messageId: $command->messageId
        );
    }
}
