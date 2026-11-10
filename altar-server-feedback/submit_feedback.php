<?php
/**
 * submit_feedback.php
 * ---------------------------------------------------------------------
 * Receives the POST data from feedback.php (sent via JavaScript's
 * fetch()), validates EVERYTHING again on the server (never trust the
 * browser), and if everything looks good, saves it into the database
 * using a prepared statement.
 *
 * It always responds with JSON, e.g.:
 *   { "success": true }
 *   { "success": false, "message": "...", "errors": { "field": "..." } }
 * feedback.php's script.js reads this JSON to decide what to show.
 * ---------------------------------------------------------------------
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

// Only allow POST requests to this endpoint.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

/**
 * Small helper: trims a posted value and returns '' if it wasn't sent.
 */
function post_value($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

/**
 * Small helper: checks a value against an allowed list of options.
 * This stops someone from bypassing the JS and submitting a value
 * that was never one of the real choices (e.g. via browser dev tools).
 */
function is_allowed($value, array $allowed) {
    return in_array($value, $allowed, true);
}

// -----------------------------------------------------------------
// 1. Collect and lightly clean the incoming data
// -----------------------------------------------------------------
$name                  = post_value('name');
$parish_vicariate      = post_value('parish_vicariate');
$overall_rating_raw    = post_value('overall_rating');
$enjoyed_most          = post_value('enjoyed_most');
$favorite_activity     = post_value('favorite_activity');
$feeling               = post_value('feeling');
$met_other_servers     = post_value('met_other_servers');
$learned_realized      = post_value('learned_realized');
$appreciated_service   = post_value('appreciated_service');
$three_words           = post_value('three_words');
$improvement           = post_value('improvement');
$next_event            = post_value('next_event');
$message_to_organizers = post_value('message_to_organizers');
$join_again            = post_value('join_again');

// -----------------------------------------------------------------
// 2. Validate every field (mirrors what the frontend checks, but this
//    copy is the one that actually matters, since it runs on the server)
// -----------------------------------------------------------------
$errors = [];

// Optional field, but still enforce a sane max length.
if (mb_strlen($name) > 100) {
    $errors['name'] = 'Name is too long.';
}

if ($parish_vicariate === '') {
    $errors['parish_vicariate'] = 'Please tell us your parish or vicariate.';
} elseif (mb_strlen($parish_vicariate) > 150) {
    $errors['parish_vicariate'] = 'That entry is too long.';
}

$overall_rating = filter_var($overall_rating_raw, FILTER_VALIDATE_INT);
if ($overall_rating === false || $overall_rating < 1 || $overall_rating > 5) {
    $errors['overall_rating'] = 'Please choose a rating from 1 to 5.';
}

if ($enjoyed_most === '') {
    $errors['enjoyed_most'] = 'Please share what you enjoyed most.';
} elseif (mb_strlen($enjoyed_most) > 2000) {
    $errors['enjoyed_most'] = 'That response is too long.';
}

if ($favorite_activity === '') {
    $errors['favorite_activity'] = 'Please share your favorite part or activity.';
} elseif (mb_strlen($favorite_activity) > 1000) {
    $errors['favorite_activity'] = 'That response is too long.';
}

$allowed_feelings = ['Very happy', 'Happy', 'It was okay', 'Not very comfortable', 'Not satisfied'];
if (!is_allowed($feeling, $allowed_feelings)) {
    $errors['feeling'] = 'Please choose how you felt during the event.';
}

$allowed_met = ['Yes, and I really enjoyed it!', 'Yes, a little', 'Not really'];
if (!is_allowed($met_other_servers, $allowed_met)) {
    $errors['met_other_servers'] = 'Please answer this question.';
}

if ($learned_realized === '') {
    $errors['learned_realized'] = 'Please share something you learned or realized.';
} elseif (mb_strlen($learned_realized) > 2000) {
    $errors['learned_realized'] = 'That response is too long.';
}

$allowed_appreciated = ["Yes, definitely!", 'Yes, a little', "I'm not sure", 'Not really'];
if (!is_allowed($appreciated_service, $allowed_appreciated)) {
    $errors['appreciated_service'] = 'Please answer this question.';
}

if ($three_words === '') {
    $errors['three_words'] = 'Please describe your experience in three words.';
} elseif (mb_strlen($three_words) > 150) {
    $errors['three_words'] = 'That entry is too long.';
}

if ($improvement === '') {
    $errors['improvement'] = 'Please share a suggestion for improvement.';
} elseif (mb_strlen($improvement) > 2000) {
    $errors['improvement'] = 'That response is too long.';
}

if (mb_strlen($next_event) > 2000) {
    $errors['next_event'] = 'That response is too long.';
}

if (mb_strlen($message_to_organizers) > 2000) {
    $errors['message_to_organizers'] = 'That response is too long.';
}

$allowed_join_again = ['YES!', 'Definitely! See you next time!', 'Maybe', "I'm not sure"];
if (!is_allowed($join_again, $allowed_join_again)) {
    $errors['join_again'] = 'Please answer this question.';
}

// -----------------------------------------------------------------
// 3. If anything failed validation, stop here and report back
// -----------------------------------------------------------------
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please complete all required fields correctly.',
        'errors'  => $errors
    ]);
    exit;
}

// -----------------------------------------------------------------
// 4. Simple duplicate-submission protection (no login required).
//    We remember the timestamp of the visitor's last submission in
//    their PHP session and politely block an immediate repeat.
//    This is NOT meant to block legitimate multiple submissions from
//    the same device later on -- only accidental instant double-taps.
// -----------------------------------------------------------------
$cooldownSeconds = 10;
if (isset($_SESSION['last_submission_time'])) {
    $secondsSinceLast = time() - $_SESSION['last_submission_time'];
    if ($secondsSinceLast < $cooldownSeconds) {
        echo json_encode([
            'success' => false,
            'message' => 'It looks like your feedback was already submitted. Thank you!'
        ]);
        exit;
    }
}

// -----------------------------------------------------------------
// 5. Save to the database using a prepared statement.
//    Prepared statements keep user input completely separate from
//    the SQL command, so it's impossible for submitted text to be
//    interpreted as SQL code (this is what prevents SQL injection).
// -----------------------------------------------------------------
try {
    $sql = "INSERT INTO feedback (
                name, parish_vicariate, overall_rating, enjoyed_most,
                favorite_activity, feeling, met_other_servers, learned_realized,
                appreciated_service, three_words, improvement, next_event,
                message_to_organizers, join_again
            ) VALUES (
                :name, :parish_vicariate, :overall_rating, :enjoyed_most,
                :favorite_activity, :feeling, :met_other_servers, :learned_realized,
                :appreciated_service, :three_words, :improvement, :next_event,
                :message_to_organizers, :join_again
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'                  => $name !== '' ? $name : null,
        ':parish_vicariate'      => $parish_vicariate,
        ':overall_rating'        => $overall_rating,
        ':enjoyed_most'          => $enjoyed_most,
        ':favorite_activity'     => $favorite_activity,
        ':feeling'               => $feeling,
        ':met_other_servers'     => $met_other_servers,
        ':learned_realized'      => $learned_realized,
        ':appreciated_service'   => $appreciated_service,
        ':three_words'           => $three_words,
        ':improvement'           => $improvement,
        ':next_event'            => $next_event !== '' ? $next_event : null,
        ':message_to_organizers' => $message_to_organizers !== '' ? $message_to_organizers : null,
        ':join_again'            => $join_again,
    ]);

    // Remember this submission time for the duplicate-protection check above.
    $_SESSION['last_submission_time'] = time();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    if (APP_DEBUG) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Something went wrong while submitting your feedback. Please try again.']);
    }
}
