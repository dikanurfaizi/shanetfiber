<?php

return [

    'table' => [
        'no' => 'No',
        'server-name' => 'Server',
        'profile-name' => 'Profile',
        'script-name' => 'Script',
        'schedule-name' => 'Schedule',
        'comment-unpayment' => 'Tag Payment',
        'comment-payment' => 'Tag Unpayment',
        'status' => 'Status',
        'action' => 'Action',
        'activation-date' => 'Activation Date',
        'ros-version' => 'ROS Version',
    ],


    'placeholder' => [
        'name' => 'Name of auto isolir without space',
        'select-server' => 'Select Server',
        'select-profile' => 'Select Profile',
        'select-script' => 'Select Script',
        'select-schedule' => 'Select Schedule',
        'comment-payment' => 'Name of tag payment without space',
        'comment-unpayment' => 'Name of tag unpayment without space',
        'due-date' => 'Due Date',
        'select-auto-isolir-option' => 'Select option for auto isolir',
        'select-auto-isolir-due-date' => 'Due date',
        'select-auto-isolir-activation-date' => 'Activation date',
        'select-version-ros' => 'Select version Router OS',
        'select-version-67' => '< V.7.10',
        'select-version-710' => '>= V.7.10',
        'nat_dst_address' => 'Ip server customer management. ex. 192.168.1.4',
        'nat_src_address_list' => 'None',
        'nat_dst_address_list' => 'IP that is allowed to access the internet when isolated',
        'proxy_access_src_address' => 'IP address pool pppoe isolir. ex: 192.168.7.0/24',
        'select-proxy-access-action' => 'Select Redirect or Deny',
    ],
    'title' => [
        'index' => 'Auto Isolir',
        'create' => 'Add Auto Isolir',
        'edit' => 'Edit Auto Isolir',
        'edit-nat-proxy' => 'Config Firewall & Proxy',
        'show' => 'View Auto Isolir',
        'delete' => 'Delete Auto Isolir',

    ],
    'header' => [
        'index' => 'Auto Isolir',
        'create' => 'Add Auto Isolir',
        'edit' => 'Edit Auto Isolir',
        'show' => 'View Auto Isolir',
        'edit-nat-proxy' => 'Setting Firewall & Web Proxy',
        'edit-nat' => 'Firewall/NAT Rule',
        'edit-proxy' => 'Proxy / Access',
    ],


    'button' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'save' => 'Save',
        'update' => 'Update',
        'delete' => 'Delete',
        'back' => 'Back',
        'enable' => 'Enable',
        'disable' => 'Disable',
    ],

    'action' => [
        'view' => 'Show',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'force-disable' => 'Force Disable',
    ],
    'status' => [
        'enabled' => 'Enable',
        'disabled' => 'Disable',
    ],


    //==========================================================
    'info' => [
        'whoops' => 'Whoops!',
        'there_were_some_problems_with_your_input' => 'There were some problems with your input.',
    ],

    'label' => [
        'server' => 'Server :',
        'name' => 'Name :',
        'profile' => 'Profile Isolir :',
        'script' => 'Script :',
        'schedule' => 'Schedule :',
        'comment-payment' => 'Tag Payment :',
        'comment-unpayment' => 'Tag Unpayment :',
        'due-date' => 'Due Date :',
        'status' => 'Status :',
        'on-event' => 'On Event :',
        'schedule-run-count' => 'Schedule Run Count :',
        'script-run-count' => 'Script Run Count :',
        'executed' => ':run-count executed ',
        'auto-isolir-option' => 'Auto Isolir Option :',
        'nat_dst_address' => 'Dst Address:',
        'nat_src_address_list' => 'Src Address List:',
        'nat_dst_address_list' => 'Dst Address List:',
        'proxy_access_src_address' => 'Access Src Address:',
        'proxy-access-action' => 'Action',
        'address_list_isolir' => 'Address List Isolir',
    ],




];
