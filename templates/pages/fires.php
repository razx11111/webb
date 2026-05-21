<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <!--
        We use relative paths for CSS.
        Note: In a real environment, you might use an absolute URL or a helper.
    -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <?php /** @var string $pageTitle */ ?>
    <h1><?= $pageTitle ?></h1>
</body>
