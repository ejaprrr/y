<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../resources/connection.php';
require_once __DIR__ . '/../../resources/functions.php';
start_session();

$user = get_user_from_session($conn);

// Determine active tab (all, mentions, likes, reposts, follows)
$active_tab = $_GET['tab'] ?? 'all';
$valid_tabs = ['all', 'mentions', 'likes', 'reposts', 'follows', 'replies'];
if (!in_array($active_tab, $valid_tabs)) {
    $active_tab = 'all';
}

// Get notifications based on tab
$type = $active_tab === 'all' ? 'all' : ($active_tab === 'likes' ? 'like' : 
       ($active_tab === 'reposts' ? 'repost' : 
       ($active_tab === 'follows' ? 'follow' : 
       ($active_tab === 'mentions' ? 'mention' : 
       ($active_tab === 'replies' ? 'reply' : 'all')))));

$notifications = get_user_notifications($conn, $user['user_name'], $type);
$grouped_notifications = group_notifications($notifications);

// Mark notifications as read
mark_notifications_as_read($conn, $user['user_name'], $type);

// Clean up old notifications
cleanup_old_notifications($conn);

// Set up page variables
$page_title = 'Y | Notifications';
$page_header = 'Notifications'; // Add this to enable header

// Create tabs configuration
$tabs = [
    [
        'id' => 'all',
        'label' => 'All',
        'url' => "?tab=all",
        'icon' => 'bell'
    ],
    [
        'id' => 'mentions',
        'label' => 'Mentions',
        'url' => "?tab=mentions",
        'icon' => 'at'
    ],
    [
        'id' => 'likes',
        'label' => 'Likes',
        'url' => "?tab=likes",
        'icon' => 'heart'
    ],
    [
        'id' => 'replies',
        'label' => 'Replies',
        'url' => "?tab=replies",
        'icon' => 'chat'
    ],
    [
        'id' => 'reposts',
        'label' => 'Reposts',
        'url' => "?tab=reposts",
        'icon' => 'repeat'
    ],
    [
        'id' => 'follows',
        'label' => 'Follows',
        'url' => "?tab=follows",
        'icon' => 'person-plus'
    ]
];

// Capture content in a buffer
ob_start();
?>

<div class="notifications-container p-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-bell text-primary fs-3"></i>
                </div>
                <div>
                    <h2 class="fs-5 fw-bold mb-1">Stay updated</h2>
                    <p class="text-muted mb-0 small">Activities related to your content</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabs navigation -->
    <?php include __DIR__ . '/../../resources/components/tabs_navigation.php'; ?>
    
    <!-- Notifications list -->
    <div class="notifications-list mt-3">
        <?php if (empty($grouped_notifications)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5">
                <div class="text-center py-5">
                    <div class="mb-3"><i class="bi bi-bell text-muted" style="font-size: 3rem;"></i></div>
                    <h4>No notifications</h4>
                    <p class="text-muted">
                        <?php if ($active_tab === 'all'): ?>
                            You don't have any notifications yet.
                        <?php else: ?>
                            You don't have any <?php echo $active_tab; ?> notifications.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Show grouped notifications -->
            <?php foreach ($grouped_notifications as $notification): ?>
                <?php if ($notification['type'] === 'day_separator'): ?>
                    <!-- Day separator -->
                    <div class="day-separator my-3">
                        <div class="d-flex align-items-center">
                            <span class="text-muted small"><?php echo format_notification_text($notification); ?></span>
                            <hr class="flex-grow-1 ms-2">
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Normal notification -->
                    <div class="card border-0 shadow-sm rounded-4 mb-3 <?php echo !$notification['is_read'] ? 'notification-unread' : ''; ?>">
                        <div class="card-body p-3">
                            <div class="d-flex">
                                <!-- Left side with icon and avatars - IMPROVED -->
                                <div class="notification-visual me-3">
                                    <!-- Type icon (more prominent now) -->
                                    <?php 
                                    $icon_class = '';
                                    $icon = '';
                                    
                                    switch($notification['type']) {
                                        case 'like':
                                            $icon_class = 'bg-danger bg-opacity-10 text-danger';
                                            $icon = 'heart-fill';
                                            break;
                                        case 'repost':
                                            $icon_class = 'bg-success bg-opacity-10 text-success';
                                            $icon = 'repeat';
                                            break;
                                        case 'reply':
                                            $icon_class = 'bg-info bg-opacity-10 text-info';
                                            $icon = 'chat-fill';
                                            break;
                                        case 'follow':
                                            $icon_class = 'bg-primary bg-opacity-10 text-primary';
                                            $icon = 'person-plus-fill';
                                            break;
                                        case 'mention':
                                            $icon_class = 'bg-warning bg-opacity-10 text-warning';
                                            $icon = 'at';
                                            break;
                                    }
                                    ?>
                                    
                                    <div class="notification-icon-badge <?php echo $icon_class; ?>">
                                        <i class="bi bi-<?php echo $icon; ?>"></i>
                                    </div>
                                    
                                    <!-- User avatars - now in an overlapping circle -->
                                    <div class="avatar-group">
                                        <?php 
                                        // Display up to 3 avatars
                                        $avatar_count = min(3, count($notification['from_users']));
                                        for($i = 0; $i < $avatar_count; $i++): 
                                            $user = $notification['from_users'][$i];
                                        ?>
                                            <div class="avatar-item" style="z-index: <?php echo 10 - $i; ?>;">
                                                <?php if (!empty($user['profile_pic'])): ?>
                                                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                                                         alt="<?php echo htmlspecialchars($user['display_name']); ?>"
                                                         class="rounded-circle border border-2 border-white">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder rounded-circle border border-2 border-white">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endfor; ?>
                                        
                                        <!-- Count badge if more than 3 users -->
                                        <?php if ($notification['count'] > 3): ?>
                                            <div class="avatar-more">
                                                <span>+<?php echo $notification['count'] - 3; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Notification content (right side) -->
                                <div class="notification-content flex-grow-1">
                                    <!-- Notification text -->
                                    <div class="mb-1">
                                        <?php echo format_notification_text($notification); ?>
                                    </div>
                                    
                                    <!-- Post preview if applicable -->
                                    <?php if (in_array($notification['type'], ['like', 'repost', 'reply', 'mention']) && !empty($notification['post_content'])): ?>
                                        <div class="post-preview mt-2 mb-2 p-2 border-start ps-3 text-muted small">
                                            "<?php echo htmlspecialchars(substr($notification['post_content'], 0, 100)); ?><?php echo strlen($notification['post_content']) > 100 ? '...' : ''; ?>"
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Notification time -->
                                    <div class="notification-time text-muted small">
                                        <?php echo format_timestamp($notification['created_at']); ?>
                                    </div>
                                </div>
                                
                                <!-- Link to related content -->
                                <a href="<?php echo get_notification_url($notification); ?>" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Improved notification styles */
    .notification-visual {
        position: relative;
        width: 70px;
        height: 50px;
    }
    
    /* Icon badge - now more prominent */
    .notification-icon-badge {
        position: absolute;
        top: -5px;
        left: -5px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 2px solid white;
        z-index: 20; /* Ensure icon is always on top */
        transition: all 0.2s;
    }
    
    .card:hover .notification-icon-badge {
        transform: scale(1.1);
    }
    
    /* Avatar group styling */
    .avatar-group {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .avatar-item {
        position: absolute;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        transition: all 0.2s;
    }
    
    .avatar-item img, .avatar-placeholder {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .avatar-placeholder {
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 1.2rem;
    }
    
    /* Position avatars in a neat arrangement */
    .avatar-item:nth-child(1) {
        transform: translateX(-50%);
    }
    
    .avatar-item:nth-child(2) {
        transform: translateX(0%);
    }
    
    .avatar-item:nth-child(3) {
        transform: translateX(50%);
    }
    
    /* More users count badge */
    .avatar-more {
        position: absolute;
        bottom: -5px;
        right: 5px;
        background-color: #0d6efd;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
        border: 2px solid white;
        z-index: 15;
    }
    
    /* Unread notification style */
    .notification-unread {
        border-left: 4px solid var(--bs-primary) !important;
    }
    
    /* Card hover effect */
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
    
    /* On hover, slightly spread the avatars for a nice effect */
    .card:hover .avatar-item:nth-child(1) {
        transform: translateX(-60%);
    }
    
    .card:hover .avatar-item:nth-child(3) {
        transform: translateX(60%);
    }
</style>

<?php
$content = ob_get_clean();
// Render with layout
include __DIR__ . '/../../resources/components/layout.php';
?>