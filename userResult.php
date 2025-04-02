<?php
include('userNavbar.php');
include('server.php');

require('fpdf186/fpdf.php');

function generateReport($result, $username, $original_data, $preprocessed_data, $selected_features, $user_input, $mlp_details, $start_time, $end_time) {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Breast Cancer Prediction Report', 0, 1, 'C');

    // User Information
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Username: ' . $username, 0, 1);
    $pdf->Cell(0, 10, 'Form Submission Time: ' . $start_time, 0, 1);
    $pdf->Cell(0, 10, 'Report Generation Time: ' . $end_time, 0, 1);

    // Prediction result and metrics
    $pdf->Cell(0, 10, 'Prediction: ' . ($result['predictions'][0] == 1 ? 'Malignant' : 'Benign'), 0, 1);
    $pdf->Cell(0, 10, 'Accuracy: ' . number_format($result['metrics']['accuracy'], 2) . '%', 0, 1);
    $pdf->Cell(0, 10, 'Precision: ' . number_format($result['metrics']['precision'], 2) . '%', 0, 1);
    $pdf->Cell(0, 10, 'Recall: ' . number_format($result['metrics']['recall'], 2) . '%', 0, 1);
    $pdf->Cell(0, 10, 'F1-Score: ' . number_format($result['metrics']['f1'], 2) . '%', 0, 1);

    // Original Data
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Original Data:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    if (!empty($original_data)) {
        $pdf->Cell(0, 10, implode(', ', $original_data[0]), 0, 1); // Header
        for ($i = 1; $i <= 5 && $i < count($original_data); $i++) { // Show only a few lines
            $pdf->Cell(0, 10, implode(', ', $original_data[$i]), 0, 1);
        }
    } else {
        $pdf->Cell(0, 10, 'No data available', 0, 1);
    }

    // Preprocessed Data
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Preprocessed Data:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    if (!empty($preprocessed_data)) {
        $pdf->Cell(0, 10, implode(', ', $preprocessed_data[0]), 0, 1); // Header
        for ($i = 1; $i <= 5 && $i < count($preprocessed_data); $i++) { // Show only a few lines
            $pdf->Cell(0, 10, implode(', ', $preprocessed_data[$i]), 0, 1);
        }
    } else {
        $pdf->Cell(0, 10, 'No data available', 0, 1);
    }

    // Selected Features
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Selected Features:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    if (!empty($selected_features)) {
        foreach ($selected_features as $feature) {
            $pdf->Cell(0, 10, $feature, 0, 1);
        }
    } else {
        $pdf->Cell(0, 10, 'No data available', 0, 1);
    }

    // User Input
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'User Input:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    foreach ($user_input as $key => $value) {
        $pdf->Cell(0, 10, $key . ': ' . $value, 0, 1);
    }

    // MLP Training Details
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'MLP Training Details:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    foreach ($mlp_details as $key => $value) {
        $pdf->Cell(0, 10, $key . ': ' . $value, 0, 1);
    }

    // Add chart image if available
    if (isset($_SESSION['chart_file']) && file_exists($_SESSION['chart_file'])) {
        $pdf->AddPage();
        $pdf->Image($_SESSION['chart_file'], 10, 10, 190, 0, 'PNG');
        unlink($_SESSION['chart_file']); // Delete the image file after adding to the PDF
        unset($_SESSION['chart_file']);
    }

    // Save the PDF to a file
    $filename = 'report_' . time() . '.pdf';
    $pdf->Output('F', $filename);

    return $filename;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dataset = $_POST['dataset'];
    $input = $_POST;
    unset($input['dataset']);

    $username = $_SESSION['user_username']; // Assuming username is stored in session
    $start_time = date('Y-m-d H:i:s');

    // Fetch the original, preprocessed, and selected data
    $original_data_path = 'original_' . $dataset . '.csv';
    $preprocessed_data_path = 'preprocessed_' . $dataset . '.csv';
    $selected_features_path = 'selected_features_' . $dataset . '.json';

    $original_data = [];
    if (file_exists($original_data_path)) {
        $original_data = array_map('str_getcsv', file($original_data_path));
    }

    $preprocessed_data = [];
    if (file_exists($preprocessed_data_path)) {
        $preprocessed_data = array_map('str_getcsv', file($preprocessed_data_path));
    }

    $selected_features = [];
    if (file_exists($selected_features_path)) {
        $selected_features = json_decode(file_get_contents($selected_features_path), true);
    }

    $mlp_details = ['Layers' => '100', 'Iterations' => '500']; // Example MLP details

    $data = [
        'dataset' => $dataset,
        'input' => $input
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents('http://localhost:5000/predict', false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        echo "HTTP request failed! Error: " . $error['message'];
    } else {
        $response = json_decode($result, true);
        if (isset($response['error'])) {
            echo "Error from server: " . $response['error'];
        } else {
            $_SESSION['prediction_result'] = $response;
            $end_time = date('Y-m-d H:i:s');
            $reportFile = generateReport($response, $username, $original_data, $preprocessed_data, $selected_features, $input, $mlp_details, $start_time, $end_time);
            $_SESSION['report_file'] = $reportFile;

            // Insert the user input and prediction result into the corresponding table
            if ($dataset === 'dataset1') {
                $stmt = $db->prepare("INSERT INTO dataset1_results (user_username, breastfeeding_duration, bmi_weight, bmi_height, bmi, age, age_at_menarche, number_of_children, marital_status, socioeconomic_status, smoking, urban_rural_residence, reproductive_history, prediction, accuracy, `precision`, recall, f1_score, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssssssssssssssss", $username, $input['breastfeeding_duration'], $input['bmi_weight'], $input['bmi_height'], $input['bmi'], $input['age'], $input['age_at_menarche'], $input['number_of_children'], $input['marital_status'], $input['socioeconomic_status'], $input['smoking'], $input['urban_rural_residence'], $input['reproductive_history'], $response['predictions'][0], $response['metrics']['accuracy'], $response['metrics']['precision'], $response['metrics']['recall'], $response['metrics']['f1']);
            } else if ($dataset === 'dataset2') {
                $stmt = $db->prepare("INSERT INTO dataset2_results (user_username, area_worst, concave_points_worst, concave_points_mean, radius_worst, perimeter_worst, perimeter_mean, concavity_mean, area_mean, concavity_worst, radius_mean, prediction, accuracy, `precision`, recall, f1_score, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssssssssssssss", $username, $input['area_worst'], $input['concave_points_worst'], $input['concave_points_mean'], $input['radius_worst'], $input['perimeter_worst'], $input['perimeter_mean'], $input['concavity_mean'], $input['area_mean'], $input['concavity_worst'], $input['radius_mean'], $response['predictions'][0], $response['metrics']['accuracy'], $response['metrics']['precision'], $response['metrics']['recall'], $response['metrics']['f1']);
            }

            if (!$stmt->execute()) {
                echo "Error: " . $stmt->error;
            } else {
                // echo "Data has been inserted successfully.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prediction Result</title>
    <link rel="stylesheet" type="text/css" href="css/userResult.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Prediction Result</h2>
    <div class="result-container">
        <?php
        if (isset($_SESSION['prediction_result'])) {
            $result = $_SESSION['prediction_result'];
            echo "<p><strong>Prediction:</strong> " . ($result['predictions'][0] == 1 ? 'Malignant' : 'Benign') . "</p>";
            echo "<p><strong>Accuracy:</strong> " . number_format($result['metrics']['accuracy'], 2) . "%</p>";
            echo "<p><strong>Precision:</strong> " . number_format($result['metrics']['precision'], 2) . "%</p>";
            echo "<p><strong>Recall:</strong> " . number_format($result['metrics']['recall'], 2) . "%</p>";
            echo "<p><strong>F1-Score:</strong> " . number_format($result['metrics']['f1'], 2) . "%</p>";
            if (isset($_SESSION['report_file'])) {
                echo "<p><a href='" . $_SESSION['report_file'] . "' download>Download Report</a></p>";
            }

            // Add hyperlinks based on prediction result
            if ($result['predictions'][0] == 1) {
                // Malignant result
                echo "<p><a href='userAdvice.php'>Get Advice</a></p>";
            } else {
                // Benign result
                echo "<p><a href='userPrevent.php'>Get Prevention Tips</a></p>";
            }

            // Displaying Chart
            $accuracy = $result['metrics']['accuracy'];
            $precision = $result['metrics']['precision'];
            $recall = $result['metrics']['recall'];
            $f1 = $result['metrics']['f1'];
            echo "
                <canvas id='resultChart'></canvas>
                <script>
                    var ctx = document.getElementById('resultChart').getContext('2d');
                    var resultChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Accuracy', 'Precision', 'Recall', 'F1-Score'],
                            datasets: [{
                                label: 'Metrics',
                                data: [$accuracy, $precision, $recall, $f1],
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(153, 102, 255, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(153, 102, 255, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Save chart as image and send to server
                    var img = resultChart.toBase64Image();
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'userSaveChart.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('img=' + encodeURIComponent(img));
                </script>
            ";

            unset($_SESSION['prediction_result']);
            unset($_SESSION['report_file']);
        } else {
            echo "<p>No prediction result found.</p>";
        }
        ?>
    </div>
</body>
</html>
