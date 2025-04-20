<?php
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
$id_achievement = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_achievement = intval($_GET['id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['id_achievement']) && is_numeric($data['id_achievement'])) {
        $id_achievement = intval($data['id_achievement']);
    }
}
if ($id_achievement === null || $id_achievement <= 0) {
    send_json_response(400, ['error' => 'Achievement ID is required and must be a positive integer']);
}
$conn = get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$conn->begin_transaction();
try {
    $stmt_ua = $conn->prepare("DELETE FROM UserAchievement WHERE id_achievement = ?");
    if (!$stmt_ua) throw new Exception('Prep del UserAchievement failed: ' . $conn->error);
    $stmt_ua->bind_param('i', $id_achievement);
    if (!$stmt_ua->execute()) throw new Exception('Exec del UserAchievement failed: ' . $stmt_ua->error);
    $stmt_ua->close();
    $stmt_ach = $conn->prepare("DELETE FROM Achievement WHERE id_achievement = ?");
    if (!$stmt_ach) throw new Exception('Prep del Achievement failed: ' . $conn->error);
    $stmt_ach->bind_param('i', $id_achievement);
    if (!$stmt_ach->execute()) throw new Exception('Exec del Achievement failed: ' . $stmt_ach->error);
    $affected_rows = $stmt_ach->affected_rows;
    $stmt_ach->close();
    if ($affected_rows > 0) {
        $conn->commit();
        send_json_response(200, ['message' => 'Achievement deleted successfully', 'id_achievement' => $id_achievement]);
    } else {
        $conn->rollback();
        send_json_response(404, ['error' => 'Achievement not found']);
    }
} catch (Exception $e) {
    $conn->rollback();
    send_json_response(500, ['error' => 'Failed to delete achievement: ' . $e->getMessage()]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 