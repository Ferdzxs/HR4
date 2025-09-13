<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'toggle') {
        $currentState = $_SESSION['sidebar_collapsed'] ?? false;
        $_SESSION['sidebar_collapsed'] = !$currentState;

        echo json_encode([
            'ok' => true,
            'collapsed' => $_SESSION['sidebar_collapsed']
        ]);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Sidebar API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>