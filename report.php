<?php
declare(strict_types=1);

require_once 'inc/config.php';
require_once 'inc/db.inc.php';

$_SESSION['stats_visits'] = ($_SESSION['stats_visits'] ?? 0) + 1;
$_SESSION['last_stats_visit'] = date('Y-m-d H:i:s');

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$filterWeather = (int)($_GET['filter_weather'] ?? 0);
$filterJourney = (int)($_GET['filter_journey'] ?? 0);
$filterSurface = (int)($_GET['filter_surface'] ?? 0);
$filterTraffic = (int)($_GET['filter_traffic'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $_SESSION['report_filters'] = [
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'filter_weather' => $filterWeather,
        'filter_journey' => $filterJourney,
        'filter_surface' => $filterSurface,
        'filter_traffic' => $filterTraffic
    ];
}

$whereConditions = [];
$params = [];

if (!empty($dateFrom)) {
    $whereConditions[] = "e.driving_date >= ?";
    $params[] = $dateFrom;
}
if (!empty($dateTo)) {
    $whereConditions[] = "e.driving_date <= ?";
    $params[] = $dateTo;
}
if ($filterWeather > 0) {
    $whereConditions[] = "EXISTS (SELECT 1 FROM experience_weather ew WHERE ew.experience_id = e.id AND ew.weather_id = ?)";
    $params[] = $filterWeather;
}
if ($filterJourney > 0) {
    $whereConditions[] = "e.id_journey = ?";
    $params[] = $filterJourney;
}
if ($filterSurface > 0) {
    $whereConditions[] = "e.id_surface = ?";
    $params[] = $filterSurface;
}
if ($filterTraffic > 0) {
    $whereConditions[] = "e.id_traffic = ?";
    $params[] = $filterTraffic;
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

if (!empty($whereConditions)) {
    $weatherSql = "
        SELECT w.label AS weather, COUNT(*) AS count
        FROM experience_weather ew
        JOIN weather w ON ew.weather_id = w.id
        JOIN driving_experience e ON ew.experience_id = e.id
        {$whereClause}
        GROUP BY w.id
        ORDER BY count DESC
    ";
    $weatherStmt = $pdo->prepare($weatherSql);
    $weatherStmt->execute($params);
    $weatherStats = $weatherStmt->fetchAll();
} else {
    $weatherSql = "
        SELECT w.label AS weather, COUNT(*) AS count
        FROM experience_weather ew
        JOIN weather w ON ew.weather_id = w.id
        GROUP BY w.id
        ORDER BY count DESC
    ";
    $weatherStats = $pdo->query($weatherSql)->fetchAll();
}

if (!empty($whereConditions)) {
    $journeySql = "
        SELECT jt.label AS journey, COUNT(*) AS count
        FROM driving_experience e
        JOIN journey_type jt ON e.id_journey = jt.id
        {$whereClause}
        GROUP BY jt.id
    ";
    $journeyStmt = $pdo->prepare($journeySql);
    $journeyStmt->execute($params);
    $journeyStats = $journeyStmt->fetchAll();
} else {
    $journeySql = "
        SELECT jt.label AS journey, COUNT(*) AS count
        FROM driving_experience e
        JOIN journey_type jt ON e.id_journey = jt.id
        GROUP BY jt.id
    ";
    $journeyStats = $pdo->query($journeySql)->fetchAll();
}

if (!empty($whereConditions)) {
    $trafficSql = "
        SELECT tt.label AS traffic, COUNT(*) AS count
        FROM driving_experience e
        JOIN traffic_type tt ON e.id_traffic = tt.id
        {$whereClause}
        GROUP BY tt.id
    ";
    $trafficStmt = $pdo->prepare($trafficSql);
    $trafficStmt->execute($params);
    $trafficStats = $trafficStmt->fetchAll();
} else {
    $trafficSql = "
        SELECT tt.label AS traffic, COUNT(*) AS count
        FROM driving_experience e
        JOIN traffic_type tt ON e.id_traffic = tt.id
        GROUP BY tt.id
    ";
    $trafficStats = $pdo->query($trafficSql)->fetchAll();
}

if (!empty($whereConditions)) {
    $surfaceSql = "
        SELECT rs.label AS surface, COUNT(*) AS count
        FROM driving_experience e
        JOIN road_surface rs ON e.id_surface = rs.id
        {$whereClause}
        GROUP BY rs.id
    ";
    $surfaceStmt = $pdo->prepare($surfaceSql);
    $surfaceStmt->execute($params);
    $surfaceStats = $surfaceStmt->fetchAll();
} else {
    $surfaceSql = "
        SELECT rs.label AS surface, COUNT(*) AS count
        FROM driving_experience e
        JOIN road_surface rs ON e.id_surface = rs.id
        GROUP BY rs.id
    ";
    $surfaceStats = $pdo->query($surfaceSql)->fetchAll();
}

try {
    $weatherList = $pdo->query("SELECT id, label FROM weather ORDER BY label")->fetchAll();
    $journeyList = $pdo->query("SELECT id, label FROM journey_type ORDER BY label")->fetchAll();
    $surfaceList = $pdo->query("SELECT id, label FROM road_surface ORDER BY label")->fetchAll();
    $trafficList = $pdo->query("SELECT id, label FROM traffic_type ORDER BY label")->fetchAll();
} catch (PDOException $e) {
    $weatherList = [];
    $journeyList = [];
    $surfaceList = [];
    $trafficList = [];
    error_log("Error loading filter data: " . $e->getMessage());
}

$weatherLabels = !empty($weatherStats) ? array_column($weatherStats, 'weather') : [];
$weatherCounts = !empty($weatherStats) ? array_column($weatherStats, 'count') : [];

$journeyLabels = !empty($journeyStats) ? array_column($journeyStats, 'journey') : [];
$journeyCounts = !empty($journeyStats) ? array_column($journeyStats, 'count') : [];

$trafficLabels = !empty($trafficStats) ? array_column($trafficStats, 'traffic') : [];
$trafficCounts = !empty($trafficStats) ? array_column($trafficStats, 'count') : [];

$surfaceLabels = !empty($surfaceStats) ? array_column($surfaceStats, 'surface') : [];
$surfaceCounts = !empty($surfaceStats) ? array_column($surfaceStats, 'count') : [];

include 'templates/report.html';
