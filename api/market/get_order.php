<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // SQL query to join Orders with Users
    $sql = "
        SELECT 
            Orders.id AS order_id,
            Orders.user_id,
            Users.username AS username,
            Users.email AS email,
            Orders.product_id,
            Orders.quantity,
            Orders.total_price,
            Orders.order_date
        FROM 
            Orders
        JOIN 
            Users ON Orders.user_id = Users.id
    ";

    $result = mysqli_query($con, $sql);

    if ($result) {
        $data = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        echo json_encode(['success' => true, 'message' => 'Successfully retrieved orders', 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database query failed']);
    }
} else {
    // Return error response with detailed MySQL error
    echo json_encode([
        'success' => false,
        'error' => 'Query failed: ' . mysqli_error($con),
        'query' => $sql
    ]);
}
