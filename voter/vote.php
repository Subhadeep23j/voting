<?php
session_start();

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

// Handle vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['party_id'])) {
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
?>
