<?php

return [
    'mikrotik' => [
        'not_found'         => 'MikroTik router data not found for this billing.',
        'connection_failed' => 'Connection to MikroTik router failed. Please check your network status or API configuration.',
        'user_offline'      => "PPP user ':username' is not detected as online on the router. Please ensure the customer's modem is powered on.",
        'query_failed'      => "Failed to verify the active status of customer ':username' on the router.",
    ],
    'success' => [
        'no_usage'  => "Status billing for :name successfully changed to 'No Usage' for this period.",
        'processed' => 'Payment for :name processed successfully.',
    ],
    'errors' => [
        'failed'    => 'Payment process failed.',
    ]
];
