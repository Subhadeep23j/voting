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

$success_message = '';
$error_message = '';

// Insert agent into DB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_name = $conn->real_escape_string($_POST['agent_name']);
    $agent_address = $conn->real_escape_string($_POST['agent_address']);
    $party_name = $conn->real_escape_string($_POST['party_name']);
    $agent_email = $conn->real_escape_string($_POST['agent_email']);
    $dob = $conn->real_escape_string($_POST['dob']);
    
    // Check if agent is at least 18 years old
    $birth_date = new DateTime($dob);
    $today = new DateTime();
    $age = $birth_date->diff($today)->y;
    
    if ($age < 18) {
        $error_message = "Agent must be at least 18 years old to register. Current age: " . $age . " years.";
    }
    
    // Handle file uploads
    $profile_picture_db = '';
    $party_logo_db = '';
    
    // Create upload directory if it doesn't exist
    $upload_dir_fs = '../uploads/';
    $upload_dir_db = 'uploads/';
    if (!is_dir($upload_dir_fs)) {
        mkdir($upload_dir_fs, 0777, true);
    }
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $profile_picture_name = 'profile_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir_fs . $profile_picture_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $profile_picture_db = $upload_dir_db . $profile_picture_name;
            } else {
                $error_message = "Error uploading profile picture.";
            }
        } else {
            $error_message = "Invalid file format for profile picture. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }
    
    // Handle party logo upload
    if (isset($_FILES['party_logo']) && $_FILES['party_logo']['error'] === UPLOAD_ERR_OK && empty($error_message)) {
        $file_extension = pathinfo($_FILES['party_logo']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $party_logo_name = 'logo_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir_fs . $party_logo_name;
            
            if (move_uploaded_file($_FILES['party_logo']['tmp_name'], $upload_path)) {
                $party_logo_db = $upload_dir_db . $party_logo_name;
            } else {
                $error_message = "Error uploading party logo.";
            }
        } else {
            $error_message = "Invalid file format for party logo. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }
    
    // Insert into database if no errors
    if (empty($error_message)) {
        $sql = "INSERT INTO agents (agent_name, agent_address, party_name, agent_email, dob, profile_picture, party_logo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $agent_name, $agent_address, $party_name, $agent_email, $dob, $profile_picture_db, $party_logo_db);

        if ($stmt->execute()) {
            $success_message = "Agent added successfully!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Agent - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h2 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
        }

        .header .subtitle {
            color: #666;
            font-size: 16px;
            font-weight: 400;
        }

        .icon-header {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .icon-header i {
            color: white;
            font-size: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input[type="date"] {
            padding: 15px 20px;
        }

        .form-group input[type="file"] {
            padding: 10px;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 16px;
            z-index: 2;
        }

        .file-upload-wrapper {
            position: relative;
            display: block;
        }

        .file-upload-wrapper input[type="file"] {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-display {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border: 2px dashed #e1e5e9;
            border-radius: 12px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-display:hover {
            border-color: #667eea;
            background: white;
        }

        .file-upload-display i {
            color: #667eea;
            margin-right: 10px;
            font-size: 18px;
        }

        .file-upload-text {
            color: #666;
            font-size: 14px;
        }

        .preview-container {
            margin-top: 10px;
            text-align: center;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 8px;
            border: 2px solid #e1e5e9;
            display: none;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            margin-top: 30px;
            padding: 12px 20px;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .back-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(-5px);
            border-color: #667eea;
        }

        .back-link i {
            margin-right: 8px;
            font-size: 14px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            animation: slideIn 0.5s ease;
        }

        .success-message {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .error-message {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .header h2 {
                font-size: 24px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-group input {
                padding: 12px 15px 12px 45px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape">
            <i class="fas fa-user-tie" style="font-size: 50px; color: #667eea;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-building" style="font-size: 40px; color: #764ba2;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-users" style="font-size: 45px; color: #f093fb;"></i>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div class="icon-header">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Add New Agent</h2>
            <p class="subtitle">Register a new agent to the system</p>
        </div>

        <?php if ($success_message): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="agentForm" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="agent_name">
                        <i class="fas fa-user"></i> Agent Name
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-user"></i>
                        <input type="text" id="agent_name" name="agent_name" required 
                               placeholder="Enter agent's full name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="agent_email">
                        <i class="fas fa-envelope"></i> Agent Email
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-envelope"></i>
                        <input type="email" id="agent_email" name="agent_email" required 
                               placeholder="Enter agent's email">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="agent_address">
                    <i class="fas fa-map-marker-alt"></i> Agent Address
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-map-marker-alt"></i>
                    <input type="text" id="agent_address" name="agent_address" required 
                           placeholder="Enter agent's address">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="party_name">
                        <i class="fas fa-flag"></i> Party Name
                    </label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-flag"></i>
                        <input type="text" id="party_name" name="party_name" required 
                               placeholder="Enter party's name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="dob">
                        <i class="fas fa-birthday-cake"></i> Date of Birth
                    </label>
                    <input type="date" id="dob" name="dob" required>
                </div>
            </div>

            <div class="form-grid">
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
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-plus"></i> Add Agent
            </button>
        </form>

        <a href="admin_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <script>
        // Preview uploaded images
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const fileUploadText = input.parentElement.querySelector('.file-upload-text');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    fileUploadText.textContent = input.files[0].name;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('agentForm');
            const inputs = document.querySelectorAll('input[type="text"], input[type="email"]');
            
            // Add floating label effect
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
            
            // Form submission animation
            form.addEventListener('submit', function(e) {
                const submitBtn = document.querySelector('.submit-btn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Agent...';
                submitBtn.style.pointerEvents = 'none';
            });
        });

        // Add particle effect on successful submission
        <?php if ($success_message): ?>
        document.addEventListener('DOMContentLoaded', function() {
            createConfetti();
        });

        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.style.cssText = `
                        position: fixed;
                        top: -10px;
                        left: ${Math.random() * 100}%;
                        width: 10px;
                        height: 10px;
                        background: ${colors[Math.floor(Math.random() * colors.length)]};
                        pointer-events: none;
                        animation: confettiFall 3s linear forwards;
                        z-index: 1000;
                    `;
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 3000);
                }, i * 30);
            }
        }

        // Add confetti animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes confettiFall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        <?php endif; ?>
    </script>
</body>
</html>