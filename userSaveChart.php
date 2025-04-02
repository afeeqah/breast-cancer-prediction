<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['img'])) {
    $img = $_POST['img'];
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $data = base64_decode($img);
    $file = 'chart_' . time() . '.png';
    file_put_contents($file, $data);
    $_SESSION['chart_file'] = $file;
    echo 'File saved: ' . $file;
} else {
    echo 'No image data received';
}
