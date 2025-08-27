<?php
session_start();
require_once __DIR__ . '/../config.php'; // timezone consistency

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Make sure you store this during login
$user_name = $_SESSION['user_name'] ?? 'User';

// Database connection
$conn = new mysqli("localhost", "root", "", "clg_ass");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch parties from DB
$sql = "SELECT * FROM parties";
$parties = $conn->query($sql);

// Check if user has already voted
$stmt = $conn->prepare("SELECT * FROM votes WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_voted = $result->num_rows > 0;

// Get user's vote details if they have voted
$user_vote_details = null;
if ($has_voted) {
    $stmt = $conn->prepare("SELECT v.*, p.party_name, p.politician_name FROM votes v JOIN parties p ON v.party_id = p.party_id WHERE v.user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user_vote_details = $stmt->get_result()->fetch_assoc();
}

// Voting schedule logic
$schedule_res = $conn->query("SELECT * FROM voting_setting ORDER BY id DESC LIMIT 1");
$schedule = $schedule_res && $schedule_res->num_rows === 1 ? $schedule_res->fetch_assoc() : null;
$now = time();
$voting_open = true; // default if no schedule
$voting_not_started = false;
$voting_ended = false;
$results_published = false;
if ($schedule) {
    $start_ts = strtotime($schedule['start_date'] . ' ' . $schedule['start_time']);
    $end_ts   = strtotime($schedule['end_date'] . ' ' . $schedule['end_time']);
    $force_open = !empty($schedule['force_open']);
    $force_closed = !empty($schedule['force_closed']);
    if ($force_closed) {
        $voting_open = false;
        $voting_ended = true;
        $voting_not_started = false;
    } elseif ($force_open) {
        $voting_open = true;
        $voting_not_started = false;
        $voting_ended = false;
    } elseif ($start_ts && $end_ts) {
        if ($now < $start_ts) {
            $voting_open = false;
            $voting_not_started = true;
        } elseif ($now > $end_ts) {
            $voting_open = false;
            $voting_ended = true;
        }
    }
    // Results only visible to voters AFTER voting ended/forced AND published by admin (result_status=1)
    $results_published = $voting_ended && (int)$schedule['result_status'] === 1;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Voting System - Cast Your Vote</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom animation for elements sliding in */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in-up {
            animation: slideInUp 0.6s ease-out forwards;
        }
    </style>
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
                    <?php if ($has_voted): ?>
                        <div class="flex items-center px-3 py-1 bg-teal-100 text-teal-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-check-circle mr-2"></i> Vote Cast
                        </div>
                    <?php else: ?>
                        <div class="flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold animate-pulse">
                            <i class="fas fa-clock mr-2"></i> Awaiting Vote
                        </div>
                    <?php endif; ?>
                    <?php if ($results_published): ?>
                        <a href="../results.php" class="hidden sm:inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold hover:bg-indigo-200 transition">
                            <i class="fas fa-chart-pie mr-2"></i> Results
                        </a>
                    <?php endif; ?>

                    <div class="hidden sm:flex items-center text-slate-600">
                        <span class="font-medium">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                    </div>

                    <a href="?logout=1" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg text-sm transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-6">
            <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">Your Voice, Your Vote</h2>
            <p class="text-lg text-slate-600 max-w-3xl mx-auto">
                Select your preferred candidate from the list below. Remember, every vote is crucial in shaping our future.
            </p>
            <?php if ($schedule): ?>
                <div class="mt-4 inline-flex flex-col items-center text-sm text-slate-500">
                    <span>Voting Window: <strong><?= htmlspecialchars($schedule['start_date'] . ' ' . $schedule['start_time']) ?></strong> → <strong><?= htmlspecialchars($schedule['end_date'] . ' ' . $schedule['end_time']) ?></strong></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mb-8 text-xs text-slate-500">Server Time (<?= date_default_timezone_get() ?>): <span id="srvTime" class="font-semibold"></span></div>

        <?php if ($voting_not_started): ?>
            <div class="mb-10 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg p-6 shadow-sm animate-slide-in-up">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center mr-5">
                        <i class="fas fa-clock text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-yellow-900">Voting Has Not Started</h3>
                        <p class="text-yellow-800 mt-1">Please return when the voting window opens.</p>
                    </div>
                </div>
            </div>
        <?php elseif ($voting_ended): ?>
            <div class="mb-10 bg-slate-50 border-l-4 border-slate-400 rounded-r-lg p-6 shadow-sm animate-slide-in-up">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-slate-400 rounded-full flex items-center justify-center mr-5">
                        <i class="fas fa-flag-checkered text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Voting Ended</h3>
                        <p class="text-slate-700 mt-1">Thank you for your participation. Results are now available.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($results_published): ?>
            <div class="mb-10 bg-indigo-50 border-l-4 border-indigo-400 rounded-r-lg p-6 shadow-sm animate-slide-in-up">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center mr-5">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-indigo-900">View Final Results</h3>
                        <p class="text-indigo-800 mt-1">Click below to see party standings and vote distribution.</p>
                        <a href="../results.php" class="mt-3 inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition">
                            <i class="fas fa-poll mr-2"></i> Open Results Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($has_voted && $user_vote_details): ?>
            <div class="mb-10 bg-teal-50 border-l-4 border-teal-400 rounded-r-lg p-6 shadow-sm animate-slide-in-up">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-teal-400 rounded-full flex items-center justify-center mr-5">
                        <i class="fas fa-check text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-teal-900">Thank You for Voting!</h3>
                        <p class="text-teal-800 mt-1">
                            You cast your vote for <strong><?php echo htmlspecialchars($user_vote_details['party_name']); ?></strong>
                            (<?php echo htmlspecialchars($user_vote_details['politician_name']); ?>).
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if ($parties->num_rows > 0): $delay_index = 0; ?>
                <?php while ($party = $parties->fetch_assoc()):
                    $is_voted_for = $has_voted && $user_vote_details['party_id'] == $party['party_id'];
                ?>
                    <div class="animate-slide-in-up bg-white rounded-2xl shadow-md border <?php echo $is_voted_for ? 'border-blue-500 ring-4 ring-blue-100' : 'border-slate-200'; ?> overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative" style="animation-delay: <?php echo $delay_index * 100; ?>ms;">

                        <?php if ($is_voted_for): ?>
                            <div class="absolute top-3 left-3 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center">
                                <i class="fas fa-check-circle mr-1.5"></i> YOUR VOTE
                            </div>
                        <?php endif; ?>

                        <img src="../<?php echo htmlspecialchars($party['party_logo']); ?>" alt="<?php echo htmlspecialchars($party['party_name']); ?>" class="w-full h-48 object-cover">

                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-slate-900 truncate"><?php echo htmlspecialchars($party['party_name']); ?></h3>
                            <p class="text-md text-slate-600 font-medium mt-1"><?php echo htmlspecialchars($party['politician_name']); ?></p>
                            <p class="text-sm text-slate-500 mt-2">Age: <?php echo htmlspecialchars($party['age']); ?></p>

                            <div class="mt-6 pt-6 border-t border-slate-200 flex items-center space-x-3">
                                <?php if (!$has_voted && $voting_open): ?>
                                    <form method="post" action="vote.php" class="flex-1">
                                        <input type="hidden" name="party_id" value="<?php echo $party['party_id']; ?>">
                                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 flex items-center justify-center">
                                            <i class="fas fa-vote-yea mr-2"></i> Vote Now
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="flex-1 w-full <?php echo $is_voted_for ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500'; ?> font-semibold py-3 px-4 rounded-lg text-center flex items-center justify-center">
                                        <i class="fas <?php echo $is_voted_for ? 'fa-check-circle' : 'fa-lock'; ?> mr-2"></i>
                                        <?php if ($is_voted_for) {
                                            echo 'Voted';
                                        } else {
                                            echo $voting_not_started ? 'Not Started' : ($voting_ended ? 'Closed' : 'Locked');
                                        } ?>
                                    </div>
                                <?php endif; ?>

                                <a href="../results.php" class="flex-shrink-0 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold py-3 px-4 rounded-lg transition duration-300 <?php echo !$results_published ? 'opacity-60 pointer-events-none cursor-not-allowed' : ''; ?>" title="<?php echo $results_published ? 'View Results' : 'Results Hidden Until Voting Ends'; ?>">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php $delay_index++;
                endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-20 bg-white rounded-lg border border-dashed">
                    <div class="text-6xl text-slate-300 mb-4"><i class="fas fa-users-slash"></i></div>
                    <h3 class="text-2xl font-bold text-slate-700 mb-2">No Candidates Available</h3>
                    <p class="text-slate-500">The election has not started yet or no candidates are registered.</p>
                </div>
            <?php endif; ?>
        </div>

        <footer class="mt-20 text-center border-t border-slate-200 pt-8">
            <p class="text-slate-500">&copy; <?php echo date("Y"); ?> Digital Voting System. All Rights Reserved.</p>
            <p class="text-sm text-slate-400 mt-2">Ensuring fair and secure elections for everyone.</p>
        </footer>
    </main>

    <script>
        // Enhanced voting confirmation dialog
        document.querySelectorAll('form[action="vote.php"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Find the closest card to get party details from it
                const card = e.target.closest('.bg-white');
                const partyName = card.querySelector('h3').textContent.trim();
                const politicianName = card.querySelector('p').textContent.trim();

                const message = `🗳️ CONFIRM YOUR VOTE\n\nAre you sure you want to vote for:\n\nParty: ${partyName}\nCandidate: ${politicianName}\n\nThis action is final and cannot be undone.`;

                if (!confirm(message)) {
                    e.preventDefault(); // Stop form submission if user cancels
                } else {
                    // Provide visual feedback that the vote is being cast
                    const button = this.querySelector('button');
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Casting Vote...';
                    button.classList.add('cursor-not-allowed', 'opacity-70');
                }
            });
        });
        // Show server time (approx) using client time; for exact sync could fetch via AJAX.
        function updateSrvTime() {
            const el = document.getElementById('srvTime');
            if (!el) return;
            el.textContent = new Date().toLocaleString();
        }
        updateSrvTime();
        setInterval(updateSrvTime, 1000);
    </script>
</body>

</html>