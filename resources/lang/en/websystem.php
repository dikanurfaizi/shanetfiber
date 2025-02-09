<?php

return [
    'title' => 'Welcome Customers <br> "ShaNet Fiber"',
    'version' => 'Version: ',
    'new-version' => 'New Version: ',
    'header' => [
        'index' => 'Settings',
        'edit' => 'Change Setting',
        'customer' => 'Configuration Customer'
    ],
    'label' => [
        'web-title' => 'Web Title:',
        'name-profile-isolir' => 'Name of Profile Isolir:',
        'comment-payment' => 'Comment Payment:',
        'comment-unpayment' => 'Comment Unpayment:',
        'due-date' => 'Due Date:',
        'enable-tax-button' => 'Enable Tax?',
        'tax-rate' => 'Tax Rate',
        'email' => 'Email',
        'address' => 'Address',
        'city' => 'City',
        'postal_code' => 'Postal Code',
        'phone' => 'Phone',
        'customer_code' => 'Customer Code',
        'long_customer_code' => 'Long Customer Code'

    ],
    'info' => [
        'comment-payment' => 'This is used to mark user secrets who have paid on Mikrotik.',
        'comment-unpayment' => 'This is used to mark user secrets who have unpaid on Mikrotik.',
        'profile-isolir' => 'This is used to set a customer isolation profile.',
        'due-date' => 'Determine the payment date.',
        'every-due-date' => 'Every date :due_date',
        'tax-rate' => 'Please write without %, default in Indonesia 12.'

    ],
    'placeholder' => [
        'web-title' => 'Web Title',
        'tax-rate' => 'Ex: 11',
        'email' => 'Email',
        'address' => 'Address',
        'city' => 'City',
        'postal_code' => 'Postal Code',
        'phone' => 'Phone',
        'customer_code' => 'Customer Code',
        'long_customer_code' => 'Long Customer Code'

    ],
    'button' => [
        'edit' => 'Edit',
        'update' => 'Update',
        'update-new-version' => 'Update to Version :version',
        'cancel' => 'Cancel',
    ],

    'common' => [
        'name' => 'Name',
        'merchant_id' => 'Merchant Id',
        'development-mode' => 'Development Mode',
        'production-mode' => 'Production Mode',
        'disable' => 'Disable',
        'enable' => 'Enable',
        'placeholder' => [
            'name' => 'Name',
        ],
    ],

    'midtran' => [
        'header-edit' => 'Configuration Midtrans',
        'name' => 'Name',
        'merchat_id' => 'Merchant Id',
        'server_key' => 'Production Server Key',
        'client_key' => 'Production Client Key',
        'sandbox_server_key' => 'Sandbox Server Key',
        'sandbox_client_key' => 'Sandbox Client Key',
        'development-mode' => 'Development Mode',
        'production-mode' => 'Production Mode',
        'disable' => 'Disable',
        'enable' => 'Enable',
        'placeholder' => array_merge(
            ['merchant_id' => 'Gxxxxxxxxxx'],
            ['server_key' => 'Mid-server-xxxxxxxxxxx'],
            ['client_key' => 'Mid-client-xxxxxxxxxxx'],
            ['sandbox_server_key' => 'SB-Mid-server-xxxxxxxxxxx'],
            ['sandbox_client_key' => 'SB-Mid-client-xxxxxxxxxxx'],
            ['name' => 'Name']
        ),
    ],

    'tripay' => [
        'header-edit' => 'Configuration Tripays',
        'name' => 'Name',
        'merchat_id' => 'Merchant Id',
        'private_key' => 'Production Private Key',
        'api_key' => 'Production API Key',
        'sandbox_private_key' => 'Sandbox Private Key',
        'sandbox_api_key' => 'Sandbox API Key',
        'development-mode' => 'Development Mode',
        'production-mode' => 'Production Mode',
        'disable' => 'Disable',
        'enable' => 'Enable',
        'placeholder' => array_merge(
            ['merchant_id' => 'Txxxxx'],
            ['private_key' => 'xxxxx-xxxxx-xxxxx-xxxxx-xxxxx'],
            ['api_key' => 'PROD-xxxxxxxxxx'],
            ['sandbox_private_key' => 'xxxxx-xxxxx-xxxxx-xxxxx-xxxxx'],
            ['sandbox_api_key' => 'DEV-xxxxxxxxxx'],
            ['name' => 'Name']
        ),
    ]

];
