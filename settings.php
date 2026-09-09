<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Site Settings - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $settings = [
            'site_name' => sanitize($_POST['site_name'] ?? ''),
            'contact_email' => sanitize($_POST['contact_email'] ?? ''),
            'contact_phone' => sanitize($_POST['contact_phone'] ?? ''),
            'footer_credit' => sanitize($_POST['footer_credit'] ?? '')
        ];

        foreach ($settings as $key => $val) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v");
            $stmt->execute(['k' => $key, 'v' => $val]);
        }
        $msg = "Settings saved successfully!";
    }
}

// Load current settings
$rows = $db->query("SELECT * FROM settings")->fetchAll();
$current_settings = [];
foreach ($rows as $r) {
    $current_settings[$r['setting_key']] = $r['setting_value'];
}
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Store Configuration & Settings</h2>

        <?php if ($msg): ?><div class="p-4 mb-6 bg-emerald-50 text-emerald-700 rounded-lg max-w-xl"><?= sanitize($msg) ?></div><?php endif; ?>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 max-w-xl">
            <form action="settings.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Store Name</label>
                    <input type="text" name="site_name" value="<?= sanitize($current_settings['site_name'] ?? '𝔸𝔸 𝕄𝕒𝕣𝕥') ?>" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Support Email</label>
                    <input type="email" name="contact_email" value="<?= sanitize($current_settings['contact_email'] ?? 'support@aamart.com') ?>" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Support Phone</label>
                    <input type="text" name="contact_phone" value="<?= sanitize($current_settings['contact_phone'] ?? '+91 98765 43210') ?>" class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Footer Credit Line</label>
                    <input type="text" name="footer_credit" value="<?= sanitize($current_settings['footer_credit'] ?? 'created By Aditya kumar,Adarsh Raj,Anikesh and Aditya') ?>" class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="bg-indigo-600 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-indigo-700 transition text-sm">Save Store Settings</button>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
