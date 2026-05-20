<?php
$messages = [
    'added'   => 'User added successfully.',
    'updated' => 'Updated successfully.',
    'deleted' => 'Deleted successfully.',
    'approved'=> 'Post approved successfully.',
    'rejected'=> 'Post request rejected.',
];
$msg = $messages[$_GET['msg'] ?? ''] ?? null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard &mdash; Travel Guide</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=admin">
            <span class="brand-icon">📖</span>
            <span>Travel Guide</span>
        </a>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">A</span>
                <span class="user-meta">
                    <span class="user-name">Administrator</span>
                    <span class="user-role">Admin</span>
                </span>
            </span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Admin Dashboard</h1>
            <p class="page-sub">Overview of users, posts and comments</p>
        </div>
        <div class="header-actions">
            <a href="index.php?page=admin&section=users" class="btn btn-primary">Manage Users</a>
            <a href="index.php?page=admin&section=posts" class="btn btn-primary">Moderate Posts</a>
            <a href="index.php?page=admin&section=comments" class="btn btn-primary">Moderate Comments</a>
            <a href="index.php?page=logout" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3 class="stat-label">Total Users</h3>
            <div class="stat-number"><?= $totalUsers ?></div>
            <div class="stat-sub">
                Admins: <?= $adminCount ?> &bull;
                Scouts: <?= $scoutCount ?> &bull;
                Users: <?= $userCount ?>
            </div>
            <a href="index.php?page=admin&section=users">Manage Users &rarr;</a>
        </div>

        <div class="stat-card">
            <h3 class="stat-label">Pending Requests</h3>
            <div class="stat-number"><?= $pendingRequests ?></div>
            <div class="stat-sub">Posts awaiting approval</div>
            <a href="index.php?page=admin&section=posts&action=pending">Review Requests &rarr;</a>
        </div>

        <div class="stat-card">
            <h3 class="stat-label">Approved Posts</h3>
            <div class="stat-number"><?= $approvedPosts ?></div>
            <div class="stat-sub">Travel guides published</div>
            <a href="index.php?page=admin&section=posts&action=approved">View Posts &rarr;</a>
        </div>

        <div class="stat-card">
            <h3 class="stat-label">Total Comments</h3>
            <div class="stat-number"><?= $totalComments ?></div>
            <div class="stat-sub">User comments on posts</div>
            <a href="index.php?page=admin&section=comments">Moderate Comments &rarr;</a>
        </div>
    </div>

    <!-- Quick Nav -->
    <div class="card">
        <h3 class="card-title">Quick Navigation</h3>
        <div class="quick-nav">
            <a href="index.php?page=admin&section=users&action=add" class="btn btn-primary">+ Add New User</a>
            <a href="index.php?page=admin&section=posts&action=pending" class="btn btn-primary">Review Pending Posts</a>
            <a href="index.php?page=admin&section=comments" class="btn btn-primary">View All Comments</a>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide Admin</footer>

</body>
</html>
