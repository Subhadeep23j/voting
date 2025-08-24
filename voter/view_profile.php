<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "clg_ass");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$update_message = '';
$update_type = ''; // Can be 'success' or 'error'

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Basic validation
    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_message = "Invalid input. Please provide a valid name and email address.";
        $update_type = 'error';
    } else {
        // Prepare the update statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user_id);

        if ($stmt->execute()) {
            $update_message = "Profile updated successfully!";
            $update_type = 'success';
            // Update the user's name in the session to reflect changes immediately
            $_SESSION['user_name'] = $name;
        } else {
            // Handle potential errors, like a duplicate email address
            if ($conn->errno === 1062) { // 1062 is the MySQL error code for duplicate entry
                 $update_message = "Error: This email address is already registered to another account.";
            } else {
                 $update_message = "An unexpected error occurred. Please try again.";
            }
            $update_type = 'error';
        }
        $stmt->close();
    }
}

// Fetch current user data to display on the page
$stmt = $conn->prepare("SELECT name, email, aadhar_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // This is unlikely if the user is logged in, but it's good practice to check
    session_destroy();
    header("Location: login.php");
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Digital Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <nav class="bg-white/80 backdrop-blur-lg sticky top-0 z-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-vote-yea text-white text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-slate-900">Digital Voting System</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Vote</a>
                    <span class="text-slate-300">|</span>
                    <a href="view_profile.php" class="text-sm font-semibold text-blue-600">My Profile</a>
                    <a href="?logout=1" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg text-sm transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-8">
            <h2 class="text-4xl font-extrabold text-slate-900 mb-2 tracking-tight">My Profile</h2>
            <p class="text-lg text-slate-600">View and update your personal information.</p>
        </div>

        <?php if ($update_message): ?>
        <div class="mb-6 p-4 rounded-lg text-center font-semibold <?php echo $update_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($update_message); ?>
        </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-200">
            <form id="profileForm" method="POST" action="view_profile.php">
                <fieldset id="profileFieldset" disabled>
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 disabled:bg-slate-100 disabled:cursor-not-allowed">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 disabled:bg-slate-100 disabled:cursor-not-allowed">
                        </div>

                        <div>
                            <label for="aadhar" class="block text-sm font-semibold text-slate-700 mb-1">Aadhar Number</label>
                            <div class="relative">
                                <input type="text" id="aadhar" name="aadhar" value="XXXX-XXXX-<?php echo substr(htmlspecialchars($user['aadhar_number']), -4); ?>" readonly class="w-full px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 text-slate-500 cursor-not-allowed">
                                <i class="fas fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Your Aadhar number cannot be changed for security reasons.</p>
                        </div>
                    </div>
                </fieldset>

                <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end items-center">
                    <button type="button" id="editBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-pencil-alt mr-2"></i> Edit Profile
                    </button>
                    
                    <div id="saveCancelContainer" class="hidden flex items-center space-x-3">
                        <button type="button" id="cancelBtn" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-6 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editBtn = document.getElementById('editBtn');
            const saveCancelContainer = document.getElementById('saveCancelContainer');
            const profileFieldset = document.getElementById('profileFieldset');
            const cancelBtn = document.getElementById('cancelBtn');
            const profileForm = document.getElementById('profileForm');

            // Store original form values for the cancel functionality
            const originalValues = {
                name: profileForm.name.value,
                email: profileForm.email.value,
            };

            // When "Edit" is clicked
            editBtn.addEventListener('click', () => {
                profileFieldset.disabled = false;
                editBtn.classList.add('hidden');
                saveCancelContainer.classList.remove('hidden');
                profileForm.name.focus(); // Focus the first editable field
            });

            // When "Cancel" is clicked
            cancelBtn.addEventListener('click', () => {
                // Revert form fields to their original values
                profileForm.name.value = originalValues.name;
                profileForm.email.value = originalValues.email;

                // Lock the form fields again
                profileFieldset.disabled = true;
                editBtn.classList.remove('hidden');
                saveCancelContainer.classList.add('hidden');
            });
        });
    </script>
</body>
</html>