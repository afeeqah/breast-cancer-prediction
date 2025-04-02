<?php
session_start();
include('server.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["dataset"])) {
    // File upload path
    $targetDir = "uploads/";
    $fileName = basename($_FILES["dataset"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Check if file is a CSV
    if($fileType != "csv") {
        echo "Sorry, only CSV files are allowed.";
    } else {
        // Get description from form
        $description = $_POST['description'];

        // Upload file to server
        if(move_uploaded_file($_FILES["dataset"]["tmp_name"], $targetFilePath)) {
            // Insert file details into database
            $query = "INSERT INTO datasets (name, description, file_path, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bind_param("sssi", $fileName, $description, $targetFilePath, $_SESSION['admin_id']);
            if($stmt->execute()) {
                // Redirect back to adminManageDataset.php immediately
                header('Location: adminManageDataset.php');
                exit();
            } else {
                echo "There was an error uploading your file.";
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
} else {
    // Redirect to adminManageDataset.php if no dataset is uploaded
    header('Location: adminManageDataset.php');
    exit();
}
