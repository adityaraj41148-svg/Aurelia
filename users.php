<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Manage Users - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Registered Customer Accounts</h2>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Registered At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold text-gray-400">#<?= $u['id'] ?></td>
                            <td class="px-6 py-4 font-bold text-gray-800"><?= sanitize($u['name']) ?></td>
                            <td class="px-6 py-4"><?= sanitize($u['email']) ?></td>
                            <td class="px-6 py-4"><?= sanitize($u['phone'] ?: 'N/A') ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase bg-emerald-100 text-emerald-700">
                                    <?= sanitize($u['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-400"><?= sanitize($u['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
