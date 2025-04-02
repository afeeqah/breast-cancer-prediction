<?php
include('adminSidebar.php');
include('server.php');

$errors = [];

// Function to fetch datasets from the database by type
function getDatasetsByType($db, $type) {
    if ($type === 'user') {
        return []; // We are removing user uploaded datasets.
    } else {
        $query = "SELECT id, name as dataset_name, num_records, date_uploaded as created_at FROM dataset WHERE type='$type'";
    }
    $result = mysqli_query($db, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Function to count the number of records in a CSV file for user datasets
function countCsvRows($filePath) {
    $count = 0;
    if (file_exists($filePath)) {
        $file = fopen($filePath, "r");
        while (!feof($file)) {
            fgetcsv($file);
            $count++;
        }
        fclose($file);
        return $count - 1; // Subtract 1 for header
    }
    return 0;
}

// Function to upload a new dataset (admin)
if (isset($_POST['upload_dataset'])) {
    $filename = $_FILES['dataset']['name'];
    $file_tmp = $_FILES['dataset']['tmp_name'];
    $target_feature = $_POST['target_feature'];

    if (pathinfo($filename, PATHINFO_EXTENSION) != 'csv') {
        $errors[] = "Only CSV files are allowed.";
    } else {
        $csv_file = fopen($file_tmp, 'r');
        $header = fgetcsv($csv_file);
        $num_records = 0;
        while ($row = fgetcsv($csv_file)) {
            $num_records++;
        }
        fclose($csv_file);

        $destination = 'uploads/' . $filename;
        move_uploaded_file($file_tmp, $destination);

        $query = "INSERT INTO dataset (name, path, num_records, date_uploaded, type, target_feature) VALUES ('$filename', '$destination', $num_records, NOW(), 'admin', '$target_feature')";
        mysqli_query($db, $query);
        header('location: adminManageDataset.php');
    }
}

// Function to delete a dataset
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM dataset WHERE id=$id";
    mysqli_query($db, $query);
    header('location: adminManageDataset.php');
}

// Function to preview a dataset
if (isset($_GET['preview'])) {
    $id = $_GET['preview'];
    $query = "SELECT path as dataset_path FROM dataset WHERE id=$id";
    $result = mysqli_query($db, $query);
    $dataset = mysqli_fetch_assoc($result);

    $file = $dataset['dataset_path'];
    $csv_file = fopen($file, 'r');
    $header = fgetcsv($csv_file);
    $rows = [];
    while ($row = fgetcsv($csv_file)) {
        $rows[] = $row;
    }
    fclose($csv_file);
}

$errors = $errors ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Manage Dataset</title>
    <link rel="stylesheet" href="css/adminManageDataset.css">
    <script>
        let preprocessedData = [];

        function uploadDataset(event) {
            event.preventDefault();
            const fileInput = document.getElementById('dataset');
            const formData = new FormData();
            formData.append('dataset', fileInput.files[0]);
            formData.append('target', document.getElementById('target_feature').value);

            fetch('http://localhost:5000/upload', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    document.getElementById('dataset_path').value = data.path;
                    alert('Dataset uploaded successfully!');
                    displayTable('dataset_preview', data.preview);
                    // Update the admin uploaded datasets table
                    updateAdminUploadedDatasets(data.path, data.columns.length, new Date().toLocaleString());
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function preprocessDataset(event) {
            event.preventDefault();
            const datasetPath = document.getElementById('dataset_path').value;
            const targetFeature = document.getElementById('target_feature').value;

            fetch('http://localhost:5000/preprocess', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ dataset_path: datasetPath, target: targetFeature })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    alert('Dataset preprocessed successfully!');
                    preprocessedData = data.preprocessed_data;
                    displayTable('preprocess_output', data.preprocessed_data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function selectFeatures(event) {
            event.preventDefault();
            const targetFeature = document.getElementById('target_feature').value;

            fetch('http://localhost:5000/select_features', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ data: preprocessedData, target: targetFeature })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    displayTable('selected_features_output', data.selected_features.map(f => ({ feature: f })));
                    const selectedFeaturesTextarea = document.getElementById('selected_features');
                    selectedFeaturesTextarea.value = JSON.stringify(data.selected_features, null, 2);
                    document.getElementById('copy_button').style.display = 'block';
                    alert('Features selected successfully!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function displayTable(containerId, data) {
            const container = document.getElementById(containerId);
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<p>No data to display</p>';
                return;
            }

            const table = document.createElement('table');
            table.className = 'scrollable-table';

            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            Object.keys(data[0]).forEach(key => {
                const th = document.createElement('th');
                th.textContent = key;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            data.forEach(row => {
                const tr = document.createElement('tr');
                Object.values(row).forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = value;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);

            container.innerHTML = '';
            container.appendChild(table);
        }

        function copyToClipboard() {
            const selectedFeaturesTextarea = document.getElementById('selected_features');
            selectedFeaturesTextarea.select();
            document.execCommand('copy');
            alert('Selected features copied to clipboard!');
        }

        function updateAdminUploadedDatasets(name, num_records, date_uploaded) {
            const tableBody = document.querySelector('#admin_uploaded_datasets tbody');
            const row = document.createElement('tr');

            const nameCell = document.createElement('td');
            nameCell.textContent = name;
            row.appendChild(nameCell);

            const recordsCell = document.createElement('td');
            recordsCell.textContent = num_records;
            row.appendChild(recordsCell);

            const dateCell = document.createElement('td');
            dateCell.textContent = date_uploaded;
            row.appendChild(dateCell);

            const actionsCell = document.createElement('td');
            const previewLink = document.createElement('a');
            previewLink.href = '#';
            previewLink.textContent = 'Preview';
            previewLink.addEventListener('click', () => {
                // Call preview function or perform relevant action
            });
            actionsCell.appendChild(previewLink);

            const deleteLink = document.createElement('a');
            deleteLink.href = `adminManageDataset.php?delete=${name}`;
            deleteLink.textContent = 'Delete';
            deleteLink.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this dataset?')) {
                    e.preventDefault();
                }
            });
            actionsCell.appendChild(deleteLink);

            row.appendChild(actionsCell);
            tableBody.appendChild(row);
        }
    </script>
    <style>
        .scrollable-table {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: auto;
            display: block;
            width: 100%;
            border-collapse: collapse;
        }
        .scrollable-table th, .scrollable-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        #copy_button {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Manage Dataset</h2>

        <!-- Upload dataset form -->
        <form onsubmit="uploadDataset(event)">
            <div class="form-group">
                <label for="dataset">Upload Dataset (CSV):</label>
                <input type="file" name="dataset" id="dataset" accept=".csv" required>
            </div>
            <div class="form-group">
                <label for="target_feature">Target Feature:</label>
                <input type="text" name="target_feature" id="target_feature" required>
            </div>
            <button type="submit" name="upload_dataset">Upload</button>
        </form>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Display dataset preview after upload -->
        <div id="dataset_preview"></div>

        <!-- Preprocess dataset form -->
        <form onsubmit="preprocessDataset(event)">
            <input type="hidden" name="dataset_path" id="dataset_path" required>
            <button type="submit">Preprocess Dataset</button>
        </form>
        <div id="preprocess_output"></div>

        <!-- Select features form -->
        <form onsubmit="selectFeatures(event)">
            <input type="hidden" name="dataset_path" id="dataset_path" required>
            <input type="hidden" name="target_feature" id="target_feature" required>
            <button type="submit">Select Features</button>
        </form>
        <textarea id="selected_features" rows="5" required></textarea>
        <button id="copy_button" onclick="copyToClipboard()">Copy</button>
        <div id="selected_features_output"></div>

        <!-- System Datasets Table -->
        <h3>System Datasets</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Dataset Name</th>
                        <th>Number of Records</th>
                        <th>Date Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $datasets = getDatasetsByType($db, 'system');
                    foreach ($datasets as $dataset): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dataset['dataset_name']); ?></td>
                            <td><?php echo htmlspecialchars($dataset['num_records']); ?></td>
                            <td><?php echo htmlspecialchars($dataset['created_at']); ?></td>
                            <td>
                                <a href="adminManageDataset.php?preview=<?php echo $dataset['id']; ?>">Preview</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Admin Uploaded Datasets Table -->
        <h3>Admin Uploaded Datasets</h3>
        <div class="table-container" id="admin_uploaded_datasets">
            <table>
                <thead>
                    <tr>
                        <th>Dataset Name</th>
                        <th>Number of Records</th>
                        <th>Date Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $datasets = getDatasetsByType($db, 'admin');
                    foreach ($datasets as $dataset): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dataset['dataset_name']); ?></td>
                            <td><?php echo htmlspecialchars($dataset['num_records']); ?></td>
                            <td><?php echo htmlspecialchars($dataset['created_at']); ?></td>
                            <td>
                                <a href="adminManageDataset.php?preview=<?php echo $dataset['id']; ?>">Preview</a>
                                <a href="adminManageDataset.php?delete=<?php echo $dataset['id']; ?>" onclick="return confirm('Are you sure you want to delete this dataset?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Preview Modal -->
        <?php if (isset($rows)): ?>
            <div id="previewModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="document.getElementById('previewModal').style.display='none'">&times;</span>
                    <h3>Dataset Preview</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach ($header as $column): ?>
                                        <th><?php echo htmlspecialchars($column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $cell): ?>
                                            <td><?php echo htmlspecialchars($cell); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        if (document.getElementById('previewModal')) {
            document.getElementById('previewModal').style.display = 'block';
        }
    </script>
</body>
</html>
