<?php
include('server.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datasetName = $_POST['dataset_name'];
    $username = $_POST['user_username'];
    $datasetPath = 'uploads/' . basename($_POST['dataset_path']);  // Ensure correct path
    $targetFeature = $_POST['target_feature'];
    $selectedFeatures = $_POST['selected_features'];
    $accuracy = $_POST['accuracy'];
    $precision = $_POST['precision'];
    $recall = $_POST['recall'];
    $f1Score = $_POST['f1_score'];

    $query = "INSERT INTO uploaded_datasets (dataset_name, user_username, dataset_path, target_feature, selected_features, accuracy, precision, recall, f1_score, created_at) 
              VALUES ('$datasetName', '$username', '$datasetPath', '$targetFeature', '$selectedFeatures', '$accuracy', '$precision', '$recall', '$f1Score', NOW())";
    
    if (mysqli_query($db, $query)) {
        $dataset_id = mysqli_insert_id($db);
        echo json_encode(['dataset_id' => $dataset_id]);
    } else {
        echo json_encode(['error' => mysqli_error($db)]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

