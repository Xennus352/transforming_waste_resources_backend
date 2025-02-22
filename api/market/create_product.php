<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');
include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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

    // Decode the incoming JSON data from the request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Sanitize input data
    $product_name = mysqli_real_escape_string($con, $data['product_name']);
    $description = mysqli_real_escape_string($con, $data['description']);
    $price = mysqli_real_escape_string($con, $data['price']);
    $quantity = mysqli_real_escape_string($con, $data['quantity']);
    $image_url = mysqli_real_escape_string($con, $data['image_url']);
    $category = mysqli_real_escape_string($con, $data['category']);


    // Get the user_id from UserSessions table using session_token
    $getCurrentUser = "SELECT user_id FROM `UserSessions` WHERE session_token = '$sessionToken'";
    $result = mysqli_query($con, $getCurrentUser);

    if (mysqli_num_rows($result) > 0) {
        // Fetch the user_id
        $row = mysqli_fetch_assoc($result);
        $userId = $row['user_id'];

        // Insert products into the HandmadeProducts table
        $query = "INSERT INTO `HandmadeProducts` (user_id, product_name, description, price, quantity, image_url, category)
          VALUES ('$userId', '$product_name', '$description', '$price', '$quantity', '$image_url', '$category')";

        if (mysqli_query($con, $query)) {
            // Return success response
            echo json_encode([
                'status' => 201,
                'success' => true,
                'message' => 'Product created successfully.',
            ]);
        } else {
            // Return error response with detailed MySQL error
            echo json_encode([
                'status' => 500,
                'success' => false,
                'message' => 'Failed to insert information.',
                'error' => 'Query failed: ' . mysqli_error($con),
                'query' => $query
            ]);
        }
    } else {
        // Return error response if session token not found
        echo json_encode([
            'status' => 400,
            'success' => false,
            'message' => 'Invalid session token.',
        ]);
    }
}
