<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Report</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<body>
    <main class="container">
        <header>
            <h1><?= $pageTitle ?? 'Report' ?></h1>
            <a href="/" class="btn">Back to Dashboard</a>
        </header>

        <section class="card" style="margin-bottom: 20px;">
            <h2>Scholarly Documentation</h2>
            <p>Access the complete Scholarly HTML technical documentation detailing the architecture, APIs, and design decisions of the Crisis Containment Service.</p>
            <div style="margin-top: 15px;">
                <a href="/report.html" target="_blank" class="btn" style="background-color: #8e44ad;">View Scholarly HTML</a>
            </div>
        </section>

        <section class="card">
            <h2>CAP XML Reports</h2>
            <p>Use the buttons below to generate CAP (Common Alerting Protocol) XML feeds based on the current disaster data.</p>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <a href="/api/cap" target="_blank" class="btn">Generate General CAP Feed (All Active)</a>
            </div>
        </section>
    </main>
</body>
</html>