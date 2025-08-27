<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$db_host = 'localhost';
$db_name = 'clg_ass';
$db_user = 'root';
$db_pass = '';
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    die('DB error');
}

require_once __DIR__ . '/../upload_helper.php';

$mode = isset($_GET['edit']) ? 'edit' : 'list';
$message = '';
$error = '';

// Fetch single party for edit
if ($mode === 'edit') {
    $id = (int)($_GET['edit'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM parties WHERE party_id = ? OR participates_id = ? LIMIT 1');
    $stmt->execute([$id, $id]);
    $party = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$party) {
        $error = 'Party not found.';
        $mode = 'list';
    }
}

// Handle update
if ($mode === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $politician_name = trim($_POST['politician_name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $party_name = trim($_POST['party_name'] ?? '');
    $booth_name = trim($_POST['booth_name'] ?? '');
    $booth_id = trim($_POST['booth_id'] ?? '');
    $pid = $party['participates_id'] ?? $party['party_id'];
    // Uploads (optional replacement)
    $upload_dir_fs = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
    $upload_dir_db = 'uploads/';
    // Optional image replacements: if no new file chosen helper returns null and we keep old path
    $upErr = '';
    $new_pol_img = secure_image_upload('politician_image', $upload_dir_fs, $upload_dir_db, 'politician_', $upErr, 5 * 1024 * 1024);
    if ($upErr) {
        $error = $upErr;
    }
    $upErr2 = '';
    $new_logo    = secure_image_upload('party_logo', $upload_dir_fs, $upload_dir_db, 'party_', $upErr2, 5 * 1024 * 1024);
    if (!$error && $upErr2) {
        $error = $upErr2;
    }
    if (!$error) {
        $fields = ['politician_name' => $politician_name, 'age' => $age, 'party_name' => $party_name, 'booth_name' => $booth_name, 'booth_id' => $booth_id];
        if ($new_pol_img) $fields['politician_image'] = $new_pol_img;
        else $fields['politician_image'] = $party['politician_image'];
        if ($new_logo) $fields['party_logo'] = $new_logo;
        else $fields['party_logo'] = $party['party_logo'];
        try {
            $stmt = $pdo->prepare('UPDATE parties SET politician_name=?, politician_image=?, age=?, party_name=?, party_logo=?, booth_name=?, booth_id=? WHERE participates_id=? OR party_id=?');
            $stmt->execute([$fields['politician_name'], $fields['politician_image'], $fields['age'], $fields['party_name'], $fields['party_logo'], $fields['booth_name'], $fields['booth_id'], $pid, $pid]);
            $message = 'Party updated successfully';
            // refresh party data
            $stmt = $pdo->prepare('SELECT * FROM parties WHERE participates_id=? OR party_id=? LIMIT 1');
            $stmt->execute([$pid, $pid]);
            $party = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $error = 'Update failed.';
        }
    }
}

// List fetch
if ($mode === 'list') {
    $rows = $pdo->query('SELECT participates_id, party_id, party_name, politician_name, age, party_logo, politician_image, booth_name, booth_id FROM parties ORDER BY party_name ASC')->fetchAll(PDO::FETCH_ASSOC);
}

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Parties</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background: #f1f5f9;
            font-family: system-ui, Arial, sans-serif
        }
    </style>
</head>

<body class="min-h-screen">
    <div class="max-w-7xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3"><i class="fas fa-flag"></i> <?= $mode === 'edit' ? 'Edit Party' : 'Parties' ?></h1>
            <div class="flex gap-2">
                <a href="add_party.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700"><i class="fas fa-plus mr-1"></i>Add Party</a>
                <a href="admin_dashboard.php" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-300"><i class="fas fa-arrow-left mr-1"></i>Dashboard</a>
                <?php if ($mode === 'edit'): ?><a href="view_parties.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-list mr-1"></i>All Parties</a><?php endif; ?>
            </div>
        </div>
        <?php if ($message): ?><div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-300 text-green-800 text-sm"><i class="fas fa-check-circle mr-2"></i><?= h($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-300 text-red-800 text-sm"><i class="fas fa-triangle-exclamation mr-2"></i><?= h($error) ?></div><?php endif; ?>

        <?php if ($mode === 'list'): ?>
            <?php if (!$rows): ?>
                <div class="p-12 text-center bg-white rounded-xl border shadow-sm">
                    <i class="fas fa-circle-exclamation text-4xl text-slate-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-slate-700 mb-2">No parties found</h3>
                    <p class="text-slate-500 mb-4">Create your first party to get started.</p>
                    <a href="add_party.php" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold"><i class="fas fa-plus mr-2"></i>Add Party</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto bg-white rounded-xl border shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 text-slate-600 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Logo</th>
                                <th class="px-4 py-3 text-left">Party</th>
                                <th class="px-4 py-3 text-left">Candidate</th>
                                <th class="px-4 py-3 text-left">Age</th>
                                <th class="px-4 py-3 text-left">Booth</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach ($rows as $r): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <img src="../<?= h($r['party_logo']) ?>" class="h-10 w-10 rounded-full object-cover border" alt="logo">
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-800"><?= h($r['party_name']) ?><div class="text-[10px] text-slate-500">ID:<?= h($r['party_id']) ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <img src="../<?= h($r['politician_image']) ?>" class="h-8 w-8 rounded-full object-cover border" alt="candidate">
                                            <span><?= h($r['politician_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><?= h($r['age']) ?></td>
                                    <td class="px-4 py-3 text-slate-600"><span class="font-medium"><?= h($r['booth_name']) ?></span>
                                        <div class="text-[10px] text-slate-400">ID:<?= h($r['booth_id']) ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="view_parties.php?edit=<?= urlencode($r['participates_id'] ?: $r['party_id']) ?>" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-semibold"><i class="fas fa-edit mr-1"></i>Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($party): ?>
                <form method="post" enctype="multipart/form-data" class="bg-white p-6 rounded-xl border shadow-sm max-w-3xl">
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center gap-2"><i class="fas fa-pen"></i> Update Party</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Party Name</label>
                            <input name="party_name" value="<?= h($party['party_name']) ?>" required class="mt-1 w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50" />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Candidate Name</label>
                            <input name="politician_name" value="<?= h($party['politician_name']) ?>" required class="mt-1 w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50" />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Age</label>
                            <input type="number" name="age" min="18" max="100" value="<?= h($party['age']) ?>" required class="mt-1 w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50" />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Booth Name</label>
                            <input name="booth_name" value="<?= h($party['booth_name']) ?>" required class="mt-1 w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50" />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Booth ID</label>
                            <input name="booth_id" value="<?= h($party['booth_id']) ?>" required class="mt-1 w-full px-3 py-2 rounded-lg border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50" />
                        </div>
                    </div>
                    <div class="mt-6 grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Party Logo (optional)</label>
                            <div class="flex items-center gap-4 mt-2">
                                <img src="../<?= h($party['party_logo']) ?>" class="h-16 w-16 object-cover rounded border" alt="logo">
                                <input type="file" name="party_logo" accept="image/*" class="text-xs" />
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-600 uppercase">Candidate Image (optional)</label>
                            <div class="flex items-center gap-4 mt-2">
                                <img src="../<?= h($party['politician_image']) ?>" class="h-16 w-16 object-cover rounded border" alt="candidate">
                                <input type="file" name="politician_image" accept="image/*" class="text-xs" />
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow" type="submit"><i class="fas fa-save mr-2"></i>Save Changes</button>
                        <a href="view_parties.php" class="px-6 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-semibold text-sm"><i class="fas fa-times mr-2"></i>Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>