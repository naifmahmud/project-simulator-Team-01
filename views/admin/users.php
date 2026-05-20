<?php
$isEdit = !empty($editing);
$messages = [
    'added'   => 'User added successfully.',
    'updated' => 'User updated successfully.',
    'deleted' => 'User deleted successfully.',
];
$msg = $messages[$_GET['msg'] ?? ''] ?? null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management &mdash; Travel Guide Admin</title>
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
            <h1 class="page-title">User Management</h1>
            <p class="page-sub">Add, edit, verify and remove user accounts</p>
        </div>
        <div class="header-actions">
            <a href="index.php?page=admin" class="btn btn-ghost">&larr; Dashboard</a>
            <a href="index.php?page=admin&section=posts" class="btn btn-ghost">Posts</a>
            <a href="index.php?page=admin&section=comments" class="btn btn-ghost">Comments</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ============ Add / Edit Form ============ -->
    <div class="card form-card">
        <h3 class="card-title"><?= $isEdit ? '&#9998; Edit User (#' . intval($editing['id']) . ')' : '+ Add New User' ?></h3>
        <form method="POST"
              action="index.php?page=admin&section=users&action=<?= $isEdit ? 'update&id=' . intval($editing['id']) : 'add' ?>"
              class="form" novalidate>

            <div class="field-row">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                           placeholder="Full name" required>
                </div>
                <div class="field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($editing['email'] ?? '') ?>"
                           placeholder="Email address" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="user"  <?= ($editing['role'] ?? '') === 'user'  ? 'selected' : '' ?>>User</option>
                        <option value="scout" <?= ($editing['role'] ?? '') === 'scout' ? 'selected' : '' ?>>Scout</option>
                        <option value="admin" <?= ($editing['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <?php if (!$isEdit): ?>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Min 8 characters" required>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!$isEdit): ?>
            <div class="field">
                <label class="checkbox">
                    <input type="checkbox" name="is_verified" value="1" checked>
                    <span>Verify user immediately</span>
                </label>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=admin&section=users" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update User</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Save User</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ============ Users Table ============ -->
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Search by name, email or role...">
            </div>
            <span class="badge" id="resultCount"><?= $totalUsers ?> total</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Verified</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="empty">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $i => $u): ?>
                        <tr id="user-row-<?= $u['id'] ?>">
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td id="verified-<?= $u['id'] ?>">
                                <?= $u['is_verified']
                                    ? '<span class="badge badge-success">Yes</span>'
                                    : '<span class="badge badge-warning">No</span>' ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td class="text-right">
                                <a class="btn-sm btn-edit"
                                   href="index.php?page=admin&section=users&action=edit&id=<?= $u['id'] ?>">Edit</a>
                                <!-- AJAX verify/unverify button -->
                                <button class="btn-sm btn-edit"
                                        onclick="toggleVerify(<?= $u['id'] ?>, <?= $u['is_verified'] ?>)"
                                        id="verify-btn-<?= $u['id'] ?>">
                                    <?= $u['is_verified'] ? 'Unverify' : 'Verify' ?>
                                </button>
                                <a class="btn-sm btn-delete"
                                   href="index.php?page=admin&section=users&action=delete&id=<?= $u['id'] ?>"
                                   onclick="return confirm('Delete this user and all their data?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="index.php?page=admin&section=users&p=<?= $currentPage - 1 ?>">&laquo; Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $currentPage): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="index.php?page=admin&section=users&p=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($currentPage < $totalPages): ?>
                <a href="index.php?page=admin&section=users&p=<?= $currentPage + 1 ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Travel Guide Admin</footer>

<script>
(function () {
    /* =========== AJAX: Toggle verify/unverify =========== */
    function toggleVerify(id, currentStatus) {
        var newStatus = currentStatus == 1 ? 0 : 1;
        var label     = newStatus == 1 ? 'Verify' : 'Unverify';

        if (!confirm((newStatus == 1 ? 'Verify' : 'Unverify') + ' this user?')) return;

        fetch('api/admin/verify_user.php?id=' + id + '&status=' + newStatus,
            { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    // Update badge
                    var cell = document.getElementById('verified-' + id);
                    cell.innerHTML = newStatus == 1
                        ? '<span class="badge badge-success">Yes</span>'
                        : '<span class="badge badge-warning">No</span>';
                    // Update button
                    var btn = document.getElementById('verify-btn-' + id);
                    btn.textContent = newStatus == 1 ? 'Unverify' : 'Verify';
                    btn.setAttribute('onclick', 'toggleVerify(' + id + ', ' + newStatus + ')');
                } else {
                    alert(data.message || 'Failed to update verification.');
                }
            })
            .catch(function (e) { console.error(e); });
    }

    // Expose to inline onclick
    window.toggleVerify = toggleVerify;

    /* =========== Inline AJAX search =========== */
    var input   = document.getElementById('searchInput');
    var body    = document.getElementById('tableBody');
    var counter = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function render(rows) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="empty">No matching results.</td></tr>';
            counter.textContent = '0 results';
            return;
        }
        var html = '';
        rows.forEach(function (u, i) {
            var verified = u.is_verified == 1
                ? '<span class="badge badge-success">Yes</span>'
                : '<span class="badge badge-warning">No</span>';
            html += '<tr id="user-row-' + u.id + '">'
                + '<td>' + (i + 1) + '</td>'
                + '<td>' + esc(u.name) + '</td>'
                + '<td>' + esc(u.email) + '</td>'
                + '<td>' + esc(u.role) + '</td>'
                + '<td id="verified-' + u.id + '">' + verified + '</td>'
                + '<td>' + esc(u.created_at) + '</td>'
                + '<td class="text-right">'
                + '<a class="btn-sm btn-edit" href="index.php?page=admin&section=users&action=edit&id=' + u.id + '">Edit</a>'
                + '<button class="btn-sm btn-edit" onclick="toggleVerify(' + u.id + ',' + u.is_verified + ')" id="verify-btn-' + u.id + '">'
                + (u.is_verified == 1 ? 'Unverify' : 'Verify') + '</button>'
                + '<a class="btn-sm btn-delete" href="index.php?page=admin&section=users&action=delete&id=' + u.id + '" onclick="return confirm(\'Delete this user and all their data?\')">Delete</a>'
                + '</td>'
                + '</tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=users&q=' + encodeURIComponent(input.value.trim()),
                { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        }, 200);
    });
}());
</script>

</body>
</html>
