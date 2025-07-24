<?php
include('connect.php');

if (isset($_GET['payment_id'])) {
    $paymentId = $_GET['payment_id'];

    // Fetch the image from the database
    $stmt = $conn->prepare("SELECT payment_references FROM payment WHERE payment_id = ?");
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
    $stmt->bind_result($imageData);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
} else {
    echo "<p>No payment ID provided.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Image</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        img {
            max-width: 90%;
            max-height: 90%;
            border: 1px solid #ccc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php
    if ($imageData) {
        echo "<img src='data:image/jpg;base64," . base64_encode($imageData) . "' alt='Payment Reference'>";
    } else {
        echo "<p>Image not found.</p>";
    }
    ?>
</body>
</html>