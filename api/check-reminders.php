<?php
header('Content-Type: application/json');

require_once '../Includes/config.php';
require_once '../Includes/reminder.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$overdue = getOverdueItems($conn);
$upcoming = getUpcomingItems($conn, 7);

echo json_encode([
    'overdue_count' => count($overdue),
    'upcoming_count' => count($upcoming),
    'overdue' => $overdue,
    'upcoming' => $upcoming
]);

$conn->close();
?>
