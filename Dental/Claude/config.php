<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dental_scout');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');

// Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // dental123

// Application settings
define('APP_NAME', 'Dental Staff Talent Scout');
define('APP_VERSION', '1.0.0');
define('RESULTS_PER_PAGE', 20);

// Social media keywords for talent scouting
$TALENT_KEYWORDS = [
    'dental assistant',
    'dental hygienist',
    'dental office manager',
    'dental receptionist',
    'dental technician',
    'rdh',
    'cda',
    'dental school graduate',
    'dental certification',
    'looking for dental job',
    'dental career',
    'dental employment',
    'dental position',
    'dental opportunity'
];

// Location keywords
$LOCATION_KEYWORDS = [
    'looking for work',
    'job search',
    'career change',
    'new graduate',
    'recently certified',
    'available for hire',
    'seeking employment',
    'job hunting'
];

// Social media platforms to monitor
$PLATFORMS = [
    'linkedin' => [
        'name' => 'LinkedIn',
        'icon' => '💼',
        'weight' => 3
    ],
    'facebook' => [
        'name' => 'Facebook',
        'icon' => '📘',
        'weight' => 2
    ],
    'instagram' => [
        'name' => 'Instagram',
        'icon' => '📸',
        'weight' => 1
    ],
    'twitter' => [
        'name' => 'Twitter',
        'icon' => '🐦',
        'weight' => 2
    ]
];

// Scoring system
define('SCORE_WEIGHTS', [
    'keyword_match' => 10,
    'location_match' => 5,
    'recent_activity' => 3,
    'professional_network' => 5,
    'engagement_level' => 2
]);

// Email configuration (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('FROM_EMAIL', 'your_email@gmail.com');
define('FROM_NAME', 'Dental Talent Scout');

// Timezone
date_default_timezone_set('America/New_York');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>