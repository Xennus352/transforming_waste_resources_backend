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

        // Get the post_id from the request body
        $input = json_decode(file_get_contents('php://input'), true);
        $postId = $input['post_id'];

        if (!$postId) {
            echo json_encode([
                'success' => false,
                'message' => 'Post ID is required.',
            ]);
            exit;
        }

        // Query to delete the saved post for the current user
        $sql = "DELETE FROM SavedPosts WHERE user_id = $userId AND post_id = $postId";
        if (mysqli_query($con, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Post successfully deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete the post.' . $postId]);
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
