<?php
/**
 * admin/view_feedback.php
 * Shows one complete feedback response, with every question and answer.
 */
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$activePage = 'feedback';

$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;

if ($id === false || $id === null) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM feedback WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$fb = $stmt->fetch();

if (!$fb) {
    header('Location: dashboard.php');
    exit;
}

/**
 * htmlspecialchars() shortcut used everywhere below so that any text
 * the participant typed is displayed safely and cannot run as HTML
 * or JavaScript in the admin's browser (this is what prevents XSS).
 */
function e($value) {
    return htmlspecialchars($value === null ? '' : $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback #<?php echo (int) $fb['id']; ?> - Altar Server Month 2026</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

    <?php include __DIR__ . '/includes/admin_header.php'; ?>

    <div class="admin-wrap">
        <a href="dashboard.php#feedback-table" class="btn btn-secondary btn-sm" style="margin-bottom:16px; display:inline-block;">&larr; Back to Dashboard</a>

        <div class="panel">
            <h2>📄 Feedback #<?php echo (int) $fb['id']; ?></h2>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:-8px;">
                Submitted <?php echo e(date('F j, Y \a\t g:i A', strtotime($fb['submitted_at']))); ?>
            </p>

            <div class="detail-section">
                <h3>Participant Information</h3>
                <div class="detail-row">
                    <div class="q">Name</div>
                    <div class="a"><?php echo $fb['name'] ? e($fb['name']) : 'Anonymous'; ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">Parish / Vicariate</div>
                    <div class="a"><?php echo e($fb['parish_vicariate']); ?></div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Experience</h3>
                <div class="detail-row">
                    <div class="q">Overall Rating</div>
                    <div class="a"><?php echo str_repeat('⭐', (int) $fb['overall_rating']); ?> (<?php echo (int) $fb['overall_rating']; ?>/5)</div>
                </div>
                <div class="detail-row">
                    <div class="q">What did you enjoy the most during the event?</div>
                    <div class="a"><?php echo e($fb['enjoyed_most']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">Which part or activity was your favorite?</div>
                    <div class="a"><?php echo e($fb['favorite_activity']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">How did you feel while participating in the event?</div>
                    <div class="a"><?php echo e($fb['feeling']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">Did you get the chance to meet and interact with other altar servers?</div>
                    <div class="a"><?php echo e($fb['met_other_servers']); ?></div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Faith and Service</h3>
                <div class="detail-row">
                    <div class="q">What is one thing you learned or realized from this event?</div>
                    <div class="a"><?php echo e($fb['learned_realized']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">Did this event help you appreciate your service as an altar server more?</div>
                    <div class="a"><?php echo e($fb['appreciated_service']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">In three words, how would you describe your Altar Server Month experience?</div>
                    <div class="a"><?php echo e($fb['three_words']); ?></div>
                </div>
            </div>

            <div class="detail-section">
                <h3>Suggestions and Improvement</h3>
                <div class="detail-row">
                    <div class="q">What can we improve for our next Altar Server activity?</div>
                    <div class="a"><?php echo e($fb['improvement']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">What would you like to see in our next Altar Server event?</div>
                    <div class="a"><?php echo $fb['next_event'] ? e($fb['next_event']) : '<span style="color:var(--text-muted);">No response</span>'; ?></div>
                </div>
                <div class="detail-row">
                    <div class="q">Message, suggestion, or appreciation for the organizers</div>
                    <div class="a"><?php echo $fb['message_to_organizers'] ? e($fb['message_to_organizers']) : '<span style="color:var(--text-muted);">No response</span>'; ?></div>
                </div>
            </div>

            <div class="detail-section" style="margin-bottom:0;">
                <h3>Final Question</h3>
                <div class="detail-row" style="margin-bottom:0;">
                    <div class="q">Would you join another Altar Server event like this?</div>
                    <div class="a"><?php echo e($fb['join_again']); ?></div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-danger js-delete-btn" data-id="<?php echo (int) $fb['id']; ?>">DELETE THIS FEEDBACK</button>
    </div>

    <!-- ============ DELETE CONFIRMATION MODAL ============ -->
    <div class="modal-backdrop" id="delete-modal">
        <div class="modal-box">
            <p>Are you sure you want to delete this feedback?</p>
            <form method="POST" action="delete_feedback.php" id="delete-form">
                <input type="hidden" name="id" id="delete-id" value="">
                <input type="hidden" name="redirect" value="dashboard">
                <?php echo csrf_field(); ?>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-delete">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
