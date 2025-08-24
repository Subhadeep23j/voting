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

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $agent_name = $_POST['agent_name'];
    $agent_address = $_POST['agent_address'];

    // Fetch existing agent data
    $sql = "SELECT profile_picture, party_logo FROM agents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agent = $result->fetch_assoc();
    
    $profile_picture_db = $agent['profile_picture'];
    $party_logo_db = $agent['party_logo'];

    $upload_dir_fs = '../uploads/';
    $upload_dir_db = 'uploads/';

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture_name = "profile_" . uniqid() . "." . pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $upload_dir_fs . $profile_picture_name)) {
            $profile_picture_db = $upload_dir_db . $profile_picture_name;
        }
    }

    // Handle party logo upload
    if (isset($_FILES['party_logo']) && $_FILES['party_logo']['error'] == 0) {
        $party_logo_name = "party_" . uniqid() . "." . pathinfo($_FILES["party_logo"]["name"], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES["party_logo"]["tmp_name"], $upload_dir_fs . $party_logo_name)) {
            $party_logo_db = $upload_dir_db . $party_logo_name;
        }
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE agents SET agent_name = ?, agent_address = ?, profile_picture = ?, party_logo = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $agent_name, $agent_address, $profile_picture_db, $party_logo_db, $id);

    if ($stmt->execute()) {
        header("Location: view_agent.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>