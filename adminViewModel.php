<?php
include('adminSidebar.php');
include('server.php');

if (!isset($_SESSION['admin_username'])) {
    echo "<script>alert('Admin not logged in.'); window.location.href = 'login.php';</script>";
    exit();
}

// Function to fetch and aggregate metrics from the given table
function fetch_metrics($db, $table, $columns) {
    $query = "SELECT $columns FROM $table";
    $result = mysqli_query($db, $query);
    $metrics = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $metrics[] = $row;
    }
    return $metrics;
}

// Fetch metrics from the specified tables
$dataset1_metrics = fetch_metrics($db, 'dataset1_results', 'accuracy, `precision`, recall, f1_score, created_at');
$dataset2_metrics = fetch_metrics($db, 'dataset2_results', 'accuracy, `precision`, recall, f1_score, created_at');
$uploaded_datasets_metrics = fetch_metrics($db, 'uploaded_datasets', 'accuracy, `precision`, recall, f1_score, created_at');
$uploaded_dataset_results_metrics = fetch_metrics($db, 'uploaded_dataset_results', 'accuracy, `precision`, recall, f1_score, created_at');

// Combine all metrics
$all_metrics = array_merge($dataset1_metrics, $dataset2_metrics, $uploaded_datasets_metrics, $uploaded_dataset_results_metrics);

function group_metrics_by_month($metrics) {
    $monthly_metrics = [];
    foreach ($metrics as $metric) {
        $month = date('Y-m', strtotime($metric['created_at']));
        if (!isset($monthly_metrics[$month])) {
            $monthly_metrics[$month] = ['accuracy' => [], 'precision' => [], 'recall' => [], 'f1_score' => []];
        }
        $monthly_metrics[$month]['accuracy'][] = $metric['accuracy'];
        $monthly_metrics[$month]['precision'][] = $metric['precision'];
        $monthly_metrics[$month]['recall'][] = $metric['recall'];
        $monthly_metrics[$month]['f1_score'][] = $metric['f1_score'];
    }
    return $monthly_metrics;
}

$monthly_metrics = group_metrics_by_month($all_metrics);

function calculate_average_metrics($monthly_metrics) {
    $averaged_metrics = [];
    foreach ($monthly_metrics as $month => $metrics) {
        $averaged_metrics[$month] = [
            'accuracy' => array_sum($metrics['accuracy']) / count($metrics['accuracy']),
            'precision' => array_sum($metrics['precision']) / count($metrics['precision']),
            'recall' => array_sum($metrics['recall']) / count($metrics['recall']),
            'f1_score' => array_sum($metrics['f1_score']) / count($metrics['f1_score']),
        ];
    }
    return $averaged_metrics;
}

$averaged_metrics = calculate_average_metrics($monthly_metrics);

// Handle retraining
$retrain_output = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retrain'])) {
    $output = shell_exec('python train.py');
    $retrain_output = $output ? $output : 'Retraining executed, but no output was captured.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Model</title>
    <link rel="stylesheet" type="text/css" href="css/adminViewModel.css?v=1.2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h2>View Model</h2>

        <div class="model-details">
            <h3>Model Data Insights</h3>
            <div class="model-metrics">
                <h3>Performance Metrics Over the Months</h3>
                <canvas id="metricsChart"></canvas>
                <script>
                    var ctx = document.getElementById('metricsChart').getContext('2d');
                    var metricsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode(array_keys($averaged_metrics)); ?>,
                            datasets: [
                                {
                                    label: 'Accuracy',
                                    data: <?php echo json_encode(array_column($averaged_metrics, 'accuracy')); ?>,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    fill: false
                                },
                                {
                                    label: 'Precision',
                                    data: <?php echo json_encode(array_column($averaged_metrics, 'precision')); ?>,
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    fill: false
                                },
                                {
                                    label: 'Recall',
                                    data: <?php echo json_encode(array_column($averaged_metrics, 'recall')); ?>,
                                    borderColor: 'rgba(255, 206, 86, 1)',
                                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                    fill: false
                                },
                                {
                                    label: 'F1-Score',
                                    data: <?php echo json_encode(array_column($averaged_metrics, 'f1_score')); ?>,
                                    borderColor: 'rgba(153, 102, 255, 1)',
                                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                    fill: false
                                }
                            ]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                zoom: {
                                    zoom: {
                                        wheel: {
                                            enabled: true
                                        },
                                        pinch: {
                                            enabled: true
                                        },
                                        mode: 'x'
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>

            <div class="center-content">
                <div class="retrain-container">
                    <h3>Retrain Model</h3>
                    <p>Click the button below to retrain the model. This will refresh the backend files and update the metrics evaluation for dataset 1 and dataset 2 that are provided by the system.</p>
                    <form method="POST" action="">
                        <button type="submit" name="retrain">Retrain Model</button>
                    </form>
                    <?php if ($retrain_output): ?>
                        <div class="retrain-output">
                            <h3>Retrain Output</h3>
                            <pre><?php echo $retrain_output; ?></pre>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="search-container">
                    <h3>Search Metrics</h3>
                    <form id="searchForm">
                        <label for="searchMinValue">Min Value:</label>
                        <input type="number" id="searchMinValue" name="searchMinValue" step="any" required>
                        <label for="searchMaxValue">Max Value:</label>
                        <input type="number" id="searchMaxValue" name="searchMaxValue" step="any" required>
                        <button type="button" onclick="searchMetrics()">Search</button><br>
                        <label for="searchDate">Search by Date:</label>
                        <input type="date" id="searchDate" name="searchDate">
                        <button type="button" onclick="searchMetricsByDate()">Search</button>
                    </form>
                </div>
            </div>

            <div class="metrics-history">
                <h3>Metrics History</h3>
                <div class="table-container">
                    <table id="metricsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Accuracy</th>
                                <th>Precision</th>
                                <th>Recall</th>
                                <th>F1-Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_metrics as $metric): ?>
                                <tr>
                                    <td><?php echo $metric['created_at']; ?></td>
                                    <td><?php echo $metric['accuracy']; ?>%</td>
                                    <td><?php echo $metric['precision']; ?>%</td>
                                    <td><?php echo $metric['recall']; ?>%</td>
                                    <td><?php echo $metric['f1_score']; ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        function searchMetrics() {
            var minValue = parseFloat(document.getElementById('searchMinValue').value);
            var maxValue = parseFloat(document.getElementById('searchMaxValue').value);
            var table = document.getElementById('metricsTable');
            var rows = table.getElementsByTagName('tr');
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName('td');
                var match = false;
                for (var j = 1; j < cells.length; j++) {
                    var cellValue = parseFloat(cells[j].innerText);
                    if (cellValue >= minValue && cellValue <= maxValue) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? '' : 'none';
            }
        }

        function searchMetricsByDate() {
            var searchDate = document.getElementById('searchDate').value;
            var table = document.getElementById('metricsTable');
            var rows = table.getElementsByTagName('tr');
            for (var i = 1; i < rows.length; i++) {
                var dateCell = rows[i].getElementsByTagName('td')[0];
                var rowDate = new Date(dateCell.innerText).toISOString().slice(0, 10);
                rows[i].style.display = (rowDate === searchDate) ? '' : 'none';
            }
        }
    </script>
</body>
</html>
