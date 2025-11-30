<?php

namespace App\Enums;

enum TelegramAction: string
{
    case ACTIVATION = 'activation_customer';
    case FULL_PAYMENT_TRANSFER = 'transfer';
    case FULL_PAYMENT_CASH = 'cash';
    case PAYMENT_GATEWAY = 'payment_gateway';
    case PAYLATER_PAYMENT = 'paylater';
    case UNPAYMENT = 'unpayment';
    case CREATE_NEW_BILL = 'create_new_bill';
    case SEND_INFO_BILLING = 'send_info_billing';
    case ORDER_CREATED = 'order_created';
    case CHANGES_PACKAGE = 'changes_paket';
    case TEST_MESSAGE = 'test_message';
}
