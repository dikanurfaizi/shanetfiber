<?php

return [
    'title' => [
        'primary' => 'Install Customer Management',
        'admin' => 'Create an Admin Account!',
        'greeting' => 'Welcome friend,',
        'welcome_message' => 'Managing Mikrotik customers with Customer Management is now much easier.',
        'license_notice' => 'This source code is free and commercial resale is strictly prohibited.',
        'important' => 'Important!!!',
    ],

    'db' => [
        'exists_warning' => 'Oops... A database named :db already exists on your server. Continuing with the installation will permanently erase all existing data inside it.',
        'success' => 'Database <b class="text-success">:db</b> has been successfully created.',
        'error' => 'Oops... Sorry, we could not find a database named <b class="text-danger">:db</b>. Perhaps during the initial installation we encountered an issue creating it. Please restart from the beginning...',
    ],

    'form' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'password' => 'Password',
        'repeat_password' => 'Repeat Password',
        'agreement' => 'I understand and agree to proceed with the installation terms.',
        'strength_weak' => 'Weak',
        'strength_medium' => 'Medium',
        'strength_strong' => 'Strong',
        'match_success' => '✔ Passwords match',
        'match_error' => '✖ Passwords do not match',
        'sequential_warning' => ' (Sequential numbers not allowed)',
    ],

    'validation' => [
        'name_required' => 'The full name field is required.',
        'email_required' => 'The email address field is required.',
        'email_invalid' => 'Please enter a valid email address.',
        'email_unique' => 'This email is already registered.',
        'password_required' => 'The password field is required.',
        'password_confirmed' => 'The password confirmation does not match.',
        'password_min' => 'The password must be at least 8 characters.',
        'password_sequential' => 'The password cannot contain sequential numbers (e.g. 123, 4321).',
        'password_date_format' => 'The password cannot use a date format (8 consecutive digits like ddmmyyyy).',
    ],

    'button' => [
        'install' => 'Install Now',
        'overwrite' => 'Proceed Installation & Overwrite Existing Database!',
        'continue' => 'Continue',
        'restart' => 'Start Over',
        'login' => 'Login Admin',
        'create_admin' => 'Create Admin Account',
    ],

    'finish' => [
        'desc_1' => 'Customer Management installation has been completed successfully.',
        'desc_2' => 'You must remove the installer route URL.',
        'desc_3' => 'Open the file',
        'desc_4' => 'or',
        'desc_5' => 'then delete the following code:',
    ],
];
