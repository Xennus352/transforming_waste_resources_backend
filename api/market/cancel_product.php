<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // Retrieve session token from the request headers
    $headers = apache_request_headers();
    $sessionToken = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$sessionToken) {
        echo json_encode([
            'status' => 400,
            'success' => false,
            'message' => 'Session token is required.',
        ]);
        exit;
    }

    // Get the user_id from UserSessions table using session_token
    $getCurrentUser   = "SELECT user_id FROM `UserSessions` WHERE session_token = '$sessionToken'";
    $result = mysqli_query($con, $getCurrentUser);

    if (mysqli_num_rows($result) > 0) {
        // Fetch the user_id
        $row = mysqli_fetch_assoc($result);
        $userId = $row['user_id'];

        // Get the product_id from the request body
        $input = json_decode(file_get_contents('php://input'), true);
        $product_id = $input['product_id'];

        if (!$product_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Product ID is required.',
            ]);
            exit;
        }

        // Query to delete the saved post for the current user
        $sql = "DELETE FROM Orders WHERE user_id = $userId AND product_id = $product_id";
        if (mysqli_query($con, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Order canceled.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel.' . $product_id]);
        }
    } else {
        // Return error response if session token not found
        echo json_encode([
            'success' => false,
            'message' => 'Invalid session token.',
        ]);
    }
} else {
    // Return error response for unsupported request methods
    echo json_encode([
        'success' => false,
        'message' => 'Unsupported request method.',
    ]);
}
