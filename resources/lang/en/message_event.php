<?php

return [
    'header' => [],
    'title' => 'Message Events Log',
    'app_title' => 'Message Events',

    'table' => [
        'hash' => '#',
        'channel' => 'Channel',
        'customer' => 'Customer',
        'phone' => 'Phone',
        'message_type' => 'Message Type',
        'status' => 'Status',
        'sent_at' => 'Sent At',
        'scheduled_at' => 'Scheduled At',
        'action' => 'Action',
    ],

    'label' => [
        'all_channel' => 'All Channels',
        'all_message_type' => 'All Message Types',
        'quick_filter' => 'Quick Filter:',
        'label_response' => 'Response:',
        'realtime' => 'REALTIME',
        'unknown_customer' => 'Unknown Customer',
        'message_id' => 'Message ID',
        'trace_id' => 'Trace ID'
    ],

    'placeholder' => [
        'placeholder_search' => 'Name / Number',
    ],

    'button' => [
        'advanced_filter' => 'Advanced Filter',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'btn_detail' => 'Detail',
        'btn_view_detail' => 'View Detail',
        'btn_retry' => 'Retry',
    ],

    'state' => [
        'status_failed' => 'Failed',
        'status_pending' => 'Pending',
        'status_success' => 'Success',
        'status_all' => 'All',
        'no_events' => 'No message events found',
    ],

    'db_status' => [
        'success' => 'SUCCESS',
        'failed'  => 'FAILED',
        'pending' => 'PENDING',
    ],
];
