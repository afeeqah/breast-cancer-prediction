<?php
include('server.php');

// Paths to dataset files (from train.py and app.py)
$system_datasets = [
    ["path" => "data1.csv", "name" => "System Dataset 1"],
    ["path" => "data2.csv", "name" => "System Dataset 2"]
];

// Function to extract dataset info
function extract_dataset_info($path) {
    $data = array_map('str_getcsv', file($path));
    $num_records = count($data) - 1; // Exclude header row
    $date_uploaded = date('Y-m-d H:i:s', filemtime($path));
    return [$num_records, $date_uploaded];
}

// Insert or update dataset info in database
foreach ($system_datasets as $dataset) {
    list($num_records, $date_uploaded) = extract_dataset_info($dataset["path"]);
    $name = $dataset["name"];
    $path = $dataset["path"];

    $query = "SELECT * FROM dataset WHERE name='$name' AND path='$path'";
    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        $query = "UPDATE dataset SET num_records=$num_records, date_uploaded='$date_uploaded' WHERE name='$name' AND path='$path'";
    } else {
        $query = "INSERT INTO dataset (name, path, num_records, date_uploaded, type)
                  VALUES ('$name', '$path', $num_records, '$date_uploaded', 'system')";
    }
    mysqli_query($db, $query);
}
