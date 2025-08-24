<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// DB Connection
$conn = new mysqli("localhost", "root", "", "clg_ass");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine if voting window is open
$schedule_conn = $conn; // reuse mysqli
$schedule_res = $schedule_conn->query("SELECT * FROM voting_setting ORDER BY id DESC LIMIT 1");
$voting_open = true; // default allow if not configured
$voting_has_ended = false;
if ($schedule_res && $schedule_res->num_rows === 1) {
    $sched = $schedule_res->fetch_assoc();
    $start_ts = strtotime($sched['start_date'] . ' ' . $sched['start_time']);
    $end_ts   = strtotime($sched['end_date'] . ' ' . $sched['end_time']);
    $force_open = !empty($sched['force_open']);
    $force_closed = !empty($sched['force_closed']);
    $now = time();
    if ($force_closed) {
        $voting_open = false;
        $voting_has_ended = true;
    } elseif ($force_open) {
        $voting_open = true;
        $voting_has_ended = false;
    } elseif ($start_ts && $end_ts) {
        if ($now < $start_ts) {
            $voting_open = false;
        }
        if ($now > $end_ts) {
            $voting_open = false;
            $voting_has_ended = true;
        }
    }
}

// Handle vote (only if window open)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['party_id'])) {
    if (!$voting_open) {
        if ($voting_has_ended) {
            echo "<script>alert('Voting period has ended.'); window.location='dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Voting has not started yet.'); window.location='dashboard.php';</script>";
            exit();
        }
    }
    $party_id = intval($_POST['party_id']);

    // ✅ Check if already voted
    $stmt = $conn->prepare("SELECT vote_id FROM votes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('⚠️ You have already voted!'); window.location='dashboard.php';</script>";
        exit();
    }

    // ✅ Insert vote
    $stmt = $conn->prepare("INSERT INTO votes (user_id, party_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $party_id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Vote cast successfully!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('❌ Error casting vote.'); window.location='dashboard.php';</script>";
    }
}
