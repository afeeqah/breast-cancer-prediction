<?php
include('server.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM form WHERE id='$id'";
    if (mysqli_query($db, $query)) {
        header("Location: adminManageHistory.php");
    } else {
        echo "Error: " . mysqli_error($db);
    }
} else {
    echo "Invalid request.";
}
