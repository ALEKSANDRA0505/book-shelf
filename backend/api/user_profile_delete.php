<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/api_helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';
setup_cors();
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['error' => 'Method Not Allowed. Expected DELETE or POST']);
}
$admin_user_id = null;
$is_admin = false;
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header || !preg_match('/^Bearer\s+(.*?)$/', $auth_header, $matches)) {
        throw new Exception('Authorization header missing or invalid', 401);
    }
    $jwt = $matches[1];
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
    
    if (!isset($decoded->data) || !isset($decoded->data->id_user)) {
        throw new Exception('Invalid token payload structure', 401);
    }
    $admin_user_id = $decoded->data->id_user;
    $conn_check = get_db_connection();
    if (!$conn_check) {
        throw new Exception('Database connection failed for admin check', 500);
    }
    $stmt_check_admin = $conn_check->prepare("SELECT status FROM UserProfile WHERE id_user = ?");
    if (!$stmt_check_admin) throw new Exception('Failed to prepare admin check', 500);
    $stmt_check_admin->bind_param("i", $admin_user_id);
    $stmt_check_admin->execute();
    $result_admin = $stmt_check_admin->get_result();
    if ($admin_data = $result_admin->fetch_assoc()) {
        if ($admin_data['status'] === 'Админ') {
            $is_admin = true;
        }
    }
    $stmt_check_admin->close();
} catch (Exception $e) {
    $code = $e->getCode() ?: 401;
    send_json_response($code, ['error' => 'Authentication failed', 'details' => $e->getMessage()]);
}
if (!$is_admin) {
    send_json_response(403, ['error' => 'Forbidden: Administrator access required.']);
}
$id_user_to_delete = null;
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_user_to_delete = intval($_GET['id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    if (isset($data['id_user']) && is_numeric($data['id_user'])) {
        $id_user_to_delete = intval($data['id_user']);
    }
}
if ($id_user_to_delete === null || $id_user_to_delete <= 0) {
    send_json_response(400, ['error' => 'User ID to delete is required and must be a positive integer']);
}
if ($id_user_to_delete === $admin_user_id) {
     send_json_response(403, ['error' => 'Forbidden: Administrators cannot delete their own account.']);
}
$conn = $conn_check ?? get_db_connection();
if (!$conn) {
    send_json_response(500, ['error' => 'Database connection failed']);
}
$conn->begin_transaction();
try {
    $stmt_sub_to = $conn->prepare("DELETE FROM `UserSubscription` WHERE `id_following_user` = ?");
    if (!$stmt_sub_to) throw new Exception("Prep del UserSubscription (following) failed: " . $conn->error);
    $stmt_sub_to->bind_param('i', $id_user_to_delete);
    if (!$stmt_sub_to->execute()) throw new Exception("Exec del UserSubscription (following) failed: " . $stmt_sub_to->error);
    $stmt_sub_to->close();
    $stmt_sub_from = $conn->prepare("DELETE FROM `UserSubscription` WHERE `id_follower_user` = ?");
    if (!$stmt_sub_from) throw new Exception("Prep del UserSubscription (follower) failed: " . $conn->error);
    $stmt_sub_from->bind_param('i', $id_user_to_delete);
    if (!$stmt_sub_from->execute()) throw new Exception("Exec del UserSubscription (follower) failed: " . $stmt_sub_from->error);
    $stmt_sub_from->close();
    $other_related_tables = [
        'Review', 
        'WishlistItem', 
        'UserAchievement', 
        'ChatMessage' 
    ];
    $other_foreign_keys = [
        'Review' => 'id_user',
        'WishlistItem' => 'id_user',
        'UserAchievement' => 'id_user',
        'ChatMessage' => 'id_sender'
    ];
    foreach ($other_related_tables as $table) {
        $fk_column = $other_foreign_keys[$table] ?? null;
        if (!$fk_column) {
           throw new Exception("Configuration error for related table deletion: table={$table}");
        }
         
        $sql_delete_related = "DELETE FROM `{$table}` WHERE `{$fk_column}` = ?";
        $stmt_related = $conn->prepare($sql_delete_related);
        if (!$stmt_related) throw new Exception("Prep del {$table} failed: " . $conn->error);
        $stmt_related->bind_param('i', $id_user_to_delete);
        if (!$stmt_related->execute()) throw new Exception("Exec del {$table} failed: " . $stmt_related->error);
        $stmt_related->close();
    }
    $stmt_user = $conn->prepare("DELETE FROM UserProfile WHERE id_user = ?");
    if (!$stmt_user) throw new Exception('Prep del UserProfile failed: ' . $conn->error);
    $stmt_user->bind_param('i', $id_user_to_delete);
    if (!$stmt_user->execute()) throw new Exception('Exec del UserProfile failed: ' . $stmt_user->error);
    $affected_rows = $stmt_user->affected_rows;
    $stmt_user->close();
    if ($affected_rows > 0) {
        $conn->commit();
        send_json_response(200, ['message' => 'User profile and related data deleted successfully', 'id_user' => $id_user_to_delete]);
    } else {
        $conn->rollback();
        send_json_response(404, ['error' => 'User not found']);
    }
} catch (Exception $e) {
    $conn->rollback();
    send_json_response(500, ['error' => 'Failed to delete user profile: ' . $e->getMessage()]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?> 