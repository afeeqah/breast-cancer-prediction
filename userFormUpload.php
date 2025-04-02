<?php
include('userNavbar.php');
include('server.php');

if (!isset($_SESSION['user_username'])) {
    echo "<script>alert('User not logged in.'); window.location.href = 'login.php';</script>";
    exit();
}

$username = $_SESSION['user_username'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Your Dataset</title>
    <link rel="stylesheet" type="text/css" href="css/userForm.css">
    <style>
        .scrollable-table {
            max-height: 300px;
            overflow-y: auto;
            display: block;
        }
        .scrollable-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .scrollable-table th, .scrollable-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .scrollable-table th {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function showAlert(message) {
            alert(message);
        }

        function validateTargetFeature(event) {
            event.preventDefault();
            const targetFeature = document.getElementById("target").value;
            const dataset = document.getElementById("dataset").files[0];

            if (!targetFeature || !dataset) {
                showAlert("Please fill out all fields.");
                return false;
            }

            const formData = new FormData();
            formData.append("dataset", dataset);
            formData.append("target", targetFeature);

            fetch("http://localhost:5000/upload", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error);
                } else {
                    const previewContainer = document.getElementById("dataset-preview");
                    let table = "<div class='scrollable-table'><table><tr>";
                    data.columns.forEach(column => {
                        table += "<th>" + column + "</th>";
                    });
                    table += "</tr>";
                    data.preview.forEach(row => {
                        table += "<tr>";
                        data.columns.forEach(column => {
                            table += "<td>" + (row[column] !== null ? row[column] : "") + "</td>";
                        });
                        table += "</tr>";
                    });
                    table += "</table></div>";
                    previewContainer.innerHTML = table;
                    document.getElementById("dataset-path").value = data.path;
                    document.getElementById("preprocess-form").style.display = "block";
                }
            })
            .catch(error => {
                showAlert("Error: " + error);
            });

            return false;
        }

        function preprocessDataset(event) {
            event.preventDefault();
            const datasetPath = document.getElementById("dataset-path").value;
            const targetFeature = document.getElementById("target").value;

            const data = {
                dataset_path: datasetPath,
                target: targetFeature
            };

            fetch("http://localhost:5000/preprocess", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error);
                } else {
                    const previewContainer = document.getElementById("preprocessed-data");
                    let table = "<div class='scrollable-table'><table><tr>";
                    data.preprocessed_columns.forEach(column => {
                        table += "<th>" + column + "</th>";
                    });
                    table += "</tr>";
                    data.preprocessed_data.forEach(row => {
                        table += "<tr>";
                        data.preprocessed_columns.forEach(column => {
                            table += "<td>" + (row[column] !== null ? row[column] : "") + "</td>";
                        });
                        table += "</tr>";
                    });
                    table += "</table></div>";
                    previewContainer.innerHTML = table;
                    document.getElementById("preprocessor-path").value = data.preprocessor_path;
                    document.getElementById("preprocessed-data-json").value = JSON.stringify(data.preprocessed_data);
                    document.getElementById("selected-features").style.display = "block";
                }
            })
            .catch(error => {
                showAlert("Error: " + error);
            });

            return false;
        }

        function selectFeatures(event) {
            event.preventDefault();

            let preprocessedData = [];
            try {
                const preprocessedDataText = document.getElementById("preprocessed-data-json").value;
                if (preprocessedDataText) {
                    preprocessedData = JSON.parse(preprocessedDataText);
                }
            } catch (error) {
                showAlert("Error parsing preprocessed data: " + error);
                return false;
            }

            const targetFeature = document.getElementById("target").value;

            const data = {
                data: preprocessedData,
                target: targetFeature
            };

            fetch("http://localhost:5000/select_features", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error);
                } else {
                    const selectedFeaturesContainer = document.getElementById("selected-features-list");
                    let table = "<div class='scrollable-table'><table><tr><th>Selected Features</th></tr>";
                    data.selected_features.forEach(feature => {
                        table += "<tr><td>" + feature + "</td></tr>";
                    });
                    table += "</table></div>";
                    selectedFeaturesContainer.innerHTML = table;
                    document.getElementById("selected-features-data").value = JSON.stringify(data.selected_features);
                    document.getElementById("train-model").style.display = "block";
                }
            })
            .catch(error => {
                showAlert("Error: " + error);
            });

            return false;
        }

        function trainModel(event) {
            event.preventDefault();
            let preprocessedData = [];
            try {
                const preprocessedDataText = document.getElementById("preprocessed-data-json").value;
                if (preprocessedDataText) {
                    preprocessedData = JSON.parse(preprocessedDataText);
                }
            } catch (error) {
                showAlert("Error parsing preprocessed data: " + error);
                return false;
            }

            const datasetPath = document.getElementById("dataset-path").value;
            const targetFeature = document.getElementById("target").value;
            const selectedFeatures = JSON.parse(document.getElementById("selected-features-data").value);
            const preprocessorPath = document.getElementById("preprocessor-path").value;

            const data = {
                data: preprocessedData,
                target: targetFeature,
                selected_features: selectedFeatures,
                preprocessor_path: preprocessorPath
            };

            fetch("http://localhost:5000/train", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error);
                } else {
                    // Store the metrics in a hidden field and display them in a table
                    document.getElementById("model-metrics-json").value = JSON.stringify(data.metrics);
                    
                    const metricsTable = `<div class='scrollable-table'><table>
                        <tr><th>Metric</th><th>Value</th></tr>
                        <tr><td>Accuracy</td><td>${data.metrics.accuracy}</td></tr>
                        <tr><td>Precision</td><td>${data.metrics.precision}</td></tr>
                        <tr><td>Recall</td><td>${data.metrics.recall}</td></tr>
                        <tr><td>F1 Score</td><td>${data.metrics.f1}</td></tr>
                    </table></div>`;
                    document.getElementById("model-metrics").innerHTML = metricsTable;

                    // Save to database and set dataset_id
                    saveToDatabase("user_uploaded_dataset", "<?php echo $username; ?>", datasetPath, targetFeature, selectedFeatures, data.metrics, function(dataset_id) {
                        document.getElementById("dataset-id").value = dataset_id;
                        document.getElementById("generate-form").style.display = "block";
                    });
                }
            })
            .catch(error => {
                showAlert("Error: " + error);
            });

            return false;
        }

        function saveToDatabase(datasetName, userName, datasetPath, targetFeature, selectedFeatures, metrics, callback) {
            const formData = new FormData();
            formData.append("dataset_name", datasetName);
            formData.append("user_username", userName); // Use the variable passed from PHP
            formData.append("dataset_path", datasetPath);
            formData.append("target_feature", targetFeature);
            formData.append("selected_features", JSON.stringify(selectedFeatures));
            formData.append("accuracy", metrics.accuracy);
            formData.append("precision", metrics.precision);
            formData.append("recall", metrics.recall);
            formData.append("f1_score", metrics.f1);

            fetch("userSaveFormResult.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showAlert(data.error);
                } else {
                    console.log("Data saved successfully.");
                    callback(data.dataset_id);
                }
            })
            .catch(error => {
                showAlert("Error: " + error);
            });
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Upload Your Dataset</h2>
        <form onsubmit="return validateTargetFeature(event)">
            <label for="dataset">Select your dataset:</label>
            <input type="file" name="dataset" id="dataset" accept=".csv" required><br>
            <label for="target">Target feature:</label>
            <input type="text" name="target" id="target" required><br>
            <input type="submit" value="Upload">
        </form>
        <div id="dataset-preview"></div>
        <form id="preprocess-form" style="display:none;" onsubmit="return preprocessDataset(event)">
            <input type="hidden" id="dataset-path">
            <input type="hidden" id="preprocessor-path">
            <input type="hidden" id="preprocessed-data-json">
            <input type="submit" value="Preprocess">
        </form>
        <div id="preprocessed-data"></div>
        <div id="selected-features" style="display:none;">
            <form onsubmit="selectFeatures(event)">
                <input type="submit" value="Show Selected Features">
            </form>
            <div id="selected-features-list"></div>
            <input type="hidden" id="selected-features-data">
        </div>
        <div id="train-model" style="display:none;">
            <form onsubmit="return trainModel(event)">
                <input type="submit" value="Show Metrics Evaluation">
            </form>
            <div id="model-metrics"></div>
            <input type="hidden" id="model-metrics-json">
            <form action="userFormUpload2.php" method="post">
                <input type="hidden" id="dataset-id" name="dataset_id"> <!-- Add dataset-id hidden field -->
                <input type="submit" value="Generate Form" id="generate-form" style="display:none;">
            </form>
        </div>
    </div>
    <?php
    if (isset($_GET['error'])) {
        echo "<script>showAlert('" . htmlspecialchars($_GET['error']) . "');</script>";
    }
    ?>
</body>
</html>
