<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Firebase integration including
    | Cloud Messaging (FCM) for push notifications.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Project
    |--------------------------------------------------------------------------
    |
    | The default Firebase project to use when no project is specified.
    |
    */
    'default_project' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Configuration
    |--------------------------------------------------------------------------
    |
    | The path to your Firebase service account JSON file.
    | You can either provide a path to the JSON file or the JSON content itself.
    |
    */
    'credentials' => [
        // Option 1: Path to service account JSON file
        'path' => env('FIREBASE_CREDENTIALS_PATH'),
        
        // Option 2: Service account details from environment variables
        'array' => [
            'type' => 'service_account',
            'project_id' => env('FIREBASE_PROJECT_ID'),
            'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
            'private_key' => env('FIREBASE_PRIVATE_KEY') ? str_replace('\\n', "\n", env('FIREBASE_PRIVATE_KEY')) : null,
            'client_email' => env('FIREBASE_CLIENT_EMAIL'),
            'client_id' => env('FIREBASE_CLIENT_ID'),
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => env('FIREBASE_CLIENT_X509_CERT_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Messaging (FCM)
    |
    */
    'messaging' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'sender_id' => env('FIREBASE_SENDER_ID'),
        
        // Default notification settings
        'default_notification' => [
            'title' => 'PetConnect',
            'icon' => '/icons/notification-icon.png',
            'badge' => '/icons/badge-icon.png',
            'click_action' => env('APP_URL'),
        ],

        // Notification channels for different types
        'channels' => [
            'appointments' => [
                'name' => 'Appointment Notifications',
                'description' => 'Notifications about appointments and bookings',
                'sound' => 'default',
                'vibration' => true,
            ],
            'health_alerts' => [
                'name' => 'Health Alerts',
                'description' => 'Important health and medical notifications',
                'sound' => 'urgent',
                'vibration' => true,
                'priority' => 'high',
            ],
            'reminders' => [
                'name' => 'Reminders',
                'description' => 'Vaccination and checkup reminders',
                'sound' => 'gentle',
                'vibration' => false,
            ],
            'marketing' => [
                'name' => 'Updates & Promotions',
                'description' => 'App updates and promotional content',
                'sound' => 'none',
                'vibration' => false,
                'priority' => 'low',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notification Topics
    |--------------------------------------------------------------------------
    |
    | Predefined topics for push notifications
    |
    */
    'topics' => [
        'all_users' => 'all-users',
        'pet_owners' => 'pet-owners',
        'clinics' => 'veterinary-clinics',
        'emergency_alerts' => 'emergency-alerts',
        'maintenance' => 'maintenance-updates',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Realtime Database (if needed for real-time features)
    |
    */
    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Storage (if needed for file storage)
    |
    */
    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Enable Firebase Performance Monitoring
    |
    */
    'performance' => [
        'enabled' => env('FIREBASE_PERFORMANCE_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    |
    | Enable Firebase Analytics integration
    |
    */
    'analytics' => [
        'enabled' => env('FIREBASE_ANALYTICS_ENABLED', false),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
    ],
];