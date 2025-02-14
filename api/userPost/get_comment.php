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
        // $userId = '6';

        // Query to get comment posts 
        $sql = "SELECT 
                        u.id AS user_id, 
                        u.username AS user_name, 
                        u.picture AS user_pic,
                        p.id AS post_id, 
                        p.title AS post_title, 
                        p.content AS post_content, 
                        c.id AS comment_id, 
                        c.comment 
                    FROM 
                        Users u
                    JOIN 
                        Posts p ON u.id = p.user_id
                    LEFT JOIN 
                        Comments c ON p.id = c.post_id";

        $commentPost = mysqli_query($con, $sql);

        if (mysqli_num_rows($commentPost) > 0) {
            $data = array();

            while ($row = mysqli_fetch_assoc($commentPost)) {
                $data[] = $row;
            }

            echo json_encode(['success' => true, 'message' => 'Successfully retrieved comments', 'data' => $data]);
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
