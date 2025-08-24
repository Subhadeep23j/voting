<?php
// Simple landing page: only introduction, no stats or party details.
session_start();
$is_user_logged_in  = !empty($_SESSION['logged_in']);
$is_admin_logged_in = !empty($_SESSION['admin_logged_in']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>College Voting Portal</title>
    <meta name="description" content="Official College Voting Portal – secure, transparent and fair digital elections for students." />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6',
                        accent: '#06b6d4'
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(14px);
        }

        .gradient-text {
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            color: transparent;
        }

        .hero-bg {
            background: radial-gradient(circle at 30% 30%, rgba(99, 102, 241, 0.25), transparent 60%), radial-gradient(circle at 70% 60%, rgba(139, 92, 246, 0.25), transparent 60%);
        }

        .logo-fallback {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
        }

        @media (prefers-reduced-motion: reduce) {

            .animate-pulse,
            .animate-bounce {
                animation: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-violet-50 text-slate-800 hero-bg">
    <header class="relative z-10">
        <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="logo-fallback shadow-lg shadow-indigo-200/40">CV</div>
                <span class="font-extrabold text-lg gradient-text tracking-tight">College Voting Portal</span>
            </div>
            <div class="hidden md:flex items-center gap-4 font-medium">
                <a href="#about" class="px-3 py-2 rounded-lg hover:bg-indigo-100 text-slate-700">About</a>
                <a href="#features" class="px-3 py-2 rounded-lg hover:bg-indigo-100 text-slate-700">Features</a>
                <a href="#how" class="px-3 py-2 rounded-lg hover:bg-indigo-100 text-slate-700">How It Works</a>
                <?php if ($is_user_logged_in): ?>
                    <a href="voter/dashboard.php" class="px-4 py-2 rounded-lg bg-primary text-white font-semibold shadow hover:-translate-y-0.5 transition">Dashboard</a>
                <?php else: ?>
                    <a href="voter/login.php" class="px-4 py-2 rounded-lg bg-primary text-white font-semibold shadow hover:-translate-y-0.5 transition">Voter Login</a>
                <?php endif; ?>
                <?php if ($is_admin_logged_in): ?>
                    <a href="admin/admin_dashboard.php" class="px-4 py-2 rounded-lg border border-indigo-300 font-semibold hover:bg-white/60">Admin Panel</a>
                <?php else: ?>
                    <a href="admin/admin_login.php" class="px-4 py-2 rounded-lg border border-indigo-300 font-semibold hover:bg-white/60">Admin Login</a>
                <?php endif; ?>
            </div>
            <button id="mobileBtn" class="md:hidden p-2 rounded-lg bg-white shadow focus:outline-none" aria-label="Open Menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </nav>
        <div id="mobileMenu" class="md:hidden hidden px-6 pb-4 space-y-2">
            <a href="#about" class="block px-4 py-3 rounded-lg bg-white shadow">About</a>
            <a href="#features" class="block px-4 py-3 rounded-lg bg-white shadow">Features</a>
            <a href="#how" class="block px-4 py-3 rounded-lg bg-white shadow">How It Works</a>
            <?php if ($is_user_logged_in): ?>
                <a href="voter/dashboard.php" class="block px-4 py-3 rounded-lg bg-primary text-white shadow">Dashboard</a>
            <?php else: ?>
                <a href="voter/login.php" class="block px-4 py-3 rounded-lg bg-primary text-white shadow">Voter Login</a>
                <a href="voter/register.php" class="block px-4 py-3 rounded-lg bg-white shadow border">Register</a>
            <?php endif; ?>
            <?php if ($is_admin_logged_in): ?>
                <a href="admin/admin_dashboard.php" class="block px-4 py-3 rounded-lg bg-white shadow border">Admin Panel</a>
            <?php else: ?>
                <a href="admin/admin_login.php" class="block px-4 py-3 rounded-lg bg-white shadow border">Admin Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="relative z-10">
        <!-- Hero -->
        <section class="max-w-7xl mx-auto px-6 pt-10 md:pt-16 pb-12" id="about">
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-6">
                        Empowering <span class="gradient-text">Student Democracy</span><br /> Secure • Transparent • Fair
                    </h1>
                    <p class="text-lg text-slate-600 mb-8 max-w-xl">Welcome to your College Voting Portal – a modern, intuitive and secure platform built to make every student vote count. Participate in campus elections with confidence and ease.</p>
                    <div class="flex flex-wrap gap-4">
                        <?php if (!$is_user_logged_in): ?>
                            <a href="voter/register.php" class="px-6 py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow hover:shadow-lg hover:-translate-y-0.5 transition">Get Started</a>
                            <a href="voter/login.php" class="px-6 py-3 rounded-xl font-semibold border border-indigo-300 bg-white/80 hover:bg-white transition">Login</a>
                        <?php else: ?>
                            <a href="voter/dashboard.php" class="px-6 py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow hover:shadow-lg hover:-translate-y-0.5 transition">Go to Dashboard</a>
                        <?php endif; ?>
                        <a href="#features" class="px-6 py-3 rounded-xl font-semibold border border-indigo-300 bg-white/80 hover:bg-white transition">Explore Features</a>
                    </div>
                    <div class="mt-10 flex items-center gap-6">
                        <div class="flex -space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 border-2 border-white"></div>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-400 to-violet-600 border-2 border-white"></div>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-600 border-2 border-white"></div>
                        </div>
                        <p class="text-sm text-slate-600"><span class="font-semibold">Trusted</span> by students across departments</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-indigo-300/30 to-violet-300/30 rounded-3xl blur-2xl"></div>
                    <div class="relative glass rounded-3xl p-8 shadow-xl border border-white/40 space-y-6">
                        <h3 class="font-bold text-xl">Why This Portal?</h3>
                        <ul class="space-y-4 text-sm text-slate-600">
                            <li class="flex gap-3"><span class="text-primary">✓</span><span>Secure authentication to protect each ballot.</span></li>
                            <li class="flex gap-3"><span class="text-primary">✓</span><span>One‑vote integrity ensuring fairness across the campus.</span></li>
                            <li class="flex gap-3"><span class="text-primary">✓</span><span>Simple, mobile‑friendly interface for quick participation.</span></li>
                            <li class="flex gap-3"><span class="text-primary">✓</span><span>Transparent process fostering trust and engagement.</span></li>
                        </ul>
                        <div class="pt-2">
                            <a href="#features" class="inline-block px-5 py-2 rounded-lg bg-gradient-to-r from-primary to-secondary text-white text-sm font-semibold shadow hover:-translate-y-0.5 transition">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Features -->
        <section class="bg-white/60 border-t border-indigo-100/50" id="features">
            <div class="max-w-7xl mx-auto px-6 py-20">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-4">Platform Features</h2>
                    <p class="text-slate-600">Designed for clarity, fairness and seamless participation in college elections.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold mb-5">1</div>
                        <h3 class="font-semibold text-lg mb-3">Secure Access</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Account‑based login with protected voting flow ensuring each student casts exactly one ballot.</p>
                    </div>
                    <div class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold mb-5">2</div>
                        <h3 class="font-semibold text-lg mb-3">Simple Experience</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Clean, mobile‑responsive interface so you can register and participate from any device.</p>
                    </div>
                    <div class="group bg-white rounded-2xl p-8 border border-slate-100 shadow-sm hover:shadow-lg transition">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold mb-5">3</div>
                        <h3 class="font-semibold text-lg mb-3">Transparent Process</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Fair, auditable flow encouraging trust and active engagement across the campus community.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="max-w-7xl mx-auto px-6 py-20" id="how">
            <div class="grid md:grid-cols-2 gap-14 items-start">
                <div>
                    <h2 class="text-3xl font-extrabold mb-6 tracking-tight">How It Works</h2>
                    <ol class="space-y-5 text-slate-700 list-decimal list-inside">
                        <li><span class="font-semibold">Create your account</span> using your valid student credentials.</li>
                        <li><span class="font-semibold">Verify & log in</span> to access the election dashboard when polls open.</li>
                        <li><span class="font-semibold">Cast your vote</span> – the system locks in exactly one secure ballot.</li>
                        <li><span class="font-semibold">Return later</span> to view aggregated outcomes once results are published.</li>
                    </ol>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <?php if (!$is_user_logged_in): ?>
                            <a href="voter/register.php" class="px-6 py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow hover:shadow-lg hover:-translate-y-0.5 transition">Register Now</a>
                            <a href="voter/login.php" class="px-6 py-3 rounded-xl font-semibold border border-indigo-300 bg-white/80 hover:bg-white transition">Login</a>
                        <?php else: ?>
                            <a href="voter/dashboard.php" class="px-6 py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow hover:shadow-lg hover:-translate-y-0.5 transition">Open Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-indigo-300/30 to-violet-300/30 rounded-3xl blur-2xl"></div>
                    <div class="relative glass rounded-3xl p-10 shadow-xl border border-white/40">
                        <h3 class="font-semibold text-lg mb-4">Core Principles</h3>
                        <div class="space-y-4 text-sm text-slate-600">
                            <p><span class="font-semibold text-slate-800">Integrity:</span> Each vote is counted once – no duplicates, no manipulation.</p>
                            <p><span class="font-semibold text-slate-800">Accessibility:</span> Works seamlessly across modern browsers & devices.</p>
                            <p><span class="font-semibold text-slate-800">Privacy:</span> Individual selections remain confidential within the system.</p>
                            <p><span class="font-semibold text-slate-800">Engagement:</span> Encourages a culture of participation & leadership.</p>
                        </div>
                        <div class="mt-8">
                            <a href="#about" class="inline-block text-sm font-semibold text-primary hover:underline">Back to top ↑</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to action -->
        <section class="max-w-7xl mx-auto px-6 py-20">
            <div class="glass border border-white/40 rounded-3xl p-10 md:p-14 shadow-xl text-center relative overflow-hidden">
                <div class="absolute -top-20 -left-20 w-72 h-72 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-gradient-to-br from-cyan-200/40 to-indigo-200/40 rounded-full blur-3xl"></div>
                <h2 class="text-3xl md:text-4xl font-extrabold mb-6 tracking-tight">Get Involved Today</h2>
                <p class="max-w-2xl mx-auto text-slate-600 mb-10">Be part of shaping campus leadership. Create your account and be ready when polls open. Your voice matters – make it count.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <?php if (!$is_user_logged_in): ?>
                        <a href="voter/register.php" class="px-8 py-4 rounded-2xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow hover:shadow-lg hover:-translate-y-0.5 transition">Create Account</a>
                        <a href="voter/login.php" class="px-8 py-4 rounded-2xl font-semibold bg-white/70 border border-indigo-200 hover:bg-white transition">Login</a>
                    <?php else: ?>
                        <a href="voter/dashboard.php" class="px-8 py-4 rounded-2xl text-white font-semibold bg-gradient-to-r from-primary to-secondary shadow hover:shadow-lg hover:-translate-y-0.5 transition">Go to Dashboard</a>
                    <?php endif; ?>
                    <a href="#features" class="px-8 py-4 rounded-2xl font-semibold bg-white/70 border border-indigo-200 hover:bg-white transition">Features</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t bg-white/70 backdrop-blur mt-10">
        <div class="max-w-7xl mx-auto px-6 py-8 text-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-slate-500">&copy; <?= date('Y') ?> College Voting Portal. All rights reserved.</p>
            <div class="flex gap-6 text-slate-500">
                <a href="voter/register.php" class="hover:text-slate-800">Register</a>
                <a href="voter/login.php" class="hover:text-slate-800">Login</a>
                <a href="admin/admin_login.php" class="hover:text-slate-800">Admin</a>
                <a href="#features" class="hover:text-slate-800">Features</a>
            </div>
        </div>
    </footer>

    <script>
        const btn = document.getElementById('mobileBtn');
        const menu = document.getElementById('mobileMenu');
        btn?.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    </script>
</body>

</html>