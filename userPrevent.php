<?php
include('userNavbar.php');
include('server.php');

$query = "SELECT * FROM prevention ORDER BY order_index";
$result = mysqli_query($db, $query);
$preventions = mysqli_fetch_all($result, MYSQLI_ASSOC);

$current_section = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prevention Tips for Breast Cancer</title>
    <link rel="stylesheet" href="css/userAdvicePrevent.css">
</head>
<body>
    <div class="container">
        <h2>Prevention Tips for Breast Cancer</h2>
        <?php foreach ($preventions as $prevention): ?>
            <?php if ($current_section != $prevention['section']): ?>
                <h3><?php echo $prevention['section']; ?></h3>
                <?php $current_section = $prevention['section']; ?>
            <?php endif; ?>
            <div class="prevention" style="border-left-color: <?php echo htmlspecialchars($prevention['color']); ?>;">
                <h4><?php echo $prevention['title']; ?></h4>
                <p><?php echo $prevention['content']; ?></p>
                <?php
                $images = json_decode($prevention['images'], true);
                $descriptions = json_decode($prevention['image_descriptions'], true);
                foreach ($images as $index => $img): ?>
                    <div class="existing-image">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Image" style="width: <?php echo htmlspecialchars($prevention['image_size']); ?>; max-width: 100%;">
                        <p><?php echo htmlspecialchars($descriptions[$index] ?? ''); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
