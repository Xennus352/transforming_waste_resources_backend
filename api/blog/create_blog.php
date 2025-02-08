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
    $title = mysqli_real_escape_string($con, $data['title']);
    $description = mysqli_real_escape_string($con, $data['description']);
    $imgUrl = mysqli_real_escape_string($con, $data['imgUrl']);


    // Check if any required field is empty
    if (empty($title) || empty($description)) {
        echo json_encode([
            'status' => 400,
            'success' => false,
            'message' => 'Title and description fields are required.',
        ]);
        exit;
    }

    // Get the user_id from UserSessions table using session_token
    $getCurrentUser = "SELECT user_id FROM `UserSessions` WHERE session_token = '$sessionToken'";
    $result = mysqli_query($con, $getCurrentUser);

    if (mysqli_num_rows($result) > 0) {
        // Fetch the user_id
        $row = mysqli_fetch_assoc($result);
        $userId = $row['user_id'];

        // Insert wasteInformation into the WasteInfo table
        $query = "INSERT INTO `WasteInfo` (user_id, title,description,  waste_pic ) 
                  VALUES ('$userId', '$title','$description', '$waste_pic')";

        if (mysqli_query($con, $query)) {
            // Return success response
            echo json_encode([
                'status' => 201,
                'success' => true,
                'message' => 'Information created successfully.',
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
