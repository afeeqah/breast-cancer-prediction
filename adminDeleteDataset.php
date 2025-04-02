<?php
session_start();
include('server.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    die("Access denied. Admins only.");
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($db, $_GET['id']); // Sanitize input

    // Fetch the file path to delete the file from the server
    $query = "SELECT file_path FROM datasets WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataset = $result->fetch_assoc();
    $stmt->close();

    if ($dataset) {
        $filePath = $dataset['file_path'];

        // Delete the file from the server
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                echo "File deleted successfully.<br>";
            } else {
                echo "Error deleting file.<br>";
            }
        } else {
            echo "File not found.<br>";
        }

        // Delete the dataset record from the database
        $query = "DELETE FROM datasets WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Dataset deleted successfully from the database.<br>";
        } else {
            echo "Error deleting dataset from the database.<br>";
        }
        $stmt->close();
    } else {
        echo "Dataset not found.<br>";
    }
} else {
    echo "No dataset ID provided.<br>";
}

$db->close();

// Redirect back to the dataset management page
header('Location: adminManageDataset.php');
exit();
