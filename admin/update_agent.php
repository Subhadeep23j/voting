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

// Fetch agent data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM agents WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $agent = $result->fetch_assoc();
    } else {
        echo "Agent not found";
        exit();
    }
} else {
    echo "No agent ID specified";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Agent - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">Update Agent</h2>
            <form action="handle_update_agent.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $agent['id']; ?>">
                <div class="mb-4">
                    <label for="agent_name" class="block text-sm font-medium text-gray-700">Agent Name</label>
                    <input type="text" name="agent_name" id="agent_name" value="<?php echo $agent['agent_name']; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="agent_address" class="block text-sm font-medium text-gray-700">Agent Address</label>
                    <input type="text" name="agent_address" id="agent_address" value="<?php echo $agent['agent_address']; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="form-group">
                    <label for="profile_picture">
                        <i class="fas fa-camera"></i> Profile Picture
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="profile_picture" name="profile_picture" 
                               accept="image/*" onchange="previewImage(this, 'profile_preview')">
                        <div class="file-upload-display">
                            <i class="fas fa-upload"></i>
                            <span class="file-upload-text">Choose profile picture...</span>
                        </div>
                    </div>
                    <div class="preview-container">
                        <img id="profile_preview" class="preview-image" alt="Profile Preview">
                    </div>
                </div>

                <div class="form-group">
                    <label for="party_logo">
                        <i class="fas fa-image"></i> Party Logo
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="party_logo" name="party_logo" 
                               accept="image/*" onchange="previewImage(this, 'logo_preview')">
                        <div class="file-upload-display">
                            <i class="fas fa-upload"></i>
                            <span class="file-upload-text">Choose party logo...</span>
                        </div>
                    </div>
                    <div class="preview-container">
                        <img id="logo_preview" class="preview-image" alt="Logo Preview">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update Agent</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
