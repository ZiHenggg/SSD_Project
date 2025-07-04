<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/auth_check.php';


header("Location: dashboard.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.css">
    <script src="bootstrap-offline/js/jquery-3.6.0.js"></script>
    <script src="bootstrap-offline/js/bootstrap.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
</head>

<body>
    <?php include 'nav.php'; ?>
</body>

</html>