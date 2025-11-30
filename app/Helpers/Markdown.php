<?php

namespace App\Helpers;

class Markdown
{
    /**
     * Escape karakter Markdown V2 (jarang dipakai jika parse_mode="HTML").
     */
    public static function escapeMarkdownV2($message)
    {
        $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!', '@'];
        foreach ($specialChars as $char) {
            $message = str_replace($char, '\\' . $char, $message);
        }
        return $message;
    }

    /**
     * Convert simple Markdown (*, _) ke HTML untuk Telegram parse_mode="HTML".
     */
    public static function convertMarkdownToHtml($message)
    {
        // Step 1 — Bold + Italic: _*text*_  atau *_text_*
        $message = preg_replace('/\_\*(.+?)\*\_/s', '<i><b>$1</b></i>', $message);
        $message = preg_replace('/\*\_(.+?)\_\*/s', '<b><i>$1</i></b>', $message);

        // Step 2 — Bold: *text*
        // Pastikan tidak terkena nested italic yg sudah diganti
        $message = preg_replace('/\*(?!<)(.+?)(?!>)\*/s', '<b>$1</b>', $message);

        // Step 3 — Italic: _text_
        $message = preg_replace('/\_(?!<)(.+?)(?!>)\_/s', '<i>$1</i>', $message);

        // Step 4 — newline => newline (Telegram HTML-supports newline)
        $message = str_replace(["\r\n", "\r"], "\n", $message);

        return $message;
    }
}
