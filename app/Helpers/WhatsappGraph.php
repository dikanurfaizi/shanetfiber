<?php

namespace App\Helpers;

class WhatsAppGraph
{
    /**
     * Buat base URL dinamis berdasarkan account.
     */
    public static function base(string $graphUrl, string $graphVersion): string
    {
        $base = trim($graphUrl, " \t\n\r\0\x0B/");
        $version = trim($graphVersion, " \t\n\r\0\x0B/");

        // Jika base belum mengandung /vXX
        if (! preg_match('/\/v\d+(\.\d+)?$/i', $base)) {
            $base .= "/{$version}";
        }

        return rtrim($base, '/');
    }

    public static function waba(string $graphUrl, string $graphVersion, string $wabaId): string
    {
        return self::base($graphUrl, $graphVersion) . "/{$wabaId}";
    }

    public static function messageTemplates(string $graphUrl, string $graphVersion, string $wabaId): string
    {
        return self::waba($graphUrl, $graphVersion, $wabaId) . '/message_templates';
    }

    public static function sendMessage(string $graphUrl, string $graphVersion, string $phoneNumberId): string
    {
        return self::base($graphUrl, $graphVersion) . "/{$phoneNumberId}/messages";
    }

    public static function templateById(string $graphUrl, string $graphVersion, string $templateId): string
    {
        return self::base($graphUrl, $graphVersion) . "/{$templateId}";
    }

    public static function templateByName(string $graphUrl, string $graphVersion, string $wabaId, string $templateName): string
    {
        return self::base($graphUrl, $graphVersion) . "/{$wabaId}/message_templates?name={$templateName}";
    }
}
