<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$db_host = 'localhost';
$db_name = 'clg_ass';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

$message = "";
$error = "";
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $politician_name = $_POST['politician_name'];
    $party_id = $_POST['party_id'];
    $age = $_POST['age'];
    $party_name = $_POST['party_name'];
    $booth_name = $_POST['booth_name'];
    $booth_id = $_POST['booth_id'];

    // Secure uploads using helper
    $upload_dir_fs = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
    $upload_dir_db = 'uploads/';
    $politician_image_path_db = secure_image_upload('politician_image', $upload_dir_fs, $upload_dir_db, 'politician_', $error);
    $party_logo_path_db      = secure_image_upload('party_logo', $upload_dir_fs, $upload_dir_db, 'party_', $error);

    if (!$error && (!$politician_image_path_db || !$party_logo_path_db)) {
        $error = 'Both politician image and party logo are required.';
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO parties (politician_name, politician_image, party_id, age, party_name, party_logo, booth_name, booth_id)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$politician_name, $politician_image_path_db, $party_id, $age, $party_name, $party_logo_path_db, $booth_name, $booth_id]);
            $message = 'Party added successfully!';
        } catch (PDOException $e) {
            $error = 'Error adding party to database.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Party - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e40af',
                        accent: '#f59e0b',
                        success: '#10b981',
                        danger: '#ef4444',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes bounceGentle {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .file-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }

        .bg-grid-slate-100 {
            background-image: url("data:image/svg+xml,%3csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3e%3cg fill='%23f1f5f9' fill-opacity='0.4' fill-rule='evenodd'%3e%3cpath d='m0 40l40-40h-40v40zm40 0v-40h-40l40 40z'/%3e%3c/g%3e%3c/svg%3e");
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] -z-10"></div>

    <!-- Floating Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-bounce-gentle"></div>
        <div class="absolute top-3/4 right-1/4 w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-bounce-gentle" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-1/4 left-1/3 w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-bounce-gentle" style="animation-delay: 2s;"></div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mb-4 shadow-lg">
                <i class="fas fa-flag text-white text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Add New Party</h1>
            <p class="text-gray-600 text-lg">Register a new political party in the system</p>
            <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-purple-600 mx-auto mt-4 rounded-full"></div>
        </div>

        <!-- Main Form Container -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 overflow-hidden animate-slide-up">
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-plus-circle mr-3"></i>
                        Party Registration Form
                    </h2>
                </div>

                <div class="px-8 py-8">
                    <!-- Success/Error Messages -->
                    <?php if ($message): ?>
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl mb-6 flex items-center animate-fade-in">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                            <span class="font-semibold"><?= htmlspecialchars($message) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6 flex items-center animate-fade-in">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                            <span class="font-semibold"><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6" id="partyForm">
                        <!-- Row 1: Politician Info -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Politician Name -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    Politician Name
                                </label>
                                <input type="text" name="politician_name" required
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition duration-300 ease-in-out transform focus:scale-105"
                                    placeholder="Enter politician's full name">
                            </div>

                            <!-- Age -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                                    Age
                                </label>
                                <input type="number" name="age" required min="18" max="100"
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:bg-white focus:outline-none transition duration-300 ease-in-out transform focus:scale-105"
                                    placeholder="Enter age">
                            </div>
                        </div>

                        <!-- Row 2: Politician Image -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                <i class="fas fa-camera text-green-500 mr-2"></i>
                                Politician Image <span class="text-xs text-gray-500">(JPG, PNG, GIF - Max 5MB)</span>
                            </label>
                            <div class="relative">
                                <label for="politicianImage" class="w-full px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-dashed border-green-300 rounded-xl hover:border-green-500 transition duration-300 cursor-pointer group flex items-center justify-center">
                                    <input type="file" name="politician_image" accept="image/*" required id="politicianImage" onchange="previewImage(this, 'politicianPreview')" class="hidden">
                                    <div class="flex items-center justify-center text-green-600 group-hover:text-green-700">
                                        <i class="fas fa-cloud-upload-alt text-2xl mr-3"></i>
                                        <span class="font-semibold">Click to upload politician image</span>
                                    </div>
                                </label>
                                <div id="politicianPreview" class="mt-3 hidden">
                                    <img class="file-preview mx-auto shadow-md">
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Party Info -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Party ID -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    <i class="fas fa-id-badge text-indigo-500 mr-2"></i>
                                    Party ID
                                </label>
                                <input type="text" name="party_id" required
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white focus:outline-none transition duration-300 ease-in-out transform focus:scale-105"
                                    placeholder="Enter unique party ID">
                            </div>

                            <!-- Party Name -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    <i class="fas fa-flag text-red-500 mr-2"></i>
                                    Party Name
                                </label>
                                <input type="text" name="party_name" required
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:bg-white focus:outline-none transition duration-300 ease-in-out transform focus:scale-105"
                                    placeholder="Enter party name">
                            </div>
                        </div>

                        <!-- Row 4: Party Logo -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                <i class="fas fa-image text-yellow-500 mr-2"></i>
                                Party Logo <span class="text-xs text-gray-500">(JPG, PNG, GIF - Max 5MB)</span>
                            </label>
                            <div class="relative">
                                <label for="partyLogo" class="w-full px-4 py-3 bg-gradient-to-r from-yellow-50 to-amber-50 border-2 border-dashed border-yellow-300 rounded-xl hover:border-yellow-500 transition duration-300 cursor-pointer group flex items-center justify-center">
                                    <input type="file" name="party_logo" accept="image/*" required id="partyLogo" onchange="previewImage(this, 'logoPreview')" class="hidden">
                                    <div class="flex items-center justify-center text-yellow-600 group-hover:text-yellow-700">
                                        <i class="fas fa-cloud-upload-alt text-2xl mr-3"></i>
                                        <span class="font-semibold">Click to upload party logo</span>
                                    </div>
                                </label>
                                <div id="logoPreview" class="mt-3 hidden">
                                    <img class="file-preview mx-auto shadow-md">
                                </div>
                            </div>
                        </div>

                        <!-- Row 5: Booth Info -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Booth Name -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    <i class="fas fa-map-marker-alt text-pink-500 mr-2"></i>
                                    Booth Name
                                </label>
                                <input type="text" name="booth_name" required
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:bg-white focus:outline-none transition duration-300 ease-in-out transform focus:scale-105"
                                    placeholder="Enter booth name">
                            </div>

                            <!-- Booth ID -->
                            <div class="space-y-2">
                                <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                    <i class="fas fa-hashtag text-teal-500 mr-2"></i>
                                    Booth ID
                                </label>
                                <input type="text" name="booth_id" required
                                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:bg-white focus:outline-none transition duration-300 ease-in-out transform focus:scale-105"
                                    placeholder="Enter booth ID">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6">
                            <button type="submit" id="submitBtn"
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-8 rounded-xl transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-300">
                                <i class="fas fa-plus-circle mr-3"></i>
                                <span id="submitText">Add Party to System</span>
                                <i class="fas fa-spinner fa-spin ml-3 hidden" id="loadingIcon"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Navigation -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <a href="admin_dashboard.php"
                            class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition duration-300 ease-in-out transform hover:scale-105">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image preview functionality
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const img = preview.querySelector('img');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Form submission handling
        document.getElementById('partyForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingIcon = document.getElementById('loadingIcon');

            submitText.textContent = 'Processing...';
            loadingIcon.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        });

        // Add input validation feedback
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('border-red-500', 'bg-red-50');
                    this.classList.remove('border-gray-200', 'bg-gray-50');
                } else {
                    this.classList.remove('border-red-500', 'bg-red-50');
                    this.classList.add('border-green-500', 'bg-green-50');
                }
            });

            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('border-red-500', 'bg-red-50');
                    this.classList.add('border-green-500', 'bg-green-50');
                }
            });
        });

        // Animate form elements on load
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('.space-y-2');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    element.style.transition = 'all 0.5s ease-out';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>

</html>