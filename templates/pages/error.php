<?php
// A simple, reusable error page template.

// Set a default title if one isn't passed from the router.
if (!isset($pageTitle)) {
    $pageTitle = "An Error Occurred";
}

// Set default message and code. The router can override these.
$errorCode = $errorCode ?? 404;
$errorMessage = $errorMessage ?? 'Page Not Found';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= APP_NAME ?></title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; }
        h1 { font-size: 48px; color: #555; }
        p { font-size: 18px; color: #777; }
        a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Error <?= (int)$errorCode ?></h1>
    <p><?= htmlspecialchars($errorMessage) ?></p>
    <hr style="margin: 20px 50px;">
    <p><a href="/">Return to Homepage</a></p>
</body>
</html>
