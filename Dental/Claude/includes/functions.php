<?php
// Security functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// Talent scouting functions
function searchLinkedIn($query, $location = '') {
    // Simulated LinkedIn search (replace with actual API or scraping)
    $mockResults = [
        [
            'name' => 'Sarah Johnson',
            'position' => 'Dental Hygienist',
            'location' => 'New York, NY',
            'bio' => 'Experienced dental hygienist seeking new opportunities. RDH certified.',
            'profile_url' => 'https://linkedin.com/in/sarahjohnson',
            'platform' => 'linkedin',
            'score' => 85,
            'skills' => 'Dental hygiene, Patient care, Radiology',
            'last_activity' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'name' => 'Michael Chen',
            'position' => 'Dental Assistant',
            'location' => 'Los Angeles, CA',
            'bio' => 'Recent dental assistant graduate looking for my first position.',
            'profile_url' => 'https://linkedin.com/in/michaelchen',
            'platform' => 'linkedin',
            'score' => 78,
            'skills' => 'Dental assisting, Sterilization, Patient records',
            'last_activity' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
    
    return $mockResults;
}

function searchFacebook($query, $location = '') {
    // Simulated Facebook search
    $mockResults = [
        [
            'name' => 'Emma Wilson',
            'position' => 'Dental Office Manager',
            'location' => 'Chicago, IL',
            'bio' => 'Experienced dental office manager with 8 years in the field.',
            'profile_url' => 'https://facebook.com/emmawilson',
            'platform' => 'facebook',
            'score' => 92,
            'skills' => 'Office management, Scheduling, Insurance billing',
            'last_activity' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ]
    ];
    
    return $mockResults;
}

function searchInstagram($query, $location = '') {
    // Simulated Instagram search
    $mockResults = [
        [
            'name' => 'Jessica Martinez',
            'position' => 'Dental Hygienist',
            'location' => 'Miami, FL',
            'bio' => 'Passionate about oral health education. Just graduated!',
            'profile_url' => 'https://instagram.com/jessicamartinez',
            'platform' => 'instagram',
            'score' => 65,
            'skills' => 'Dental hygiene, Education, Social media',
            'last_activity' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ]
    ];
    
    return $mockResults;
}

function searchTwitter($query, $location = '') {
    // Simulated Twitter search
    $mockResults = [
        [
            'name' => 'David Brown',
            'position' => 'Dental Technician',
            'location' => 'Houston, TX',
            'bio' => 'Dental lab technician with expertise in crowns and bridges.',
            'profile_url' => 'https://twitter.com/davidbrown',
            'platform' => 'twitter',
            'score' => 73,
            'skills' => 'Lab work, Crowns, Bridges, CAD/CAM',
            'last_activity' => date('Y-m-d H:i:s', strtotime('-6 hours'))
        ]
    ];
    
    return $mockResults;
}

function calculateScore($candidate) {
    global $TALENT_KEYWORDS, $LOCATION_KEYWORDS;
    
    $score = 0;
    $text = strtolower($candidate['bio'] . ' ' . $candidate['skills']);
    
    // Keyword matching
    foreach ($TALENT_KEYWORDS as $keyword) {
        if (strpos($text, strtolower($keyword)) !== false) {
            $score += SCORE_WEIGHTS['keyword_match'];
        }
    }
    
    // Location matching
    foreach ($LOCATION_KEYWORDS as $keyword) {
        if (strpos($text, strtolower($keyword)) !== false) {
            $score += SCORE_WEIGHTS['location_match'];
        }
    }
    
    // Recent activity bonus
    $lastActivity = strtotime($candidate['last_activity']);
    $hoursAgo = (time() - $lastActivity) / 3600;
    
    if ($hoursAgo < 24) {
        $score += SCORE_WEIGHTS['recent_activity'] * 3;
    } elseif ($hoursAgo < 168) { // 1 week
        $score += SCORE_WEIGHTS['recent_activity'] * 2;
    } elseif ($hoursAgo < 720) { // 1 month
        $score += SCORE_WEIGHTS['recent_activity'];
    }
    
    // Platform weight
    global $PLATFORMS;
    if (isset($PLATFORMS[$candidate['platform']])) {
        $score += $PLATFORMS[$candidate['platform']]['weight'] * 5;
    }
    
    return min($score, 100); // Cap at 100
}

function performSearch($query, $location = '', $platforms = ['linkedin', 'facebook', 'instagram', 'twitter']) {
    $allResults = [];
    
    foreach ($platforms as $platform) {
        switch ($platform) {
            case 'linkedin':
                $results = searchLinkedIn($query, $location);
                break;
            case 'facebook':
                $results = searchFacebook($query, $location);
                break;
            case 'instagram':
                $results = searchInstagram($query, $location);
                break;
            case 'twitter':
                $results = searchTwitter($query, $location);
                break;
            default:
                $results = [];
        }
        
        foreach ($results as $result) {
            $result['score'] = calculateScore($result);
            $allResults[] = $result;
        }
    }
    
    // Sort by score
    usort($allResults, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    return $allResults;
}

function saveCandidates($candidates, $db) {
    foreach ($candidates as $candidate) {
        // Check if candidate already exists
        $existing = $db->fetchOne(
            "SELECT id FROM candidates WHERE profile_url = ?",
            [$candidate['profile_url']]
        );
        
        if (!$existing) {
            $db->insert('candidates', [
                'name' => $candidate['name'],
                'location' => $candidate['location'],
                'position' => $candidate['position'],
                'platform' => $candidate['platform'],
                'profile_url' => $candidate['profile_url'],
                'bio' => $candidate['bio'],
                'skills' => $candidate['skills'],
                'score' => $candidate['score'],
                'last_activity' => $candidate['last_activity']
            ]);
        } else {
            // Update existing candidate
            $db->update('candidates', [
                'score' => $candidate['score'],
                'last_activity' => $candidate['last_activity'],
                'bio' => $candidate['bio'],
                'skills' => $candidate['skills']
            ], 'id = ?', [$existing['id']]);
        }
    }
}

function sendNotification($email, $subject, $message) {
    // Simple mail function (replace with proper SMTP in production)
    $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($email, $subject, $message, $headers);
}

function formatTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 604800) return floor($time/86400) . 'd ago';
    if ($time < 2419200) return floor($time/604800) . 'w ago';
    
    return date('M j, Y', strtotime($datetime));
}

function getScoreColor($score) {
    if ($score >= 80) return '#28a745';
    if ($score >= 60) return '#ffc107';
    if ($score >= 40) return '#fd7e14';
    return '#dc3545';
}

function getScoreLabel($score) {
    if ($score >= 80) return 'Excellent';
    if ($score >= 60) return 'Good';
    if ($score >= 40) return 'Fair';
    return 'Poor';
}
?>