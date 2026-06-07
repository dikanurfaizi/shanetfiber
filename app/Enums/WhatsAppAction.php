<?php

namespace App\Enums;

enum WhatsAppAction: string
{
    case ACTIVATION = 'activation_customer';
    case TRANSFER = 'transfer';
    case CASH = 'cash';
    case PAYMENT_GATEWAY = 'payment_gateway';
    case PAYLATER = 'paylater';
    case UNPAYMENT = 'unpayment';
    case CREATE_NEW_BILL = 'create_new_bill';
    case SEND_INFO_BILLING = 'send_info_billing';
    case ORDER_CREATED = 'order_created';
    case CHANGES_PACKAGE = 'changes_paket';
    case TEST_MESSAGE = 'test_message';
    case PAYMENT_CHANNEL_UPDATE = 'payment_channel_update';
    case UNKNOWN_KEYWORD = 'unknown_keyword';
    case UNKNOWN_CUSTOMER = 'unknown_customer';
    case THANK_YOU = 'thank_you';
    case YA = 'ya';
    case STOP = 'stop';
    case MENU = 'menu';
    case UNKNOWN_INFO_PACKAGE = 'unknown_info_paket';
    case UNKNOWN_CUSTOMER_INFO = 'unknown_customer_info';
    case UNKNOWN_CALL_ADMIN = 'unknown_call_admin';
    case CALL_ADMIN = 'hubungi_admin';
    case ISSUES = 'gangguan';
}
