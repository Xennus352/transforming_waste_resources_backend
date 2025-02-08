<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $sql = "SELECT * FROM RecyclePost";
    $result = mysqli_query($con, $sql);

    if ($result) {
        $data = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        echo json_encode($data);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database query failed']);
    }
} else {
    // Return error response with detailed MySQL error
    echo json_encode([
        'success' => false,
        'error' => 'Query failed: ' . mysqli_error($con),
        'query' => $sql
    ]);
}
