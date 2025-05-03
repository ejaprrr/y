<?php
/**
 * Functions for handling notifications
 */

/**
 * Create a notification
 * 
 * @param mysqli $conn Database connection
 * @param string $type Notification type (like, reply, repost, follow, mention)
 * @param string $to_user Username of notification recipient
 * @param string $from_user Username of user who triggered notification
 * @param int|null $post_id Related post ID (null for follows)
 * @return bool Success status
 */
function create_notification($conn, $type, $to_user, $from_user, $post_id = null) {
    // Don't notify yourself
    if ($to_user === $from_user) {
        return false;
    }
    
    // Check if a similar notification exists in the last hour (prevent spam)
    $stmt = $conn->prepare("
        SELECT id FROM notifications 
        WHERE user_name = ? AND type = ? AND from_user_name = ? 
        " . ($post_id ? "AND post_id = ?" : "AND post_id IS NULL") . "
        AND created_at > NOW() - INTERVAL 1 HOUR
        LIMIT 1
    ");
    
    if ($post_id) {
        $stmt->bind_param("sssi", $to_user, $type, $from_user, $post_id);
    } else {
        $stmt->bind_param("sss", $to_user, $type, $from_user);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If a recent notification exists, don't create another
    if ($result->num_rows > 0) {
        return false;
    }
    
    // Create the notification
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_name, type, from_user_name, post_id, is_read) 
        VALUES (?, ?, ?, ?, 0)
    ");
    
    if ($post_id) {
        $stmt->bind_param("sssi", $to_user, $type, $from_user, $post_id);
    } else {
        $null_value = null;
        $stmt->bind_param("sssi", $to_user, $type, $from_user, $null_value);
    }
    
    return $stmt->execute();
}

/**
 * Get notifications for a user
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param string $type Type filter ('all' or specific type)
 * @param int $limit Maximum number to return
 * @return array Notifications
 */
function get_user_notifications($conn, $user_name, $type = 'all', $limit = 50) {
    $condition = "n.created_at > NOW() - INTERVAL 7 DAY";
    
    if ($type !== 'all') {
        $condition .= " AND n.type = ?";
    }
    
    $sql = "
        SELECT n.*, 
            u.display_name as from_display_name, 
            u.profile_picture_url as from_profile_pic,
            p.text_content as post_content,
            p.author_user_name as post_author
        FROM notifications n
        JOIN users u ON n.from_user_name = u.user_name
        LEFT JOIN posts p ON n.post_id = p.id
        WHERE n.user_name = ? AND $condition
        ORDER BY n.created_at DESC
        LIMIT ?
    ";
    
    if ($type !== 'all') {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $user_name, $type, $limit);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $user_name, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

/**
 * Group similar notifications to prevent visual spam
 * 
 * @param array $notifications Raw notifications
 * @return array Grouped notifications with day separators
 */
function group_notifications($notifications) {
    if (empty($notifications)) {
        return [];
    }
    
    $grouped = [];
    $current_date = null;
    $groups = [];
    
    foreach ($notifications as $notification) {
        // Extract the date part for day separators
        $notif_date = date('Y-m-d', strtotime($notification['created_at']));
        
        // If this is a new day, add a day separator
        if ($current_date !== $notif_date) {
            $current_date = $notif_date;
            $grouped[] = [
                'type' => 'day_separator',
                'date' => $notification['created_at'],
                'created_at' => $notification['created_at']
            ];
        }
        
        $type = $notification['type'];
        $post_id = $notification['post_id'];
        
        // Create a key for grouping similar notifications
        $key = "{$type}_{$post_id}";
        
        // Handle follow notifications separately (don't group)
        if ($type === 'follow') {
            $grouped[] = [
                'id' => $notification['id'],
                'type' => $type,
                'from_users' => [[
                    'user_name' => $notification['from_user_name'],
                    'display_name' => $notification['from_display_name'],
                    'profile_pic' => $notification['from_profile_pic']
                ]],
                'count' => 1,
                'is_read' => (bool)$notification['is_read'],
                'created_at' => $notification['created_at']
            ];
            continue;
        }
        
        // Group similar notifications (same type and post)
        if (isset($groups[$key])) {
            // Check if this user is already in the group
            $user_exists = false;
            foreach ($groups[$key]['from_users'] as $user) {
                if ($user['user_name'] === $notification['from_user_name']) {
                    $user_exists = true;
                    break;
                }
            }
            
            if (!$user_exists) {
                // Always increment count for new unique users
                $groups[$key]['count']++;
                
                // Only add to from_users array if we have less than 3 users
                if (count($groups[$key]['from_users']) < 3) {
                    $groups[$key]['from_users'][] = [
                        'user_name' => $notification['from_user_name'],
                        'display_name' => $notification['from_display_name'],
                        'profile_pic' => $notification['from_profile_pic']
                    ];
                    
                    // If this is the latest user to interact, move them to the front
                    if (strtotime($notification['created_at']) > strtotime($groups[$key]['created_at'])) {
                        $latestUser = array_pop($groups[$key]['from_users']);
                        array_unshift($groups[$key]['from_users'], $latestUser);
                        $groups[$key]['created_at'] = $notification['created_at'];
                    }
                }
            }
            
            // Update the is_read status if any notification in the group is unread
            if (!$notification['is_read']) {
                $groups[$key]['is_read'] = false;
            }
        } else {
            // This is a new group
            $groups[$key] = [
                'id' => $notification['id'],
                'type' => $type,
                'post_id' => $post_id,
                'from_users' => [[
                    'user_name' => $notification['from_user_name'],
                    'display_name' => $notification['from_display_name'],
                    'profile_pic' => $notification['from_profile_pic']
                ]],
                'post_content' => $notification['post_content'],
                'post_author' => $notification['post_author'],
                'count' => 1,
                'is_read' => (bool)$notification['is_read'],
                'created_at' => $notification['created_at']
            ];
        }
    }
    
    // Add grouped notifications to the result
    foreach ($groups as $group) {
        $grouped[] = $group;
    }
    
    // Make sure every element has created_at before sorting
    foreach ($grouped as &$item) {
        if (!isset($item['created_at']) && isset($item['date'])) {
            $item['created_at'] = $item['date'];
        }
    }
    
    // Sort by date (day separators and notifications)
    usort($grouped, function($a, $b) {
        // Make sure both elements have created_at before comparing
        if (!isset($a['created_at'])) return 1;
        if (!isset($b['created_at'])) return -1;
        
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $grouped;
}

/**
 * Format notification text based on type and users
 * 
 * @param array $notification Grouped notification
 * @return string Formatted text
 */
function format_notification_text($notification) {
    if ($notification['type'] === 'day_separator') {
        // Use either 'date' or 'created_at' field, whichever is available
        $date_value = isset($notification['date']) ? $notification['date'] : 
                     (isset($notification['created_at']) ? $notification['created_at'] : null);
        
        if ($date_value) {
            $date = date('F j, Y', strtotime($date_value));
            return $date;
        } else {
            return "Unknown date";
        }
    }
    
    $type = $notification['type'];
    $from_users = $notification['from_users'];
    $count = $notification['count'];
    
    // Format user names
    $names = [];
    foreach ($from_users as $user) {
        $names[] = !empty($user['display_name']) ? $user['display_name'] : $user['user_name'];
    }
    
    // Single user
    if ($count === 1) {
        $name = "<strong>".htmlspecialchars($names[0])."</strong>";
        
        switch ($type) {
            case 'like':
                return "$name liked your post";
            case 'repost':
                return "$name reposted your post";
            case 'reply':
                return "$name replied to your post";
            case 'follow':
                return "$name started following you";
            case 'mention':
                return "$name mentioned you in a post";
            default:
                return "$name interacted with your content";
        }
    }
    
    // Multiple users
    if (count($names) === 2) {
        $users = "<strong>".htmlspecialchars($names[0])."</strong> and <strong>".htmlspecialchars($names[1])."</strong>";
    } else {
        $users = "<strong>".htmlspecialchars($names[0])."</strong>";
        if ($count > 2) {
            $others = $count - 1;
            $users .= " and <strong>$others others</strong>";
        } else {
            $users .= " and others";
        }
    }
    
    switch ($type) {
        case 'like':
            return "$users liked your post";
        case 'repost':
            return "$users reposted your post";
        case 'reply':
            return "$users replied to your post";
        case 'mention':
            return "$users mentioned you in posts";
        default:
            return "$users interacted with your content";
    }
}

/**
 * Get the URL for a notification
 * 
 * @param array $notification Notification data
 * @return string URL to the related content
 */
function get_notification_url($notification) {
    if ($notification['type'] === 'day_separator') {
        return '#';
    }
    
    $type = $notification['type'];
    
    if ($type === 'follow') {
        return "/y/public/app/profile.php?username=" . urlencode($notification['from_users'][0]['user_name']);
    } else if (!empty($notification['post_id'])) {
        return "/y/public/app/post.php?id=" . urlencode($notification['post_id']);
    }
    
    return "/y/public/app/notifications.php";
}

/**
 * Mark notifications as read
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @param string $type Type filter ('all' or specific type)
 * @return bool Success status
 */
function mark_notifications_as_read($conn, $user_name, $type = 'all') {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_name = ? AND is_read = 0";
    
    if ($type !== 'all') {
        $sql .= " AND type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user_name, $type);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_name);
    }
    
    return $stmt->execute();
}

/**
 * Count unread notifications
 * 
 * @param mysqli $conn Database connection
 * @param string $user_name Username
 * @return int Number of unread notifications
 */
function count_unread_notifications($conn, $user_name) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_name = ? AND is_read = 0");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['count'];
    }
    
    return 0;
}

/**
 * Delete notifications older than 7 days
 * 
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function cleanup_old_notifications($conn) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 7 DAY");
    return $stmt->execute();
}

/**
 * Process @mentions in content and create notifications
 *
 * @param mysqli $conn Database connection
 * @param string $content Post content
 * @param string $author_name Post author username
 * @param int $post_id Post ID
 */
function process_mentions($conn, $content, $author_name, $post_id) {
    preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);
    
    if (empty($matches[1])) {
        return;
    }
    
    $mentioned_users = array_unique($matches[1]);
    
    foreach ($mentioned_users as $username) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT 1 FROM users WHERE user_name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            create_notification($conn, 'mention', $username, $author_name, $post_id);
        }
    }
}
?>