<?php

return [
    'title' => [
        'export' => 'Export Customer to Router',
        'comment' => 'Sync Comment to Router'
    ],
    'message' => [
        'warning' => 'Warning! Exporting data will overwrite or duplicate customer accounts on the target router.',
        'comment' => 'Update customer comment status (Payment/Unpayment) on a specific Mikrotik router based on the active period billing data.',
        'select_source_server' => 'Please select the source server.',
        'select_destination_server' => 'Please select the destination server.',
        'select_target_server' => 'Please select the target server for synchronization.',
        'processing_export' => 'Exporting data to router...',
        'processing_sync' => 'Synchronizing comments to router...',
    ],
    'placeholder' => [
        'select_source_server' => 'Select Source Server',
        'select_destination_server' => 'Select Destination Server',
        'select_target_server' => 'Select Target Server for Sync',
    ],
    'button' => [
        'export_customer_data' => 'Export Customer Data',
        'export_comment_data' => 'Sync Comment Data',
    ],
    'label' => [
        'source_server' => 'Source Server',
        'destination_server' => 'Destination Server',
        'target_server' => 'Target Server',
    ],
];
