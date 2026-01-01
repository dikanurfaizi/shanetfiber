<?php

return [
    'pay-later' => 'Pay Later',
    'bank-transfer' => 'Bank Transfer',
    'table' => [
        'no' => 'No',
        'number' => 'Billing No.',
        'customer-name' => 'Name',
        'periode' => 'Period',
        'address' => 'Address',
        'package-name' => 'Package',
        'activation' => 'Activation',
        'bill' => 'Bill',
        'bill-state' => 'Bill Status',
        'package-price' => 'Price',
        'action' => 'Action',
        'payment-methode' => 'Payment Method',
        'duedate' => 'Due Date',
        'customer_id' => 'Cust ID',
        'teller-name' => 'Teller Name'
    ],
    'placeholder' => [
        'search-customer-name' => 'All Customers',
        'search-month' => 'All Months',
        'search-address' => 'All Address',
        'search-without-address' => 'Without Address',
        'search-year' => 'All Years',
        'search-teller' => 'All Tellers',
        'search-payment-methode' => 'All Payment Method',
        'search-account-bank' => 'All Bank',
        'search-admin' => 'All Admin',
        'select' => [
            'status' => 'Status',
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'periode' => 'Please select periode',
            'paylater' => 'Pay Later',
            'notactive' => 'Not Active Period'
        ],
        'search-period' => 'Period for export'
    ],
    'title' => [
        'index' => 'Manage Billings',
        'create' => 'Create New Billing',
        'edit' => 'Edit Billing',
        'show' => 'View Billing',
        'create-one' => 'Add New Billing',
        'delete' => 'Delete Billing Period',
    ],
    'header' => [
        'index' => 'Active Bills',
        'create' => 'Add New Billing',
        'create-one' => 'Add Billing to Existing Period',
        'edit' => 'Edit Billing',
        'show' => 'Billing Detail',
        'paid-off' => 'Paid Off',
        'search' => 'Search',
        'delete' => 'Delete billing periode :billingperiode',
        'history-index' => 'Historical Bills'
    ],
    'label' => [
        'periode' => 'Period:',
        'automatic' => 'Automatic Billing?',
        'enable-automatic' => 'Enable Automatic Billing',
        'select-billing-date' => 'Select Billing Date (1–28)',
        'select-date' => 'Select Date',
    ],

    'button' => [
        'created-new-bill' => 'Create New Bills',
        'add-new-bill' => 'Add New Bill',
        'edit' => 'Edit Bill',
        'save-add' => 'Add Bills',
        'save-create' => 'Create Bills',
        'update' => 'Update Bill',
        'delete' => 'Delete Bill',
        'back' => 'Back',
        'cancel' => 'Cancel',
        'pay' => 'Pay',
        'unpay' => 'Unpay',
        'search' => 'Search Bills',
        'view' => 'Show',
        'all-payment' => 'All',
        'waiting-payment' => 'Waiting',
        'paid-off' => 'Paid Off',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'paid-off' => 'Paid Off',
        'unpaid' => 'Unpaid',
        'create-order' => 'Bayar Sekarang',
        'view-order' => 'View Order',
        'pay-later' => 'Pay Later',
        'activation' => 'Activation',
        'continue-payment' => 'Lanjutkan Pembayaran',
        'export-all-receipt' => 'Export All Receipt',
    ],

    'action' => [
        'view' => 'View this billing',
        'edit' => 'Edit this billing',
        'delete' => 'Delete this billing',
    ],

    'info' => [
        'whoops' => 'Whoops!',
        'there_were_some_problems_with_your_input' => 'There were some problems with your input.',
        'are_you_sure_to_delete_billing' => 'Are you sure to delete billing periode :billingperiode?',
        'warning-delete' => 'Warning!!! Deleting a billing will delete their all bill in periode :billingperiode.',
        'after-delete' => 'After delete, it cannot be recovered.',
        'press-button' => 'Press :button-delete button to delete billing periode :billingperiode and press :button-cancel to cancel this operation.',
    ],
    'state' => [
        'belum-lunas' => 'Unpaid',
        'lunas' => 'Paid Off',
        'paylater' => 'Pay Later',
        'belum-lunas-lainnya' => 'and :count_bill_unpaid more bills unpaid',
        'tidak-ada-pemakaian' => 'Not Active Period',
        'confirm-on' => 'confirm on '
    ],
    'status' => [
        'waiting-payment' => 'Waiting Payment',
        'paid-off' => 'Paid Off',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
    ],
    'mikrotik' => [
        'comment-belum-lunas' => 'belum_lunas',
        'comment-lunas' => 'lunas',
        'profile_isolir' => 'profile_isolir',
    ],
    'invoice' => [
        'header' => [
            'title' => 'INVOICE',
            'invoice_no' => 'Invoice No',
            'printed'   => 'Printed',
            'due_date' => 'Due Date'
        ],
        'info' => [
            'bill_to' => 'Bill To',
            'issued_by' => 'Issued By',
            'company_name'  => 'Shanum Network Fiber'
        ],
        'period' => [
            'billing_period' => 'Billing Period'
        ],
        'table' => [
            'item' => 'Item',
            'qty' => 'Qty',
            'price' => 'Price',
            'amount' => 'Amount'
        ],
        'summary' => [
            'subtotal' => 'Sub Total',
            'discount' => 'Discount',
            'tax' => 'PPN',
            'total' => 'Total Due'
        ],
        'payment_info' => [
            'instruction' => 'Payment Instruction',
            'transfer' => 'Silakan transfer jumlah total ke salah satu rekening berikut:'
        ],
        'footer' => [
            'copyright' => 'Invoice generated by Shanum Network Fiber',
            'notes' => 'This invoice is system generated and does not require signature'
        ]
    ]

];
