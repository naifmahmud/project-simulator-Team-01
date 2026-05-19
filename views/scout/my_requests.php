<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Travel Guide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#127760;</span>
            <span>Travel Guide</span>
        </a>
        <nav class="nav-menu">
            <a href="index.php?page=scout-create-request">Create Request</a>
            <a href="index.php?page=scout-my-requests" class="active">My Requests</a>
            <a href="index.php?page=scout-approved-posts">Approved Posts</a>
            <a href="index.php?page=logout">Logout</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar">
                    <?= htmlspecialchars(substr($_SESSION['user_name'], 0, 1)) ?>
                </span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span class="user-role">Scout</span>
                </span>
            </span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">My Requests</h1>
        <p class="page-sub">Manage your submitted post requests</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <?php if (!empty($requests)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Country</th>
                            <th>Genre</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <?php $data = $request['post_data_decoded'] ?? []; ?>
                            <tr>
                                <td><?= htmlspecialchars($request['id']) ?></td>
                                <td><?= htmlspecialchars($data['title'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($data['country'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(ucfirst($data['genre'] ?? 'N/A')) ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'badge-warning',
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-error'
                                    ];
                                    $class = $statusClass[$request['status']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $class ?>">
                                        <?= htmlspecialchars(ucfirst($request['status'])) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($request['requested_at'])) ?></td>
                                <td>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <a href="index.php?page=scout-edit-request&id=<?= $request['id'] ?>" 
                                           class="btn btn-sm btn-primary">Edit</a>
                                        <button onclick="deleteRequest(<?= $request['id'] ?>)" 
                                                class="btn btn-sm btn-danger">Delete</button>
                                    <?php else: ?>
                                        <span class="text-muted">Read-only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>You haven't submitted any requests yet.</p>
                <a href="index.php?page=scout-create-request" class="btn btn-primary">Create Your First Request</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function deleteRequest(requestId) {
    if (!confirm('Are you sure you want to delete this request? This action cannot be undone.')) {
        return;
    }
    
    fetch('ajax/delete_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ request_id: requestId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Request deleted successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to delete request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

</body>
</html>
