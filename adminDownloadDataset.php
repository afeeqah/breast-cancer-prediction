<?php
// adminDownloadDataset.php

// Check if the file parameter is set
if(isset($_GET['file'])) {
    // Get the file path from the URL parameter
    $file = $_GET['file'];

    // Check if the file exists
    if(file_exists($file)) {
        // Set headers to force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header('Content-Transfer-Encoding: binary');

        // Read the file and output its contents
        readfile($file);
        exit;
    } else {
        // If the file does not exist, display an error message
        echo "File not found.";
    }
} else {
    // If the file parameter is not set, display an error message
    echo "Invalid request.";
}
