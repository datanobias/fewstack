<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$db = new Database();
$db->createTables();

// Handle search
$results = [];
$searchPerformed = false;

if (isset($_POST['search'])) {
    $query = sanitize($_POST['query']);
    $location = sanitize($_POST['location']);
    $platforms = $_POST['platforms'] ?? ['linkedin', 'facebook', 'instagram', 'twitter'];
    
    $results = performSearch($query, $location, $platforms);
    saveCandidates($results, $db);
    $searchPerformed = true;
    
    // Save search history
    $db->insert('searches', [
        'query' => $query,
        'location' => $location,
        'results_count' => count($results)
    ]);
}

// Get recent candidates
$recentCandidates = $db->fetchAll(
    "SELECT * FROM candidates ORDER BY created_at DESC LIMIT 10"
);

// Get stats
$stats = [
    'total_candidates' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates")['count'],
    'high_score_candidates' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates WHERE score >= 80")['count'],
    'recent_activity' => $db->fetchOne("SELECT COUNT(*) as count FROM candidates WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'],
    'total_searches' => $db->fetchOne("SELECT COUNT(*) as count FROM searches")['count']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dental Staff Talent Scout</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🦷 Dental Staff Talent Scout</h1>
            <nav>
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="candidates.php">All Candidates</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_candidates']; ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['high_score_candidates']; ?></div>
                <div class="stat-label">High Score (80+)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['recent_activity']; ?></div>
                <div class="stat-label">Active This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_searches']; ?></div>
                <div class="stat-label">Total Searches</div>
            </div>
        </div>

        <div class="search-section">
            <h2>🔍 Search for Talent</h2>
            
            <form method="POST" class="search-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="query">Search Keywords</label>
                        <input type="text" id="query" name="query" 
                               placeholder="e.g., dental assistant, dental hygienist" 
                               value="<?php echo $_POST['query'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" 
                               placeholder="e.g., New York, NY" 
                               value="<?php echo $_POST['location'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Platforms to Search</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="platforms[]" value="linkedin" checked> 💼 LinkedIn</label>
                        <label><input type="checkbox" name="platforms[]" value="facebook" checked> 📘 Facebook</label>
                        <label><input type="checkbox" name="platforms[]" value="instagram" checked> 📸 Instagram</label>
                        <label><input type="checkbox" name="platforms[]" value="twitter" checked> 🐦 Twitter</label>
                    </div>
                </div>
                
                <button type="submit" name="search" class="btn btn-primary">🔍 Search Talent</button>
            </form>
        </div>

        <?php if ($searchPerformed): ?>
        <div class="results-section">
            <h2>🎯 Search Results (<?php echo count($results); ?> found)</h2>
            
            <?php if (empty($results)): ?>
                <div class="no-results">
                    <p>No candidates found for your search criteria. Try different keywords or locations.</p>
                </div>
            <?php else: ?>
                <div class="candidates-grid">
                    <?php foreach ($results as $candidate): ?>
                        <div class="candidate-card">
                            <div class="candidate-header">
                                <h3><?php echo htmlspecialchars($candidate['name']); ?></h3>
                                <div class="score-badge" style="background-color: <?php echo getScoreColor($candidate['score']); ?>">
                                    <?php echo $candidate['score']; ?>%
                                </div>
                            </div>
                            
                            <div class="candidate-info">
                                <p><strong>Position:</strong> <?php echo htmlspecialchars($candidate['position']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($candidate['location']); ?></p>
                                <p><strong>Platform:</strong> <?php echo $PLATFORMS[$candidate['platform']]['icon']; ?> <?php echo $PLATFORMS[$candidate['platform']]['name']; ?></p>
                                <p><strong>Last Active:</strong> <?php echo formatTimeAgo($candidate['last_activity']); ?></p>
                            </div>
                            
                            <div class="candidate-bio">
                                <p><?php echo htmlspecialchars(substr($candidate['bio'], 0, 150)); ?><?php echo strlen($candidate['bio']) > 150 ? '...' : ''; ?></p>
                            </div>
                            
                            <div class="candidate-skills">
                                <strong>Skills:</strong> <?php echo htmlspecialchars($candidate['skills']); ?>
                            </div>
                            
                            <div class="candidate-actions">
                                <a href="<?php echo htmlspecialchars($candidate['profile_url']); ?>" 
                                   target="_blank" class="btn btn-secondary">View Profile</a>
                                <button onclick="contactCandidate('<?php echo htmlspecialchars($candidate['name']); ?>')" 
                                        class="btn btn-primary">Contact</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="recent-section">
            <h2>📋 Recent Candidates</h2>
            
            <?php if (empty($recentCandidates)): ?>
                <div class="no-results">
                    <p>No candidates found yet. Start by searching for talent above!</p>
                </div>
            <?php else: ?>
                <div class="candidates-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Location</th>
                                <th>Platform</th>
                                <th>Score</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCandidates as $candidate): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['location']); ?></td>
                                    <td><?php echo $PLATFORMS[$candidate['platform']]['icon']; ?></td>
                                    <td>
                                        <span class="score-badge" style="background-color: <?php echo getScoreColor($candidate['score']); ?>">
                                            <?php echo $candidate['score']; ?>%
                                        </span>
                                    </td>
                                    <td><?php echo formatTimeAgo($candidate['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($candidate['profile_url']); ?>" 
                                           target="_blank" class="btn btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function contactCandidate(name) {
            alert('Contact feature coming soon! For now, click "View Profile" to contact ' + name + ' directly.');
        }
        
        // Auto-refresh search results every 30 seconds
        let searchInterval;
        
        function startAutoRefresh() {
            if (document.querySelector('.results-section')) {
                searchInterval = setInterval(function() {
                    // Only refresh if we have search results
                    console.log('Auto-refreshing search results...');
                }, 30000);
            }
        }
        
        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', startAutoRefresh);
    </script>
</body>
</html>