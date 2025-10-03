<?php
return [
    // Mail settings
    'smtp_host' => 'smtp.example.com',
    'smtp_user' => 'you@example.com',
    'smtp_pass' => 'yourpassword',
    'smtp_port' => '587',
    'smtp_secure' => 'tls',
    'from_email' => 'you@example.com',
    'from_name'  => 'Game Signup System',

    // Event system defaults
    'max_tables' => '0',
    'default_start_hour' => '18:00',
    'default_end_hour'   => '23:00',

    // System messages
    'message_main' => 'Welcome to our game night!',
    'message_add_game' => 'Add a new game below:',
    'message_add_player' => 'Fill the form to join a game.',

    // Default admin (will be created at install)
    'default_admin_user' => 'admin',
    'default_admin_email' => 'admin@example.com',
    'default_admin_pass' => 'changeme' // plaintext only in defaults
];