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

$voter = null;
$error_message = '';

// Securely validate and fetch voter details
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = intval($_GET['id']);

    // SECURITY FIX: Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM student_registration WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $voter = $result->fetch_assoc();
    } else {
        $error_message = "Voter not found.";
    }
    $stmt->close();
} else {
    $error_message = "A valid Voter ID was not provided.";
}

$conn->close();

// Determine icon based on gender
$iconClass = 'fa-user'; // Default icon
if ($voter) {
    switch (strtolower($voter['gender'])) {
        case 'male':
            $iconClass = 'fa-male';
            break;
        case 'female':
            $iconClass = 'fa-female';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Details - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 font-sans">
    <div class="flex min-h-screen">

        <main class="flex-1 p-10">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-4xl font-extrabold text-slate-800">Voter Profile</h1>
                <a href="admin_dashboard.php" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-md hover:bg-slate-50 transition-colors text-sm font-semibold flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>

            <?php if ($voter): ?>
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-700 to-indigo-900 p-8">
                    <div class="flex items-center">
                        <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center border-4 border-blue-500">
                            <i class="fas <?php echo $iconClass; ?> text-5xl text-white"></i>
                        </div>
                        <div class="ml-6">
                            <h2 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($voter['name']); ?></h2>
                            <p class="text-indigo-200 text-sm font-medium"><?php echo htmlspecialchars($voter['email']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Personal Details</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div><dt class="text-sm font-semibold text-slate-500">Gender</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['gender']); ?></dd></div>
                            <div><dt class="text-sm font-semibold text-slate-500">Phone Number</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['phone']); ?></dd></div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Address Information</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div><dt class="text-sm font-semibold text-slate-500">Village/Town</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['village_town']); ?></dd></div>
                            <div><dt class="text-sm font-semibold text-slate-500">Post Office</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['post']); ?></dd></div>
                            <div><dt class="text-sm font-semibold text-slate-500">PIN Code</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['pin']); ?></dd></div>
                            <div><dt class="text-sm font-semibold text-slate-500">Police Station</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['police_station']); ?></dd></div>
                            <div class="md:col-span-2"><dt class="text-sm font-semibold text-slate-500">District</dt><dd class="mt-1 text-lg text-slate-900"><?php echo htmlspecialchars($voter['district']); ?></dd></div>
                        </dl>
                    </div>
                </div>

                <div class="bg-slate-50 px-8 py-5 flex justify-end items-center space-x-3">
                    <a href="edit_voter.php?id=<?php echo $voter['id']; ?>" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-sm flex items-center"><i class="fas fa-pencil-alt mr-2"></i>Edit</a>
                    <button onclick="confirmDelete(<?php echo $voter['id']; ?>)" class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold text-sm flex items-center"><i class="fas fa-trash-alt mr-2"></i>Delete</button>
                </div>
            <a href="admin_dashboard.php" class="mt-4 inline-block text-xl text-blue-600 hover:underline"><-------Back to Dashboard</a>

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
        function confirmDelete(voterId) {
            if (confirm(`Are you sure you want to delete this voter (ID: ${voterId})? This action cannot be undone.`)) {
                // To make this functional, you would redirect to a delete script.
                // window.location.href = 'delete_voter.php?id=' + voterId;
                alert('Deletion functionality is for demonstration and not implemented here.');
            }
        }
    </script>
</body>
</html>