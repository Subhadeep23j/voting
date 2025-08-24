<?php 
include '../db.php'; // contains your database connection

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = trim($_POST['name']);
    $email       = trim($_POST['email']);
    $gender      = $_POST['gender'];
    $phone       = trim($_POST['phone']);
    $village     = trim($_POST['village_town']);
    $post        = trim($_POST['post']);
    $pin         = trim($_POST['pin']);
    $police      = trim($_POST['police_station']);
    $district    = trim($_POST['district']);
    $password    = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Check if email already exists
        $check_sql = "SELECT email FROM student_registration WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Insert query using prepared statements for security
            $sql = "INSERT INTO student_registration (name, email, gender, phone, village_town, post, pin, police_station, district, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $name, $email, $gender, $phone, $village, $post, $pin, $police, $district, $hashed_password);
            
            if ($stmt->execute()) {
                $message = "Registration successful! Welcome aboard, " . htmlspecialchars($name) . "!";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Join Our Community</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#8b5cf6',
                        accent: '#06b6d4',
                        success: '#10b981',
                        danger: '#ef4444',
                        warning: '#f59e0b',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-soft': 'pulseSoft 2s infinite',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes bounceGentle {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        @keyframes pulseSoft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .input-focus {
            transition: all 0.3s ease-in-out;
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.25);
        }
        
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3e%3cg fill='none' fill-rule='evenodd'%3e%3cg fill='%239CA3AF' fill-opacity='0.05'%3e%3cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3e%3c/g%3e%3c/g%3e%3c/svg%3e");
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen bg-pattern">
    <!-- Floating background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-bounce-gentle"></div>
        <div class="absolute top-3/4 right-1/4 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-bounce-gentle" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-1/4 left-1/3 w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-bounce-gentle" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
        <div class="max-w-2xl w-full">
            <!-- Header -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mb-6 shadow-lg">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">Student Registration</h1>
                <p class="text-gray-600 text-lg">Join our academic community today</p>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-purple-600 mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- Registration Form Container -->
            <div class="bg-white/80 backdrop-blur-lg border border-white/20 rounded-2xl shadow-2xl overflow-hidden animate-slide-up">
                <!-- Form Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-user-plus mr-3"></i>
                        Create Your Account
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

                    <!-- Registration Form -->
                    <form method="POST" action="" class="space-y-6" id="registrationForm">
                        <!-- Personal Information Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user text-blue-500 mr-2"></i>
                                Personal Information
                            </h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Full Name -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-signature text-blue-500 mr-2"></i>
                                        Full Name *
                                    </label>
                                    <input type="text" name="name" required
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter your full name">
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-envelope text-purple-500 mr-2"></i>
                                        Email Address *
                                    </label>
                                    <input type="email" name="email" required
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter your email address">
                                </div>

                                <!-- Gender -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-venus-mars text-pink-500 mr-2"></i>
                                        Gender *
                                    </label>
                                    <select name="gender" required
                                            class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:bg-white focus:outline-none transition duration-300">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Phone -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-phone text-green-500 mr-2"></i>
                                        Phone Number *
                                    </label>
                                    <input type="tel" name="phone" required pattern="[0-9]{10}"
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter 10-digit phone number">
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                Address Information
                            </h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Village/Town -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-home text-indigo-500 mr-2"></i>
                                        Village/Town
                                    </label>
                                    <input type="text" name="village_town"
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter village or town">
                                </div>

                                <!-- Post Office -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-mail-bulk text-teal-500 mr-2"></i>
                                        Post Office
                                    </label>
                                    <input type="text" name="post"
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-teal-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter post office">
                                </div>

                                <!-- PIN Code -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-hashtag text-yellow-500 mr-2"></i>
                                        PIN Code
                                    </label>
                                    <input type="text" name="pin" pattern="[0-9]{6}"
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-yellow-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter 6-digit PIN code">
                                </div>

                                <!-- Police Station -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-shield-alt text-red-500 mr-2"></i>
                                        Police Station
                                    </label>
                                    <input type="text" name="police_station"
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter police station">
                                </div>

                                <!-- District -->
                                <div class="space-y-2 md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-map text-purple-500 mr-2"></i>
                                        District
                                    </label>
                                    <input type="text" name="district"
                                           class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:bg-white focus:outline-none transition duration-300"
                                           placeholder="Enter district">
                                </div>
                            </div>
                        </div>

                        <!-- Security Section -->
                        <div class="pb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-lock text-green-600 mr-2"></i>
                                Account Security
                            </h3>
                            
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Password -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-key text-blue-500 mr-2"></i>
                                        Password *
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="password" required minlength="6" id="password"
                                               class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:bg-white focus:outline-none transition duration-300 pr-12"
                                               placeholder="Enter password (min 6 characters)">
                                        <button type="button" onclick="togglePassword('password', this)" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength bg-gray-200" id="passwordStrength"></div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        <i class="fas fa-shield-check text-green-500 mr-2"></i>
                                        Confirm Password *
                                    </label>
                                    <div class="relative">
                                        <input type="password" name="confirm_password" required minlength="6" id="confirmPassword"
                                               class="input-focus w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:bg-white focus:outline-none transition duration-300 pr-12"
                                               placeholder="Confirm your password">
                                        <button type="button" onclick="togglePassword('confirmPassword', this)" 
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="text-sm"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-6">
                            <button type="submit" id="submitBtn"
                                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-8 rounded-xl transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-user-plus mr-3"></i>
                                <span id="submitText">Create Account</span>
                                <i class="fas fa-spinner fa-spin ml-3 hidden" id="loadingIcon"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-center mt-8 text-gray-600">
                <p class="text-sm">
                    Already have an account? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword(fieldId, button) {
            const field = document.getElementById(fieldId);
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = document.getElementById('passwordStrength');
            let score = 0;
            
            if (password.length >= 6) score += 25;
            if (password.match(/[a-z]/)) score += 25;
            if (password.match(/[A-Z]/)) score += 25;
            if (password.match(/[0-9]/)) score += 25;
            
            if (score < 50) {
                strength.className = 'password-strength bg-red-400';
                strength.style.width = score + '%';
            } else if (score < 75) {
                strength.className = 'password-strength bg-yellow-400';
                strength.style.width = score + '%';
            } else {
                strength.className = 'password-strength bg-green-400';
                strength.style.width = score + '%';
            }
        });

        // Password match validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchDiv.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.textContent = '✓ Passwords match';
                matchDiv.className = 'text-sm text-green-600';
                this.classList.remove('border-red-500');
                this.classList.add('border-green-500');
            } else {
                matchDiv.textContent = '✗ Passwords do not match';
                matchDiv.className = 'text-sm text-red-600';
                this.classList.remove('border-green-500');
                this.classList.add('border-red-500');
            }
        });

        // Form submission handling
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingIcon = document.getElementById('loadingIcon');
            
            submitText.textContent = 'Creating Account...';
            loadingIcon.classList.remove('hidden');
            submitBtn.disabled = true;
        });

        // Input validation feedback
        document.querySelectorAll('input[required], select[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.classList.add('border-red-500', 'bg-red-50');
                    this.classList.remove('border-gray-200', 'bg-gray-50');
                } else {
                    this.classList.remove('border-red-500', 'bg-red-50');
                    this.classList.add('border-green-500', 'bg-green-50');
                }
            });
        });

        // Phone number validation
        document.querySelector('input[name="phone"]').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });

        // PIN code validation
        document.querySelector('input[name="pin"]').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
    </script>
</body>
</html>