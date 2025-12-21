<?php
declare(strict_types=1);

require_once 'inc/config.php';
require_once 'inc/db.inc.php';

$_SESSION['dashboard_visits'] = ($_SESSION['dashboard_visits'] ?? 0) + 1;
$_SESSION['last_dashboard_visit'] = date('Y-m-d H:i:s');

$sql = "
    SELECT 
        e.id,
        e.driving_date,
        e.start_time,
        e.end_time,
        e.mileage_km,
        jt.label AS journey,
        rs.label AS surface,
        tt.label AS traffic,
        GROUP_CONCAT(w.label SEPARATOR ', ') AS weather
    FROM driving_experience e
    JOIN journey_type jt ON e.id_journey = jt.id
    JOIN road_surface rs ON e.id_surface = rs.id
    JOIN traffic_type tt ON e.id_traffic = tt.id
    LEFT JOIN experience_weather ew ON e.id = ew.experience_id
    LEFT JOIN weather w ON ew.weather_id = w.id
    GROUP BY e.id
    ORDER BY e.driving_date DESC, e.start_time DESC
";

try {
    $stmt = $pdo->query($sql);
    $experiences = $stmt->fetchAll();
} catch (PDOException $e) {
    $experiences = [];
    error_log("Error loading experiences: " . $e->getMessage());
}

try {
    $totalKmStmt = $pdo->query("SELECT SUM(mileage_km) AS total_km FROM driving_experience");
    $totalKmRow  = $totalKmStmt->fetch();
    $totalKm     = (float)($totalKmRow['total_km'] ?? 0);
} catch (PDOException $e) {
    $totalKm = 0;
    error_log("Error calculating total km: " . $e->getMessage());
}

include 'templates/dashboard.html';
