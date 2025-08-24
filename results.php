<?php
session_start();
require_once __DIR__ . '/db.php'; // Provides $pdo

function safe($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$total_votes = 0;
$rows = [];
$winner = null;
$fetchError = '';
// Voting schedule gating: voters see results only after end OR result_status=1; admins always.
$can_view_results = true; // default true if no schedule exists.
try {
    $sch = $pdo->query('SELECT * FROM voting_setting ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($sch) {
        $start_ts = strtotime($sch['start_date'] . ' ' . $sch['start_time']);
        $end_ts   = strtotime($sch['end_date'] . ' ' . $sch['end_time']);
        $result_status = (int)$sch['result_status'];
        $now = time();
        $is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
        if (!$is_admin) {
            $can_view_results = ($now > $end_ts) || $result_status === 1; // after end OR manually published
        }
    }
} catch (Throwable $e) {
    // ignore gating failure, fallback to allowing
}
if ($can_view_results) {
    try {
        $total_votes = (int)$pdo->query('SELECT COUNT(*) FROM votes')->fetchColumn();
        $stmt = $pdo->query('SELECT p.participates_id, p.party_name, p.party_logo, p.politician_name, COUNT(v.vote_id) AS total_votes
                             FROM parties p
                             LEFT JOIN votes v ON v.party_id = p.participates_id
                             GROUP BY p.participates_id, p.party_name, p.party_logo, p.politician_name
                             HAVING total_votes > 0 OR p.participates_id IS NOT NULL
                             ORDER BY total_votes DESC, p.party_name ASC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $winner = $rows[0];
        }
    } catch (Throwable $e) {
        $fetchError = 'Unable to load results right now.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Voting Results - Election Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#7c3aed',
                        success: '#059669',
                        warning: '#d97706',
                        danger: '#dc2626',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.8s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'count-up': 'countUp 2s ease-out',
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
                transform: translateY(30px);
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

        @keyframes countUp {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .vote-bar {
            transition: width 2s ease-in-out;
        }

        .winner-glow {
            box-shadow: 0 0 30px rgba(34, 197, 94, 0.4);
        }

        .bg-grid-pattern {
            background-image: url("data:image/svg+xml,%3csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3e%3cg fill='none' fill-rule='evenodd'%3e%3cg fill='%23f1f5f9' fill-opacity='0.1'%3e%3ccircle cx='30' cy='30' r='2'/%3e%3c/g%3e%3c/g%3e%3c/svg%3e");
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 min-h-screen">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-grid-pattern opacity-20"></div>

    <!-- Floating particles -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-bounce-gentle"></div>
        <div class="absolute top-3/4 right-1/4 w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-bounce-gentle" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-1/4 left-1/3 w-96 h-96 bg-green-400 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-bounce-gentle" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-12 animate-fade-in">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mb-6 shadow-2xl">
                <i class="fas fa-vote-yea text-white text-3xl"></i>
            </div>
            <h1 class="text-5xl font-bold text-white mb-4">
                Live Election Results
            </h1>
            <p class="text-blue-200 text-xl mb-6">
                <?php if ($can_view_results) {
                    echo 'Real-time voting statistics and analysis';
                } else {
                    echo 'Results will be available once voting ends.';
                } ?>
            </p>

            <!-- Live indicator -->
            <div class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-full text-sm font-semibold">
                <div class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></div>
                LIVE RESULTS
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid md:grid-cols-4 gap-6 mb-12">
            <!-- Total Votes -->
            <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center animate-slide-up">
                <div class="text-blue-300 text-2xl mb-2">
                    <i class="fas fa-users"></i>
                </div>
                <div class="text-3xl font-bold text-white mb-1 animate-count-up"><?= $can_view_results ? number_format($total_votes) : '—' ?></div>
                <div class="text-blue-200 text-sm">Total Votes Cast</div>
            </div>

            <!-- Total Parties -->
            <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center animate-slide-up" style="animation-delay: 0.1s;">
                <div class="text-purple-300 text-2xl mb-2">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="text-3xl font-bold text-white mb-1"><?= $can_view_results ? count($rows) : '—' ?></div>
                <div class="text-purple-200 text-sm">Participating Parties</div>
            </div>

            <!-- Winner -->
            <?php if ($can_view_results && $winner && $total_votes > 0): ?>
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="text-green-300 text-2xl mb-2">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="text-xl font-bold text-white mb-1"><?= safe($winner['party_name']) ?></div>
                    <div class="text-green-200 text-sm">Leading Party</div>
                </div>

                <!-- Leading Percentage -->
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center animate-slide-up" style="animation-delay: 0.3s;">
                    <div class="text-yellow-300 text-2xl mb-2">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="text-3xl font-bold text-white mb-1">
                        <?= $total_votes > 0 ? round(($winner['total_votes'] / $total_votes) * 100, 1) : 0 ?>%
                    </div>
                    <div class="text-yellow-200 text-sm">Leading Margin</div>
                </div>
            <?php else: ?>
                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="text-gray-300 text-2xl mb-2">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="text-xl font-bold text-white mb-1">Awaiting</div>
                    <div class="text-gray-200 text-sm">Results</div>
                </div>

                <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center animate-slide-up" style="animation-delay: 0.3s;">
                    <div class="text-gray-300 text-2xl mb-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="text-xl font-bold text-white mb-1">0%</div>
                    <div class="text-gray-200 text-sm">No Votes Yet</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Results Table -->
        <div class="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl overflow-hidden shadow-2xl animate-slide-up" style="animation-delay: 0.4s;">
            <div class="bg-gradient-to-r from-blue-600/50 to-purple-600/50 px-8 py-6 border-b border-white/20">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Detailed Results
                </h2>
            </div>

            <div class="overflow-x-auto">
                <?php if (!$can_view_results): ?>
                    <div class="p-12 text-center text-blue-200">Results are not yet available. Please check back after the voting period.</div>
                <?php elseif ($fetchError): ?>
                    <div class="p-8 text-center text-red-300 text-sm"><?= safe($fetchError) ?></div>
                <?php elseif ($rows): ?>
                    <table class="w-full">
                        <thead class="bg-black/20">
                            <tr class="text-left">
                                <th class="px-8 py-4 text-blue-200 font-semibold uppercase tracking-wider text-sm">Rank</th>
                                <th class="px-8 py-4 text-blue-200 font-semibold uppercase tracking-wider text-sm">Party</th>
                                <th class="px-8 py-4 text-blue-200 font-semibold uppercase tracking-wider text-sm">Candidate</th>
                                <th class="px-8 py-4 text-blue-200 font-semibold uppercase tracking-wider text-sm">Votes</th>
                                <th class="px-8 py-4 text-blue-200 font-semibold uppercase tracking-wider text-sm">Percentage</th>
                                <th class="px-8 py-4 text-blue-200 font-semibold uppercase tracking-wider text-sm">Progress</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <?php
                            $rank = 1;
                            $colors = ['bg-gradient-to-r from-green-500 to-emerald-500', 'bg-gradient-to-r from-blue-500 to-cyan-500', 'bg-gradient-to-r from-purple-500 to-violet-500', 'bg-gradient-to-r from-orange-500 to-amber-500', 'bg-gradient-to-r from-pink-500 to-rose-500'];
                            foreach ($rows as $row):
                                $percentage = $total_votes > 0 ? round(($row['total_votes'] / $total_votes) * 100, 2) : 0;
                                $isWinner = ($rank === 1 && $total_votes > 0);
                            ?>
                                <tr class="hover:bg-white/5 transition duration-300 <?= $isWinner ? 'winner-glow bg-green-500/5' : '' ?>">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center">
                                            <?php if ($rank === 1 && $total_votes > 0): ?>
                                                <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-yellow-900 px-3 py-1 rounded-full text-sm font-bold flex items-center">
                                                    <i class="fas fa-crown mr-1"></i>
                                                    #<?= $rank ?>
                                                </div>
                                            <?php elseif ($rank === 2): ?>
                                                <div class="bg-gradient-to-r from-gray-300 to-gray-400 text-gray-800 px-3 py-1 rounded-full text-sm font-bold">
                                                    #<?= $rank ?>
                                                </div>
                                            <?php elseif ($rank === 3): ?>
                                                <div class="bg-gradient-to-r from-orange-400 to-orange-500 text-orange-900 px-3 py-1 rounded-full text-sm font-bold">
                                                    #<?= $rank ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-bold">
                                                    #<?= $rank ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 rounded-full overflow-hidden mr-4 border-2 border-white/20">
                                                <img src="<?= safe($row['party_logo']) ?>"
                                                    alt="<?= safe($row['party_name']) ?>"
                                                    class="w-full h-full object-cover">
                                            </div>
                                            <div class="text-white font-semibold text-lg">
                                                <?= safe($row['party_name']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-blue-200 font-medium">
                                            <?= safe($row['politician_name']) ?>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-white font-bold text-xl">
                                            <?= number_format($row['total_votes']) ?>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-2xl font-bold text-white">
                                            <?= $percentage ?>%
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden">
                                            <div class="vote-bar h-full rounded-full <?= $colors[($rank - 1) % count($colors)] ?>"
                                                style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <div class="text-xs text-blue-200 mt-1">
                                            <?= $percentage ?>% of total votes
                                        </div>
                                    </td>
                                </tr>
                            <?php $rank++;
                            endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div class="text-6xl text-white/20 mb-4">
                            <i class="fas fa-ballot"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">No Votes Cast Yet</h3>
                        <p class="text-blue-200">Voting results will appear here once votes are submitted.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 animate-fade-in">
            <div class="inline-flex items-center px-6 py-3 bg-white/10 backdrop-blur-lg rounded-full text-white">
                <i class="fas fa-sync-alt mr-2 animate-spin"></i>
                Results update automatically
            </div>
            <p class="text-blue-200 text-sm mt-4">
                Last updated: <span id="lastUpdated"></span>
            </p>
        </div>
    </div>

    <script>
        // Update timestamp
        function updateTimestamp() {
            const now = new Date();
            document.getElementById('lastUpdated').textContent = now.toLocaleString();
        }

        updateTimestamp();
        setInterval(updateTimestamp, 60000); // Update every minute

        // Animate vote bars on load
        document.addEventListener('DOMContentLoaded', function() {
            const voteBars = document.querySelectorAll('.vote-bar');
            setTimeout(() => {
                voteBars.forEach((bar, index) => {
                    setTimeout(() => {
                        bar.style.width = bar.style.width || '0%';
                    }, index * 200);
                });
            }, 500);
        });

        // Auto-refresh results every 30 seconds
        <?php if ($can_view_results): ?>
            setInterval(() => {
                location.reload();
            }, 30000);
        <?php endif; ?>

        // Add counter animation for numbers
        document.addEventListener('DOMContentLoaded', function() {
            const totalVotes = <?= (int)$total_votes ?>;
            const counter = document.querySelector('.animate-count-up');

            if (counter && totalVotes > 0) {
                let current = 0;
                const increment = totalVotes / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= totalVotes) {
                        current = totalVotes;
                        clearInterval(timer);
                    }
                    counter.textContent = new Intl.NumberFormat().format(Math.floor(current));
                }, 40);
            }
        });
    </script>
</body>

</html>