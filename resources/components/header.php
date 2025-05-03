<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title ?? 'Y'); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Common styles -->
    <style>
        .tweet:hover { background-color: #f8f9fa; }
        .follow-button.following:hover::after {
            content: 'Unfollow';
        }
        .follow-button.following::after {
            content: 'Following';
        }
        .follow-button.following:hover {
            background-color: #f8d7da;
            color: #dc3545;
            border-color: #dc3545;
        }
    </style>
    <?php if (isset($extra_styles)): ?>
    <style>
        <?php echo $extra_styles; ?>
    </style>
    <?php endif; ?>
</head>