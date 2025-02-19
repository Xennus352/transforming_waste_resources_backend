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

        // Query to get like posts for the current user
        $sql = "SELECT 
                    P.id AS post_id,
                    P.title AS post_title,
                    P.content AS post_content,
                    P.picture AS post_picture,
                    P.contentInBurmese As post_contentInBurmese, 
                    COALESCE(total_likes.total_like_count, 0) AS total_like_count,
                    GROUP_CONCAT(DISTINCT L.user_id) AS liked_by_users
                FROM 
                    Posts P
                LEFT JOIN 
                    (SELECT post_id, COUNT(user_id) AS total_like_count
                    FROM Likes
                    GROUP BY post_id) total_likes
                ON 
                    P.id = total_likes.post_id
                LEFT JOIN 
                    Likes L ON P.id = L.post_id
                GROUP BY 
                    P.id, P.title, P.content
                ORDER BY 
                    total_like_count DESC";

        $likedCount = mysqli_query($con, $sql);

        if (mysqli_num_rows($likedCount) > 0) {
            $data = array();

            while ($row = mysqli_fetch_assoc($likedCount)) {
                $data[] = $row;
            }

            echo json_encode(['success' => true, 'message' => 'Successfully retrieved likes', 'data' => $data]);
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
