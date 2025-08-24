<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_name = 'clg_ass';
$db_user = 'root';
$db_pass = '';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Get statistics
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM student_registration");
    $total_students = $stmt->fetch()['total'];
    
    // Get total agents
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agents");
    $total_agents = $stmt->fetch()['total'];
    
    // Get recent registrations
    $stmt = $pdo->query("SELECT * FROM student_registration ORDER BY id DESC LIMIT 8");
    $recent_students = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error_message = 'Database connection error: ' . $e->getMessage();
    $total_students = 0;
    $total_agents = 0;
    $recent_students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Election Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #1a202c;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 2rem 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h1 {
            color: #2d3748;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: #718096;
            font-size: 0.9rem;
        }

        .quick-actions {
            flex: 1;
            padding: 1.5rem;
        }

        .section-title {
            color: #2d3748;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .action-btn.secondary {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }

        .action-btn.secondary:hover {
            box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);
        }

        .action-btn.tertiary {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .action-btn.tertiary:hover {
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
        }

        .action-btn i {
            font-size: 1rem;
        }

        .admin-profile {
            padding: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            margin-top: auto;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .admin-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .admin-details h3 {
            color: #2d3748;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .admin-details p {
            color: #718096;
            font-size: 0.8rem;
        }

        .logout-btn {
            width: 100%;
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            overflow-x: hidden;
        }

        .main-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .main-header h2 {
            color: #2d3748;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .main-header p {
            color: #718096;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #718096;
            font-size: 1rem;
            font-weight: 500;
        }

        .content-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .content-header {
            display: flex;
            align-items: center;
            justify-content: between;
            margin-bottom: 2rem;
        }

        .content-header h3 {
            color: #2d3748;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .voter-list {
            display: grid;
            gap: 1rem;
        }

        .voter-card {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .voter-card:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(102, 126, 234, 0.3);
            transform: translateY(-2px);
        }

        .voter-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .voter-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .voter-details h4 {
            color: #2d3748;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .voter-details p {
            color: #718096;
            font-size: 0.9rem;
        }

        .view-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .dashboard-container {
                flex-direction: column;
            }

            .main-header h2 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .voter-card {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Floating particles animation */
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
            animation: float 8s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 20%;
            right: 20%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 30%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            bottom: 30%;
            right: 10%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
            }
            25% { 
                transform: translateY(-20px) rotate(90deg); 
            }
            50% { 
                transform: translateY(-40px) rotate(180deg); 
            }
            75% { 
                transform: translateY(-20px) rotate(270deg); 
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape">
            <i class="fas fa-vote-yea" style="font-size: 60px; color: #667eea;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-users" style="font-size: 50px; color: #764ba2;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-chart-bar" style="font-size: 55px; color: #f093fb;"></i>
        </div>
        <div class="shape">
            <i class="fas fa-cog" style="font-size: 45px; color: #4facfe;"></i>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1><i class="fas fa-poll-h"></i> Admin Panel</h1>
                <p>Election Management System</p>
            </div>

            <div class="quick-actions">
                <h3 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h3>
                <div class="action-grid">
                    <a href="view_all_voters.php" class="action-btn">
                        <i class="fas fa-users"></i>
                        <span>View All Voters</span>
                    </a>
                    <a href="add_agent.php" class="action-btn secondary">
                        <i class="fas fa-user-plus"></i>
                        <span>Add New Agent</span>
                    </a>
                    <a href="view_agent.php" class="action-btn tertiary">
                        <i class="fas fa-user-tie"></i>
                        <span>View Agents</span>
                    </a>
                    <a href="add_party.php" class="action-btn">
                        <i class="fas fa-flag"></i>
                        <span>Add Party</span>
                    </a>
                    <a href="view_parties.php" class="action-btn secondary">
                        <i class="fas fa-list"></i>
                        <span>View Parties</span>
                    </a>
                    <a href="voting_schedule.php" class="action-btn tertiary">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Set Voting Schedule</span>
                    </a>
                    <a href="../results.php" class="action-btn">
                        <i class="fas fa-chart-pie"></i>
                        <span>View Results</span>
                    </a>
                    <a href="system_settings.php" class="action-btn secondary">
                        <i class="fas fa-cogs"></i>
                        <span>System Settings</span>
                    </a>
                </div>
            </div>

            <div class="admin-profile">
                <div class="admin-info">
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                    </div>
                    <div class="admin-details">
                        <h3><?php echo htmlspecialchars($admin_username); ?></h3>
                        <p>System Administrator</p>
                    </div>
                </div>
                <a href="?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="main-header">
                <h2>Dashboard Overview</h2>
                <p>Welcome back! Here's what's happening with your election system today.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Voters</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_agents; ?></div>
                    <div class="stat-label">Active Agents</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Today's Activity</div>
                </div>
            </div>

            <div class="content-section">
                <div class="content-header">
                    <h3>
                        <i class="fas fa-user-check"></i>
                        Recent Voter Registrations
                    </h3>
                </div>

                <?php if (!empty($recent_students)): ?>
                    <div class="voter-list">
                        <?php foreach ($recent_students as $student): ?>
                            <div class="voter-card">
                                <div class="voter-info">
                                    <div class="voter-avatar">
                                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                    </div>
                                    <div class="voter-details">
                                        <h4><?php echo htmlspecialchars($student['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($student['email']); ?></p>
                                    </div>
                                </div>
                                <a href="view_voters.php?id=<?php echo $student['id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-plus"></i>
                        <h3>No Recent Registrations</h3>
                        <p>No voters have registered recently. Check back later for updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to cards
            const cards = document.querySelectorAll('.stat-card, .voter-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Animate counter numbers
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/\D/g, ''));
                if (target && !isNaN(target)) {
                    let current = 0;
                    const increment = target / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            counter.textContent = target + (counter.textContent.includes('%') ? '%' : '');
                            clearInterval(timer);
                        } else {
                            counter.textContent = Math.floor(current) + (counter.textContent.includes('%') ? '%' : '');
                        }
                    }, 30);
                }
            });
        });
    </script>
</body>
</html>