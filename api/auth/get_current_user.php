<?php
// Allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

include('../lib/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
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
    $getCurrentUser  = "SELECT user_id FROM `UserSessions` WHERE session_token = '$sessionToken'";
    $result = mysqli_query($con, $getCurrentUser);

    if (mysqli_num_rows($result) > 0) {
        // Fetch the user_id
        $row = mysqli_fetch_assoc($result);
        $userId = $row['user_id'];

        // Now, retrieve user details from the Users table
        $getUser  = "SELECT * FROM `Users` WHERE id = '$userId'";
        $userResult = mysqli_query($con, $getUser);

        if (mysqli_num_rows($userResult) > 0) {
            $userData = mysqli_fetch_assoc($userResult);

            // Return success response with user data
            echo json_encode([
                'status' => 200,
                'success' => true,
                'message' => "Success",
                'data' => $userData, // Return the user data
            ]);
        } else {
            echo json_encode([
                'status' => 404,
                'success' => false,
                'message' => 'User  not found.',
            ]);
        }
    } else {
        echo json_encode([
            'status' => 401,
            'success' => false,
            'message' => 'Invalid session token.',
        ]);
    }
} else {
    echo json_encode([
        'status' => 405,
        'success' => false,
        'message' => 'Method not allowed.',
    ]);
}
