<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'clg_ass';
$db_user = 'root';
$db_pass = '';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update parties table
$sql = "SELECT id, politician_image, party_logo FROM parties";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $politician_image = $row['politician_image'];
        $party_logo = $row['party_logo'];

        if (strpos($politician_image, '/') === false) {
            $new_politician_image = 'uploads/' . $politician_image;
            $update_sql = "UPDATE parties SET politician_image = '$new_politician_image' WHERE id = $id";
            $conn->query($update_sql);
        }

        if (strpos($party_logo, '/') === false) {
            $new_party_logo = 'uploads/' . $party_logo;
            $update_sql = "UPDATE parties SET party_logo = '$new_party_logo' WHERE id = $id";
            $conn->query($update_sql);
        }
    }
    echo "Parties table updated successfully.<br>";
} else {
    echo "No records found in parties table.<br>";
}

// Update agents table
$sql = "SELECT id, profile_picture, party_logo FROM agents";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $profile_picture = $row['profile_picture'];
        $party_logo = $row['party_logo'];

        if (strpos($profile_picture, '/') === false) {
            $new_profile_picture = 'uploads/' . $profile_picture;
            $update_sql = "UPDATE agents SET profile_picture = '$new_profile_picture' WHERE id = $id";
            $conn->query($update_sql);
        }

        if (strpos($party_logo, '/') === false) {
            $new_party_logo = 'uploads/' . $party_logo;
            $update_sql = "UPDATE agents SET party_logo = '$new_party_logo' WHERE id = $id";
            $conn->query($update_sql);
        }
    }
    echo "Agents table updated successfully.<br>";
} else {
    echo "No records found in agents table.<br>";
}

$conn->close();
?>
