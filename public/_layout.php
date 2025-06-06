<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'GroupMates' ?></title>
    <link rel="icon" type="image/x-icon" href="/img/logo.svg">
    <link rel="stylesheet" href="bootstrap-offline/css/bootstrap.css">
    <script src="bootstrap-offline/js/jquery-3.6.0.js"></script>
    <script src="bootstrap-offline/js/bootstrap.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/group.css">
    <link rel="stylesheet" href="css/profile.css">
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="initial my-5">
        <?= $content ?? '' ?>
    </div>
</body>

<script src="js/home.js"></script>

</html>