<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Contact Messages - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Customer Contact Inquiries</h2>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Message</th>
                        <th class="px-6 py-3">Received At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($messages)): ?>
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-400">No contact messages received.</td></tr>
                    <?php else: ?>
                        <?php foreach ($messages as $m): ?>
                            <tr>
                                <td class="px-6 py-4 font-bold text-gray-400">#<?= $m['id'] ?></td>
                                <td class="px-6 py-4 font-bold text-gray-800"><?= sanitize($m['name']) ?></td>
                                <td class="px-6 py-4 text-indigo-600 font-semibold"><?= sanitize($m['email']) ?></td>
                                <td class="px-6 py-4 max-w-sm text-gray-600 leading-relaxed"><?= sanitize($m['message']) ?></td>
                                <td class="px-6 py-4 text-xs text-gray-400"><?= sanitize($m['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
