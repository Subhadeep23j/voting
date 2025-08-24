<?php
session_start();
// Restrict to admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Set timezone (shared config)
require_once __DIR__ . '/../config.php';

// Simple PDO connection (reuse style from dashboard)
$db_host = 'localhost';
$db_name = 'clg_ass';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    die('Database connection failed');
}

// --- Ensure table & new columns exist (auto-migrate) ---
try {
    $tableExists = $pdo->query("SHOW TABLES LIKE 'voting_setting'")->fetchColumn();
    if (!$tableExists) {
        $pdo->exec("CREATE TABLE voting_setting (
            id INT AUTO_INCREMENT PRIMARY KEY,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            result_status TINYINT(1) NOT NULL DEFAULT 0,
            force_open TINYINT(1) NOT NULL DEFAULT 0,
            force_closed TINYINT(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } else {
        // add missing columns silently
        $cols = $pdo->query("SHOW COLUMNS FROM voting_setting")->fetchAll(PDO::FETCH_COLUMN);
        $missing = [];
        foreach (['result_status', 'force_open', 'force_closed'] as $c) {
            if (!in_array($c, $cols, true)) {
                $missing[] = $c;
            }
        }
        if ($missing) {
            if (in_array('result_status', $missing, true)) {
                $pdo->exec("ALTER TABLE voting_setting ADD COLUMN result_status TINYINT(1) NOT NULL DEFAULT 0 AFTER end_time");
            }
            if (in_array('force_open', $missing, true)) {
                $pdo->exec("ALTER TABLE voting_setting ADD COLUMN force_open TINYINT(1) NOT NULL DEFAULT 0 AFTER result_status");
            }
            if (in_array('force_closed', $missing, true)) {
                $pdo->exec("ALTER TABLE voting_setting ADD COLUMN force_closed TINYINT(1) NOT NULL DEFAULT 0 AFTER force_open");
            }
        }
    }
} catch (Throwable $e) {
    // If migration fails we continue; form will show error on save
}

// Fetch current schedule (latest row)
$schedule = $pdo->query('SELECT * FROM voting_setting ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = trim($_POST['start_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_date   = trim($_POST['end_date'] ?? '');
    $end_time   = trim($_POST['end_time'] ?? '');
    $publish_results = isset($_POST['publish_results']) ? 1 : 0; // allow early viewing
    $force_open      = isset($_POST['force_open']) ? 1 : 0;      // ignore start time gating
    $force_closed    = isset($_POST['force_closed']) ? 1 : 0;    // treat as finished early

    // Basic validation
    if (!$start_date || !$start_time || !$end_date || !$end_time) {
        $error = 'All fields are required.';
    } else {
        $start_ts = strtotime($start_date . ' ' . $start_time);
        $end_ts   = strtotime($end_date . ' ' . $end_time);
        if ($start_ts === false || $end_ts === false) {
            $error = 'Invalid date/time format.';
        } elseif ($start_ts >= $end_ts) {
            $error = 'End date/time must be after start date/time.';
        } else {
            try {
                // Determine available columns (backwards compatibility)
                $cols = $pdo->query("SHOW COLUMNS FROM voting_setting")->fetchAll(PDO::FETCH_COLUMN);
                $hasForce = in_array('force_open', $cols, true) && in_array('force_closed', $cols, true);
                if ($schedule) {
                    if ($hasForce) {
                        $stmt = $pdo->prepare('UPDATE voting_setting SET start_date=?, end_date=?, start_time=?, end_time=?, result_status=?, force_open=?, force_closed=? WHERE id=?');
                        $stmt->execute([$start_date, $end_date, $start_time, $end_time, $publish_results, $force_open, $force_closed, $schedule['id']]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE voting_setting SET start_date=?, end_date=?, start_time=?, end_time=?, result_status=? WHERE id=?');
                        $stmt->execute([$start_date, $end_date, $start_time, $end_time, $publish_results, $schedule['id']]);
                    }
                } else {
                    if ($hasForce) {
                        $stmt = $pdo->prepare('INSERT INTO voting_setting (start_date, end_date, start_time, end_time, result_status, force_open, force_closed) VALUES (?,?,?,?,?,?,?)');
                        $stmt->execute([$start_date, $end_date, $start_time, $end_time, $publish_results, $force_open, $force_closed]);
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO voting_setting (start_date, end_date, start_time, end_time, result_status) VALUES (?,?,?,?,?)');
                        $stmt->execute([$start_date, $end_date, $start_time, $end_time, $publish_results]);
                    }
                }
                $message = 'Voting schedule saved successfully.';
                $schedule = $pdo->query('SELECT * FROM voting_setting ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $error = 'Unable to save schedule.'; // generic
                // Uncomment line below temporarily if you need to see the exact DB error while debugging
                // $error .= ' (' . htmlspecialchars($e->getMessage()) . ')';
            }
        }
    }
}

// Derive helpful status
$now = time();
$status_label = 'Not Scheduled';
if ($schedule) {
    $start_ts = strtotime($schedule['start_date'] . ' ' . $schedule['start_time']);
    $end_ts   = strtotime($schedule['end_date'] . ' ' . $schedule['end_time']);
    $force_open = !empty($schedule['force_open']);
    $force_closed = !empty($schedule['force_closed']);
    if ($force_closed) {
        $status_label = 'Voting ENDED (Forced)';
    } elseif ($force_open) {
        $status_label = 'Voting OPEN (Forced)';
    } elseif ($now < $start_ts) {
        $status_label = 'Scheduled (Not Started)';
    } elseif ($now >= $start_ts && $now <= $end_ts) {
        $status_label = 'Voting OPEN';
    } elseif ($now > $end_ts) {
        $status_label = 'Voting ENDED';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Voting Schedule</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: system-ui, Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.35rem;
            color: #334155;
        }

        input[type=date],
        input[type=time] {
            width: 100%;
            padding: 0.75rem 0.9rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            background: #f8fafc;
        }

        .grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.4rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 8px 20px -6px rgba(99, 102, 241, 0.4);
            transition: .3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -6px rgba(99, 102, 241, 0.55);
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #e2e8f0;
            color: #334155;
            letter-spacing: .5px;
        }

        .messages {
            margin-bottom: 1rem;
        }

        .alert {
            padding: 0.9rem 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 0.6rem;
        }

        .alert.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #f87171;
        }

        form .actions {
            margin-top: 1.5rem;
        }

        .switch {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 1rem;
        }

        .switch input {
            width: 18px;
            height: 18px;
        }

        a.back {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 1rem;
        }

        a.back:hover {
            color: #1e293b;
        }

        .meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <a class="back" href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        <h1><i class="fas fa-calendar-check"></i> Voting Schedule</h1>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <strong>Status:</strong> <span class="status-badge"><?= $status_label ?></span>
                </div>
                <?php if ($schedule): ?>
                    <div style="font-size:0.85rem;color:#475569;">
                        Current Window: <strong><?= htmlspecialchars($schedule['start_date'] . ' ' . $schedule['start_time']) ?></strong> → <strong><?= htmlspecialchars($schedule['end_date'] . ' ' . $schedule['end_time']) ?></strong>
                    </div>
                <?php else: ?>
                    <div style="font-size:0.85rem;color:#475569;">No schedule set.</div>
                <?php endif; ?>
            </div>
            <div class="messages">
                <?php if ($message): ?><div class="alert success"><i class="fas fa-check-circle"></i> <?= $message ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert error"><i class="fas fa-triangle-exclamation"></i> <?= $error ?></div><?php endif; ?>
            </div>
            <form method="post" novalidate>
                <div class="grid">
                    <div>
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($schedule['start_date'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" value="<?= htmlspecialchars($schedule['start_time'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($schedule['end_date'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" value="<?= htmlspecialchars($schedule['end_time'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="switch">
                    <input type="checkbox" id="publish_results" name="publish_results" value="1" <?= isset($schedule['result_status']) && $schedule['result_status'] ? 'checked' : '' ?>>
                    <label for="publish_results" style="margin:0; font-weight:500;">Publish results early (before voting ends)</label>
                </div>
                <div class="switch">
                    <input type="checkbox" id="force_open" name="force_open" value="1" <?= isset($schedule['force_open']) && $schedule['force_open'] ? 'checked' : '' ?>>
                    <label for="force_open" style="margin:0; font-weight:500;">Force voting OPEN (ignore start time)</label>
                </div>
                <div class="switch">
                    <input type="checkbox" id="force_closed" name="force_closed" value="1" <?= isset($schedule['force_closed']) && $schedule['force_closed'] ? 'checked' : '' ?>>
                    <label for="force_closed" style="margin:0; font-weight:500;">Force voting CLOSED now</label>
                </div>
                <div class="meta">Times use the server's timezone (<?= date_default_timezone_get() ?>). Ensure you align with this when setting schedule.</div>
                <div class="actions">
                    <button class="btn" type="submit"><i class="fas fa-save"></i> Save Schedule</button>
                </div>
            </form>
        </div>
        <p style="font-size:0.75rem;color:#94a3b8;">Once the end time has passed, voters can only view the results page. Admin can always view live results regardless of schedule.</p>
    </div>
</body>

</html>
<?php /* End of file */ ?>