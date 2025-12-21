<?php
declare(strict_types=1);

require_once 'inc/config.php';
require_once 'inc/db.inc.php';

if (!isset($_SESSION['app_initialized'])) {
    $_SESSION['app_initialized'] = true;
    $_SESSION['last_visit'] = date('Y-m-d H:i:s');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervised Driving Experience - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="main-container">
    <header style="text-align: center; margin-bottom: 50px;">
        <h1>Supervised Driving Experience</h1>
        <p class="subtitle">Track and analyze your driving experiences</p>
    </header>

    <nav class="home-navigation">
        <a href="new_experience.php" class="nav-card">
            <div class="nav-icon">📝</div>
            <h2>Record New Experience</h2>
            <p>Add a new driving experience with weather, traffic, and road conditions</p>
        </a>

        <a href="dashboard.php" class="nav-card">
            <div class="nav-icon">📊</div>
            <h2>Dashboard</h2>
            <p>View all driving experiences and total kilometers traveled</p>
        </a>

        <a href="report.php" class="nav-card">
            <div class="nav-icon">📈</div>
            <h2>Statistics</h2>
            <p>Analyze driving conditions with charts and graphs</p>
        </a>

        <a href="manage_variables.php" class="nav-card">
            <div class="nav-icon">⚙️</div>
            <h2>Manage Variables</h2>
            <p>Add or edit weather conditions, journey types, and other variables</p>
        </a>
    </nav>

    <footer>
        <hr>
        <p>Supervised Driving Experience Application</p>
        <p>Last visit: <?= htmlspecialchars($_SESSION['last_visit'] ?? 'First visit') ?></p>
    </footer>
</main>

</body>
</html>

