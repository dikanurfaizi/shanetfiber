<?php

namespace App\Enums;

enum WhatsAppAction: string
{
    case ACTIVATION = 'activation_customer';
    case PAYMENT = 'payment';
    case TRANSFER = 'transfer';
    case CASH = 'cash';
    case PAYMENT_ONLINE = 'payment_online';
    case PAYLATER = 'paylater';
    case UNPAYMENT = 'unpayment';
    case INITIAL_BROADCAST = 'initial_broadcast';
    case DUE_DATE_BROADCAST = 'due_date_broadcast';
    case OVERDUE_BROADCAST = 'overdue_broadcast';
    case CHANGES_PACKAGE = 'changes_package';
    case TEST_MESSAGE = 'test_message';
    case PAYMENT_CHANNEL_UPDATE = 'payment_channel_update';
    case UNKNOWN_KEYWORD = 'unknown_keyword';
    case UNKNOWN_CUSTOMER = 'unknown_customer';
    case THANK_YOU = 'thank_you';
    case YA = 'ya';
    case STOP = 'stop';
    case MENU = 'menu';
    case UNKNOWN_INFO_PACKAGE = 'unknown_info_package';
    case UNKNOWN_CUSTOMER_INFO = 'unknown_customer_info';
    case UNKNOWN_CALL_ADMIN = 'unknown_call_admin';
    case CALL_ADMIN = 'hubungi_admin';
    case ISSUES = 'gangguan';
    case TELEGRAM_BOT = 'telegram_bot';
    case DIRECT_MESSAGE = 'direct_message';

    /**
     * Cek apakah action merupakan kategori Broadcast.
     */
    public function isBroadcast(): bool
    {
        return in_array($this, self::broadcastCases(), true);
    }

    /**
     * Daftar seluruh action yang dikategorikan sebagai Broadcast.
     * 💡 Sumber kebenaran tunggal untuk jenis broadcast di aplikasi.
     *
     * @return array<self>
     */
    public static function broadcastCases(): array
    {
        return [
            self::INITIAL_BROADCAST,
            self::DUE_DATE_BROADCAST,
            self::OVERDUE_BROADCAST,
            self::PAYMENT_CHANNEL_UPDATE,
        ];
    }
}
