<?php
$mysqli = new mysqli("localhost", "root", "", "y_new");

function add_user($conn, $username, $hashed_password) {
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        return $new_id;
    }

    $stmt->close();
    return false;
}

function random_sentence() {
    $samples = [
        "Just had the best coffee ever.",
        "Anyone else love the sound of rain?",
        "Learning PHP is more fun than I thought.",
        "What’s your go-to weekend activity?",
        "Started a new book today – it's so good!",
        "Coding late at night hits different.",
        "Why do Mondays feel like mini-boss fights?",
        "This app is looking better each day.",
        "Throwback to the beach last summer 🌊",
        "Looking forward to the weekend already."
    ];
    return $samples[array_rand($samples)];
}

function random_hashtag() {
    $tags = ['#life', '#coding', '#php', '#coffee', '#rainyday', '#throwback', '#fun', '#weekend', '#booklover', '#motivation'];
    return $tags[array_rand($tags)];
}

$user_ids = [];

for ($i = 1; $i <= 5; $i++) {
    $username = "user" . $i;
    $password = password_hash("password$i", PASSWORD_DEFAULT);
    $user_id = add_user($mysqli, $username, $password);
    if ($user_id) {
        $user_ids[] = $user_id;

        $display_name = ucfirst($username);
        $bio = "Hi, I'm $display_name. Welcome to my profile!";
        $profile_picture = "https://picsum.photos/seed/profile$i/200";
        $cover_image = "https://picsum.photos/seed/cover$i/600/200";

        $stmt = $mysqli->prepare("UPDATE users SET display_name = ?, bio = ?, profile_picture = ?, cover_image = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $display_name, $bio, $profile_picture, $cover_image, $user_id);
        $stmt->execute();
        $stmt->close();

        for ($j = 1; $j <= 30; $j++) {
            $content = random_sentence();
            $stmt = $mysqli->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $user_id, $content);
            $stmt->execute();
            $post_id = $stmt->insert_id;

            // 50% chance to add a hashtag
            if (rand(0, 1)) {
                $hashtag = str_replace('#', '', random_hashtag());
                $stmt_tag = $mysqli->prepare("INSERT INTO hashtags (post_id, hashtag, created_at) VALUES (?, ?, NOW())");
                $stmt_tag->bind_param("is", $post_id, $hashtag);
                $stmt_tag->execute();
                $stmt_tag->close();
            }

            $stmt->close();
        }
    }
}

// Add random follows
foreach ($user_ids as $follower) {
    foreach ($user_ids as $followed) {
        if ($follower !== $followed && rand(0, 1)) {
            $stmt = $mysqli->prepare("INSERT INTO follows (follower_id, followed_id, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $follower, $followed);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Add random likes
$post_result = $mysqli->query("SELECT id FROM posts");
$post_ids = [];
while ($row = $post_result->fetch_assoc()) {
    $post_ids[] = $row['id'];
}

foreach ($post_ids as $post_id) {
    // Up to 3 users like the post
    $liked_users = array_rand($user_ids, rand(1, 3));
    if (!is_array($liked_users)) $liked_users = [$liked_users];
    foreach ($liked_users as $index) {
        $user_id = $user_ids[$index];
        $stmt = $mysqli->prepare("INSERT INTO likes (user_id, post_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        $stmt->close();
    }
}

echo "Test data created successfully!";
?>