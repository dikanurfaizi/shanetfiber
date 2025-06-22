<?php

namespace App\Helpers;

class Markdown
{
    public static function escapeMarkdownV2($message)
    {
        $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!', '@'];
        foreach ($specialChars as $char) {
            $message = str_replace($char, '\\' . $char, $message);
        }
        return $message;
    }

    public static function convertMarkdownToHtml($message)
    {
        // Bold: *text* → <b>text</b>
        $message = preg_replace('/\*(.+?)\*/s', '<b>$1</b>', $message);

        // Italic: _text_ → <i>text</i>
        $message = preg_replace('/_(.+?)_/s', '<i>$1</i>', $message);

        // Ganti newline jadi linebreak manual (\n), jangan pakai <br>
        $message = str_replace("\n", "\n", $message);

        return $message;
    }
}
