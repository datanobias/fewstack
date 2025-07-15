<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$db = new Database();

// Get current settings
$settings = $db->fetchOne("SELECT * FROM practice_settings ORDER BY id DESC LIMIT 1");

// Handle form submission
if (isset($_POST['save_settings'])) {
    $data = [
        'practice_name' => sanitize($_POST['practice_name']),
        'location' => sanitize($_POST['location']),
        'positions_needed' => sanitize($_POST['positions_needed']),
        'notification_email' => sanitize($_POST['notification_email']),
        'min_score' => intval($_POST['min_score']),
        'auto_alerts' => isset($_POST['auto_alerts']) ? 1 : 0
    ];
    
    if ($settings) {
        $db->update('practice_settings', $data, 'id = ?', [$settings['id']]);
    } else {
        $db->insert('practice_settings', $data);
    }
    
    $success = "Settings saved successfully!";
    $settings = $db->fetchOne("SELECT * FROM practice_settings ORDER BY id DESC LIMIT 1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Dental Staff Talent Scout</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🦷 Dental Staff Talent Scout</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="candidates.php">All Candidates</a>
                <a href="settings.php" class="active">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="page-header">
            <h2>⚙️ Settings</h2>
        </div>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="settings-section">
            <form method="POST" class="settings-form">
                <div class="form-section">
                    <h3>🏢 Practice Information</h3>
                    
                    <div class="form-group">
                        <label for="practice_name">Practice Name</label>
                        <input type="text" id="practice_name" name="practice_name" 
                               value="<?php echo htmlspecialchars($settings['practice_name'] ?? ''); ?>"
                               placeholder="e.g., Smile Dental Care">
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Practice Location</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars($settings['location'] ?? ''); ?>"
                               placeholder="e.g., New York, NY">
                    </div>
                    
                    <div class="form-group">
                        <label for="positions_needed">Positions Needed</label>
                        <textarea id="positions_needed" name="positions_needed" 
                                  placeholder="e.g., Dental Hygienist, Dental Assistant, Office Manager"><?php echo htmlspecialchars($settings['positions_needed'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔔 Notification Settings</h3>
                    
                    <div class="form-group">
                        <label for="notification_email">Notification Email</label>
                        <input type="email" id="notification_email" name="notification_email" 
                               value="<?php echo htmlspecialchars($settings['notification_email'] ?? ''); ?>"
                               placeholder="your@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="min_score">Minimum Score for Alerts</label>
                        <select id="min_score" name="min_score">
                            <option value="50" <?php echo ($settings['min_score'] ?? 50) == 50 ? 'selected' : ''; ?>>50 (All candidates)</option>
                            <option value="60" <?php echo ($settings['min_score'] ?? 50) == 60 ? 'selected' : ''; ?>>60 (Good candidates)</option>
                            <option value="70" <?php echo ($settings['min_score'] ?? 50) == 70 ? 'selected' : ''; ?>>70 (High quality)</option>
                            <option value="80" <?php echo ($settings['min_score'] ?? 50) == 80 ? 'selected' : ''; ?>>80 (Excellent only)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="auto_alerts" <?php echo ($settings['auto_alerts'] ?? 1) ? 'checked' : ''; ?>>
                            Send automatic email alerts for high-score candidates
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3>🔍 Search Preferences</h3>
                    
                    <div class="form-group">
                        <label>Default Platforms to Search</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="default_platforms[]" value="linkedin" checked disabled> 💼 LinkedIn (Always enabled)</label>
                            <label><input type="checkbox" name="default_platforms[]" value="facebook" checked> 📘 Facebook</label>
                            <label><input type="checkbox" name="default_platforms[]" value="instagram" checked> 📸 Instagram</label>
                            <label><input type="checkbox" name="default_platforms[]" value="twitter" checked> 🐦 Twitter</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Auto-Search Schedule</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="auto_search_daily"> Daily at 9:00 AM</label>
                            <label><input type="checkbox" name="auto_search_weekly"> Weekly on Monday</label>
                            <label><input type="checkbox" name="auto_search_monthly"> Monthly on 1st</label>
                        </div>
                        <small>Automated searches will use your saved search queries and preferences</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3>📊 Data Management</h3>
                    
                    <div class="form-group">
                        <label>Data Retention</label>
                        <select name="data_retention">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90" selected>90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                        <small>Automatically delete candidate data older than selected period</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Export Format</label>
                        <select name="export_format">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_settings" class="btn btn-primary">💾 Save Settings</button>
                    <button type="button" onclick="testNotification()" class="btn btn-secondary">📧 Test Email</button>
                </div>
            </form>
        </div>

        <div class="settings-section">
            <h3>🔧 System Tools</h3>
            
            <div class="tools-grid">
                <div class="tool-card">
                    <h4>🗑️ Clear Data</h4>
                    <p>Remove all candidate data from the system</p>
                    <button onclick="clearAllData()" class="btn btn-danger">Clear All Data</button>
                </div>
                
                <div class="tool-card">
                    <h4>📤 Export All</h4>
                    <p>Export all candidate data to CSV file</p>
                    <button onclick="exportAllData()" class="btn btn-secondary">Export All</button>
                </div>
                
                <div class="tool-card">
                    <h4>🔄 Reset Settings</h4>
                    <p>Reset all settings to default values</p>
                    <button onclick="resetSettings()" class="btn btn-warning">Reset Settings</button>
                </div>
                
                <div class="tool-card">
                    <h4>📊 System Stats</h4>
                    <p>View database and system statistics</p>
                    <button onclick="showStats()" class="btn btn-info">View Stats</button>
                </div>
            </div>
        </div>

        <div class="settings-section">
            <h3>ℹ️ About</h3>
            <div class="about-info">
                <p><strong>Dental Staff Talent Scout</strong> v<?php echo APP_VERSION; ?></p>
                <p>AI-powered recruitment tool for dental practices</p>
                <p>Created for modern dental practices to find qualified staff efficiently</p>
                <p><strong>Support:</strong> support@dentaltalentscout.com</p>
            </div>
        </div>
    </div>

    <script>
        function testNotification() {
            const email = document.getElementById('notification_email').value;
            if (!email) {
                alert('Please enter a notification email first');
                return;
            }
            
            fetch('api/test-notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Error sending test email: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        function clearAllData() {
            if (confirm('Are you sure you want to clear ALL candidate data? This action cannot be undone.')) {
                if (confirm('This will permanently delete all candidates, searches, and alerts. Are you absolutely sure?')) {
                    fetch('api/clear-data.php', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('All data cleared successfully');
                                location.reload();
                            } else {
                                alert('Error clearing data: ' + data.message);
                            }
                        });
                }
            }
        }

        function exportAllData() {
            window.location.href = 'api/export-all.php';
        }

        function resetSettings() {
            if (confirm('Reset all settings to default values?')) {
                fetch('api/reset-settings.php', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Settings reset successfully');
                            location.reload();
                        } else {
                            alert('Error resetting settings: ' + data.message);
                        }
                    });
            }
        }

        function showStats() {
            fetch('api/system-stats.php')
                .then(response => response.json())
                .then(data => {
                    let statsText = `System Statistics:\n\n`;
                    statsText += `Total Candidates: ${data.total_candidates}\n`;
                    statsText += `Total Searches: ${data.total_searches}\n`;
                    statsText += `High Score Candidates: ${data.high_score_candidates}\n`;
                    statsText += `Database Size: ${data.database_size}\n`;
                    statsText += `Last Search: ${data.last_search}\n`;
                    
                    alert(statsText);
                })
                .catch(error => {
                    alert('Error loading stats: ' + error.message);
                });
        }
    </script>
</body>
</html>