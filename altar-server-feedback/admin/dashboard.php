<?php
/**
 * admin/dashboard.php
 * ---------------------------------------------------------------------
 * Protected page (see includes/auth_check.php). Shows summary
 * statistics plus a searchable, filterable table of all feedback.
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$activePage = 'dashboard';

// -----------------------------------------------------------------
// Read search & filter parameters from the URL (GET request)
// -----------------------------------------------------------------
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$ratingFilter = isset($_GET['rating']) ? trim($_GET['rating']) : '';

// -----------------------------------------------------------------
// Build the WHERE clause safely using placeholders
// -----------------------------------------------------------------
$conditions = [];
$params = [];

if ($search !== '') {
    $conditions[] = '(name LIKE :search OR parish_vicariate LIKE :search2)';
    $params[':search']  = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
}

if ($ratingFilter !== '' && in_array($ratingFilter, ['1', '2', '3', '4', '5'], true)) {
    $conditions[] = 'overall_rating = :rating';
    $params[':rating'] = (int) $ratingFilter;
}

$whereSql = '';
if (!empty($conditions)) {
    $whereSql = 'WHERE ' . implode(' AND ', $conditions);
}

// -----------------------------------------------------------------
// Fetch the filtered feedback list
// -----------------------------------------------------------------
$listSql = "SELECT id, name, parish_vicariate, overall_rating, feeling, join_again, submitted_at
            FROM feedback
            $whereSql
            ORDER BY submitted_at DESC";
$stmt = $pdo->prepare($listSql);
$stmt->execute($params);
$feedbackList = $stmt->fetchAll();

// -----------------------------------------------------------------
// Calculate statistics
// -----------------------------------------------------------------
$totalFeedback = (int) $pdo->query('SELECT COUNT(*) FROM feedback')->fetchColumn();

$avgRating = $pdo->query('SELECT AVG(overall_rating) FROM feedback')->fetchColumn();
$avgRating = $avgRating !== null ? round((float) $avgRating, 2) : 0;

$wouldJoinAgain = (int) $pdo->query(
    "SELECT COUNT(*) FROM feedback WHERE join_again IN ('YES!', 'Definitely! See you next time!')"
)->fetchColumn();

$wouldNotJoin = (int) $pdo->query(
    "SELECT COUNT(*) FROM feedback WHERE join_again NOT IN ('YES!', 'Definitely! See you next time!')"
)->fetchColumn();

$uniqueParishes = (int) $pdo->query('SELECT COUNT(DISTINCT parish_vicariate) FROM feedback')->fetchColumn();

$ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$ratingStmt = $pdo->query('SELECT overall_rating, COUNT(*) AS c FROM feedback GROUP BY overall_rating');
foreach ($ratingStmt->fetchAll() as $row) {
    $ratingCounts[(int) $row['overall_rating']] = (int) $row['c'];
}
$fiveStarCount = $ratingCounts[5];
$maxRatingCount = max(1, max($ratingCounts));

/**
 * Small helper to render a "would join again" answer as a colored pill.
 */
function join_again_pill($value) {
    $positive = ['YES!', 'Definitely! See you next time!'];
    $neutral  = ['Maybe'];
    $safeValue = htmlspecialchars($value);
    if (in_array($value, $positive, true)) {
        return '<span class="pill pill-yes">' . $safeValue . '</span>';
    } elseif (in_array($value, $neutral, true)) {
        return '<span class="pill pill-maybe">' . $safeValue . '</span>';
    }
    return '<span class="pill pill-no">' . $safeValue . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Altar Server Month 2026</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/includes/admin_header.php'; ?>

    <div class="admin-wrap">
        <div class="admin-page-title">📊 ALTAR SERVER MONTH 2026 &mdash; Dashboard</div>

        <!-- ============ STATISTICS ============ -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-value"><?php echo $totalFeedback; ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-value"><?php echo $avgRating; ?> / 5</div>
                <div class="stat-label">Average Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🌟</div>
                <div class="stat-value"><?php echo $fiveStarCount; ?></div>
                <div class="stat-label">5-Star Ratings</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💙</div>
                <div class="stat-value"><?php echo $wouldJoinAgain; ?></div>
                <div class="stat-label">Would Join Again</div>
            </div>
        </div>

        <!-- ============ RATING BREAKDOWN ============ -->
        <div class="panel">
            <h2>📈 Rating Breakdown</h2>
            <div class="rating-bars">
                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php $count = $ratingCounts[$star]; $pct = round(($count / $maxRatingCount) * 100); ?>
                    <div class="rating-bar-row">
                        <span class="stars-label"><?php echo str_repeat('⭐', $star); ?></span>
                        <div class="rating-bar-track">
                            <div class="rating-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                        </div>
                        <span class="bar-count"><?php echo $count; ?></span>
                    </div>
                <?php endfor; ?>
            </div>
            <p style="color:var(--ink-muted); font-size:0.85rem; margin-top:16px; margin-bottom:0;">
                Would join again: <strong style="color:var(--navy);"><?php echo $wouldJoinAgain; ?></strong>
                &nbsp;|&nbsp;
                Would not / unsure: <strong style="color:var(--navy);"><?php echo $wouldNotJoin; ?></strong>
                &nbsp;|&nbsp;
                Parishes/Vicariates represented: <strong style="color:var(--navy);"><?php echo $uniqueParishes; ?></strong>
            </p>
        </div>

        <!-- ============ SEARCH & FILTER + RESPONSES ============ -->
        <div class="panel" id="feedback-table">
            <h2>📋 Feedback Responses</h2>

            <form method="GET" class="filter-bar" id="search-form" onsubmit="return false;">
                <input type="text" id="search-input" name="search" placeholder="🔍 Search name or parish..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="rating" id="rating-filter" aria-label="Filter by rating" onchange="this.form.submit()">
                    <option value="">All Ratings</option>
                    <option value="5" <?php echo $ratingFilter === '5' ? 'selected' : ''; ?>>5 stars</option>
                    <option value="4" <?php echo $ratingFilter === '4' ? 'selected' : ''; ?>>4 stars</option>
                    <option value="3" <?php echo $ratingFilter === '3' ? 'selected' : ''; ?>>3 stars</option>
                    <option value="2" <?php echo $ratingFilter === '2' ? 'selected' : ''; ?>>2 stars</option>
                    <option value="1" <?php echo $ratingFilter === '1' ? 'selected' : ''; ?>>1 star</option>
                </select>
                <a href="dashboard.php" class="btn btn-secondary btn-sm">CLEAR FILTERS</a>
            </form>

            <?php if (empty($feedbackList)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💬</div>
                    <div class="empty-title">No feedback yet</div>
                    <p>Once altar servers start sharing their experiences, their responses will appear here.</p>
                </div>
            <?php else: ?>

                <!-- Desktop table -->
                <div class="table-scroll">
                    <table class="feedback-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Parish/Vicariate</th>
                                <th>Rating</th>
                                <th>Feeling</th>
                                <th>Would Join Again</th>
                                <th>Date Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbackList as $row): ?>
                                <tr>
                                    <td>#<?php echo (int) $row['id']; ?></td>
                                    <td><?php echo $row['name'] !== null && $row['name'] !== '' ? htmlspecialchars($row['name']) : '<span style="color:var(--ink-faint);">Anonymous</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($row['parish_vicariate']); ?></td>
                                    <td><?php echo str_repeat('⭐', (int) $row['overall_rating']); ?></td>
                                    <td><?php echo htmlspecialchars($row['feeling']); ?></td>
                                    <td><?php echo join_again_pill($row['join_again']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($row['submitted_at']))); ?></td>
                                    <td>
                                        <a href="view_feedback.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-secondary btn-sm">VIEW</a>
                                        <button type="button" class="btn btn-danger btn-sm js-delete-btn" data-id="<?php echo (int) $row['id']; ?>">DELETE</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile cards -->
                <div class="feedback-cards">
                    <?php foreach ($feedbackList as $row): ?>
                        <div class="fb-card">
                            <div class="fb-card-top">
                                <div>
                                    <div class="fb-parish"><?php echo htmlspecialchars($row['parish_vicariate']); ?></div>
                                    <div class="fb-name"><?php echo $row['name'] !== null && $row['name'] !== '' ? htmlspecialchars($row['name']) : 'Anonymous'; ?> &middot; #<?php echo (int) $row['id']; ?></div>
                                </div>
                                <div class="fb-stars"><?php echo str_repeat('⭐', (int) $row['overall_rating']); ?></div>
                            </div>
                            <div class="fb-row"><strong>Feeling:</strong> <?php echo htmlspecialchars($row['feeling']); ?></div>
                            <div class="fb-row"><strong>Would join again:</strong> <?php echo join_again_pill($row['join_again']); ?></div>
                            <div class="fb-row"><strong>Submitted:</strong> <?php echo htmlspecialchars(date('M j, Y', strtotime($row['submitted_at']))); ?></div>
                            <div class="fb-actions">
                                <a href="view_feedback.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-secondary btn-sm">VIEW FULL FEEDBACK</a>
                                <button type="button" class="btn btn-danger btn-sm js-delete-btn" data-id="<?php echo (int) $row['id']; ?>">DELETE</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- ============ DELETE CONFIRMATION MODAL ============ -->
    <div class="modal-backdrop" id="delete-modal">
        <div class="modal-box">
            <p>Are you sure you want to delete this feedback?</p>
            <form method="POST" action="delete_feedback.php" id="delete-form">
                <input type="hidden" name="id" id="delete-id" value="">
                <?php echo csrf_field(); ?>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-delete">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Wire up the delete confirmation modal.
        const modal = document.getElementById('delete-modal');
        const deleteIdField = document.getElementById('delete-id');

        document.querySelectorAll('.js-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteIdField.value = btn.getAttribute('data-id');
                modal.classList.add('open');
            });
        });

        document.getElementById('cancel-delete').addEventListener('click', function () {
            modal.classList.remove('open');
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.classList.remove('open');
        });
    </script>
</body>
</html>