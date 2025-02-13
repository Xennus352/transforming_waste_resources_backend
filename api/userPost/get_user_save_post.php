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

        // Query to get saved posts for the current user
        $sql = "SELECT p.id, p.title, p.content, p.picture, p.created_at
            FROM SavedPosts sp
            JOIN Posts p ON sp.post_id = p.id
            WHERE sp.user_id =  $userId";
        $currentUserSavedPost = mysqli_query($con, $sql);

        if (mysqli_num_rows($currentUserSavedPost) > 0) {
            $data = array();

            while ($row = mysqli_fetch_assoc($currentUserSavedPost)) {
                $data[] = $row;
            }

            echo json_encode(['success' => true, 'message' => 'Successfully retrieved saved posts', 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database query failed']);
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
