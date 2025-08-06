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
    case CREATE_ORDER = 'create_order';
}
