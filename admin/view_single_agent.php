<?php
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "clg_ass");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$agent = null;
$error_message = '';

// Fetch agent data securely
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = $_GET['id'];

    // SECURITY FIX: Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM agents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $agent = $result->fetch_assoc();
    } else {
        $error_message = "Agent not found.";
    }
    $stmt->close();
} else {
    $error_message = "A valid Agent ID was not provided.";
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Agent - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 font-sans">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-800 text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-slate-700">
                Admin Panel
            </div>
            <nav class="flex-1 p-4">
                <ul>
                    <li><a href="admin_dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors"><i class="fas fa-tachometer-alt w-6"></i>Dashboard</a></li>
                    <li class="mt-2"><a href="view_agents.php" class="flex items-center p-3 rounded-lg bg-slate-900 font-semibold transition-colors"><i class="fas fa-users w-6"></i>Agents</a></li>
                    <li class="mt-2"><a href="#" class="flex items-center p-3 rounded-lg hover:bg-slate-700 transition-colors"><i class="fas fa-cogs w-6"></i>Settings</a></li>
                </ul>
            </nav>
            <div class="p-4 border-t border-slate-700">
                <a href="admin_logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-700 transition-colors"><i class="fas fa-sign-out-alt w-6"></i>Logout</a>
            </div>
        </aside>

        <main class="flex-1 p-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-4xl font-extrabold text-slate-800">Agent Profile</h1>
                <a href="view_agents.php" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-md hover:bg-slate-50 transition-colors text-sm font-semibold flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Agents List
                </a>
            </div>

            <?php if ($agent): ?>
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-8">
                    <div class="flex items-center">
                        <div class="w-24 h-24 bg-slate-600 rounded-full flex items-center justify-center border-4 border-slate-500">
                            <i class="fas fa-user-tie text-5xl text-slate-400"></i>
                        </div>
                        <div class="ml-6">
                            <h2 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($agent['agent_name']); ?></h2>
                            <p class="text-slate-300 text-sm font-medium">Agent ID: #<?php echo htmlspecialchars($agent['id']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">Agent Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="border-b border-slate-200 pb-4">
                            <dt class="text-sm font-semibold text-slate-500">Full Name</dt>
                            <dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($agent['agent_name']); ?></dd>
                        </div>
                        <div class="border-b border-slate-200 pb-4">
                            <dt class="text-sm font-semibold text-slate-500">Contact Email (Example)</dt>
                            <dd class="mt-1 text-lg text-slate-900">agent<?php echo htmlspecialchars($agent['id']); ?>@example.com</dd>
                        </div>
                        <div class="border-b border-slate-200 pb-4 col-span-1 md:col-span-2">
                            <dt class="text-sm font-semibold text-slate-500">Residential Address</dt>
                            <dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($agent['agent_address']); ?></dd>
                        </div>
                         <div class="border-b border-slate-200 pb-4">
                            <dt class="text-sm font-semibold text-slate-500">Status (Example)</dt>
                            <dd class="mt-1 text-lg text-slate-900"><span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Active</span></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-slate-50 px-8 py-5 flex justify-end items-center space-x-3">
                    <a href="edit_agent.php?id=<?php echo $agent['id']; ?>" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-sm flex items-center">
                        <i class="fas fa-pencil-alt mr-2"></i>Edit
                    </a>
                    <button onclick="confirmDelete(<?php echo $agent['id']; ?>)" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm flex items-center">
                        <i class="fas fa-trash-alt mr-2"></i>Delete
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="max-w-4xl mx-auto bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg" role="alert">
                <h3 class="font-bold text-lg">Error</h3>
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function confirmDelete(agentId) {
            if (confirm(`Are you sure you want to delete Agent #${agentId}? This action cannot be undone.`)) {
                // If you have a delete script, redirect there.
                // window.location.href = 'delete_agent.php?id=' + agentId;
                alert('Deletion functionality is not implemented in this demo.');
            }
        }
    </script>
</body>
</html>