<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

requireLogin();

$db = new Database();

// Handle filtering
$where = "1=1";
$params = [];

if (isset($_GET['position']) && !empty($_GET['position'])) {
    $where .= " AND position LIKE ?";
    $params[] = '%' . $_GET['position'] . '%';
}

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where .= " AND location LIKE ?";
    $params[] = '%' . $_GET['location'] . '%';
}

if (isset($_GET['platform']) && !empty($_GET['platform'])) {
    $where .= " AND platform = ?";
    $params[] = $_GET['platform'];
}

if (isset($_GET['min_score']) && !empty($_GET['min_score'])) {
    $where .= " AND score >= ?";
    $params[] = intval($_GET['min_score']);
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = RESULTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get total count
$totalCount = $db->fetchOne("SELECT COUNT(*) as count FROM candidates WHERE $where", $params)['count'];
$totalPages = ceil($totalCount / $limit);

// Get candidates
$candidates = $db->fetchAll(
    "SELECT * FROM candidates WHERE $where ORDER BY score DESC, created_at DESC LIMIT $limit OFFSET $offset",
    $params
);

// Handle status update
if (isset($_POST['update_status'])) {
    $candidateId = intval($_POST['candidate_id']);
    $newStatus = sanitize($_POST['status']);
    
    $db->update('candidates', ['status' => $newStatus], 'id = ?', [$candidateId]);
    
    // Add alert
    $db->insert('alerts', [
        'candidate_id' => $candidateId,
        'message' => "Status updated to: $newStatus",
        'type' => 'info'
    ]);
    
    header('Location: candidates.php?' . $_SERVER['QUERY_STRING']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Candidates - Dental Staff Talent Scout</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🦷 Dental Staff Talent Scout</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="candidates.php" class="active">All Candidates</a>
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="page-header">
            <h2>👥 All Candidates (<?php echo $totalCount; ?>)</h2>
            <div class="page-actions">
                <button onclick="exportCandidates()" class="btn btn-secondary">📊 Export CSV</button>
                <button onclick="bulkAction()" class="btn btn-primary">📋 Bulk Actions</button>
            </div>
        </div>

        <div class="filters-section">
            <h3>🔍 Filter Candidates</h3>
            <form method="GET" class="filters-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="position">Position</label>
                        <select id="position" name="position">
                            <option value="">All Positions</option>
                            <option value="dental assistant" <?php echo ($_GET['position'] ?? '') === 'dental assistant' ? 'selected' : ''; ?>>Dental Assistant</option>
                            <option value="dental hygienist" <?php echo ($_GET['position'] ?? '') === 'dental hygienist' ? 'selected' : ''; ?>>Dental Hygienist</option>
                            <option value="dental office manager" <?php echo ($_GET['position'] ?? '') === 'dental office manager' ? 'selected' : ''; ?>>Office Manager</option>
                            <option value="dental receptionist" <?php echo ($_GET['position'] ?? '') === 'dental receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                            <option value="dental technician" <?php echo ($_GET['position'] ?? '') === 'dental technician' ? 'selected' : ''; ?>>Dental Technician</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" 
                               placeholder="e.g., New York" 
                               value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="platform">Platform</label>
                        <select id="platform" name="platform">
                            <option value="">All Platforms</option>
                            <option value="linkedin" <?php echo ($_GET['platform'] ?? '') === 'linkedin' ? 'selected' : ''; ?>>💼 LinkedIn</option>
                            <option value="facebook" <?php echo ($_GET['platform'] ?? '') === 'facebook' ? 'selected' : ''; ?>>📘 Facebook</option>
                            <option value="instagram" <?php echo ($_GET['platform'] ?? '') === 'instagram' ? 'selected' : ''; ?>>📸 Instagram</option>
                            <option value="twitter" <?php echo ($_GET['platform'] ?? '') === 'twitter' ? 'selected' : ''; ?>>🐦 Twitter</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_score">Min Score</label>
                        <select id="min_score" name="min_score">
                            <option value="">Any Score</option>
                            <option value="80" <?php echo ($_GET['min_score'] ?? '') === '80' ? 'selected' : ''; ?>>80+ (Excellent)</option>
                            <option value="60" <?php echo ($_GET['min_score'] ?? '') === '60' ? 'selected' : ''; ?>>60+ (Good)</option>
                            <option value="40" <?php echo ($_GET['min_score'] ?? '') === '40' ? 'selected' : ''; ?>>40+ (Fair)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="candidates.php" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="no-results">
                <p>No candidates found matching your criteria.</p>
                <a href="dashboard.php" class="btn btn-primary">Search for Talent</a>
            </div>
        <?php else: ?>
            <div class="candidates-section">
                <div class="candidates-table">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Location</th>
                                <th>Platform</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><input type="checkbox" name="candidate_ids[]" value="<?php echo $candidate['id']; ?>"></td>
                                    <td>
                                        <div class="candidate-name">
                                            <strong><?php echo htmlspecialchars($candidate['name']); ?></strong>
                                            <div class="candidate-meta">
                                                Last active: <?php echo formatTimeAgo($candidate['last_activity']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['location']); ?></td>
                                    <td><?php echo $PLATFORMS[$candidate['platform']]['icon']; ?></td>
                                    <td>
                                        <span class="score-badge" style="background-color: <?php echo getScoreColor($candidate['score']); ?>">
                                            <?php echo $candidate['score']; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-select">
                                                <option value="new" <?php echo $candidate['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                                <option value="contacted" <?php echo $candidate['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                                <option value="interview" <?php echo $candidate['status'] === 'interview' ? 'selected' : ''; ?>>Interview</option>
                                                <option value="hired" <?php echo $candidate['status'] === 'hired' ? 'selected' : ''; ?>>Hired</option>
                                                <option value="rejected" <?php echo $candidate['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo formatTimeAgo($candidate['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo htmlspecialchars($candidate['profile_url']); ?>" 
                                               target="_blank" class="btn btn-sm">View</a>
                                            <button onclick="viewCandidate(<?php echo $candidate['id']; ?>)" 
                                                    class="btn btn-sm btn-primary">Details</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-secondary">← Previous</a>
                        <?php endif; ?>
                        
                        <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-secondary">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Candidate Details Modal -->
    <div id="candidateModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="candidateDetails"></div>
        </div>
    </div>

    <script>
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="candidate_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // View candidate details
        function viewCandidate(candidateId) {
            // Fetch candidate details via AJAX
            fetch('api/candidate-details.php?id=' + candidateId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('candidateDetails').innerHTML = `
                        <h3>${data.name}</h3>
                        <p><strong>Position:</strong> ${data.position}</p>
                        <p><strong>Location:</strong> ${data.location}</p>
                        <p><strong>Platform:</strong> ${data.platform}</p>
                        <p><strong>Score:</strong> ${data.score}%</p>
                        <p><strong>Bio:</strong> ${data.bio}</p>
                        <p><strong>Skills:</strong> ${data.skills}</p>
                        <p><strong>Profile URL:</strong> <a href="${data.profile_url}" target="_blank">View Profile</a></p>
                    `;
                    document.getElementById('candidateModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Error loading candidate details');
                });
        }

        // Export candidates
        function exportCandidates() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="candidate_ids[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                alert('Please select candidates to export');
                return;
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'api/export-candidates.php';
            
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'candidate_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Bulk actions
        function bulkAction() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="candidate_ids[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                alert('Please select candidates for bulk action');
                return;
            }
            
            const action = prompt('Enter action (contacted, interview, hired, rejected):');
            if (action && ['contacted', 'interview', 'hired', 'rejected'].includes(action)) {
                // Implement bulk update
                alert(`Bulk action "${action}" will be implemented for ${selectedIds.length} candidates`);
            }
        }

        // Modal functionality
        const modal = document.getElementById('candidateModal');
        const span = document.getElementsByClassName('close')[0];

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>