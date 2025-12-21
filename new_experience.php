<?php
declare(strict_types=1);

require_once 'inc/config.php';
require_once 'inc/db.inc.php';
require_once 'inc/class.inc.php';

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
    error_log("Error loading reference data: " . $e->getMessage());
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date       = $_POST['driving_date'] ?? '';
    $startTime  = $_POST['start_time'] ?? '';
    $endTime    = $_POST['end_time'] ?? '';
    $km         = $_POST['mileage_km'] ?? '';
    $journeyId  = $_POST['journey'] ?? '';
    $surfaceId  = $_POST['surface'] ?? '';
    $trafficId  = $_POST['traffic'] ?? '';
    $weatherIds = $_POST['weather'] ?? [];

    if ($date === '') {
        $errors[] = "Driving date is required.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
        $errors[] = "Invalid date format.";
    } elseif (strtotime($date) > time()) {
        $errors[] = "Date cannot be in the future.";
    }
    
    if ($startTime === '') {
        $errors[] = "Start time is required.";
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $startTime)) {
        $errors[] = "Invalid time format.";
    }
    
    if ($endTime === '') {
        $errors[] = "End time is required.";
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $endTime)) {
        $errors[] = "Invalid time format.";
    } elseif ($date !== '' && $startTime !== '' && strtotime($date . ' ' . $endTime) <= strtotime($date . ' ' . $startTime)) {
        $errors[] = "End time must be after start time.";
    }
    
    if ($km === '' || !is_numeric($km) || (float)$km <= 0) {
        $errors[] = "Mileage must be a positive number.";
    } elseif ((float)$km > 10000) {
        $errors[] = "Mileage seems too high. Please verify.";
    }
    
    if ($journeyId === '' || !is_numeric($journeyId)) {
        $errors[] = "Journey type is required.";
    }
    
    if ($surfaceId === '' || !is_numeric($surfaceId)) {
        $errors[] = "Road surface is required.";
    }
    
    if ($trafficId === '' || !is_numeric($trafficId)) {
        $errors[] = "Traffic type is required.";
    }
    
    if (empty($weatherIds) || !is_array($weatherIds)) {
        $errors[] = "Select at least one weather condition.";
    } else {
        if (empty($weatherList)) {
            $errors[] = "Weather conditions not loaded. Please refresh the page.";
        } else {
            $validWeatherIds = array_map('intval', array_column($weatherList, 'id'));
            foreach ($weatherIds as $wId) {
                $wIdInt = (int)$wId;
                if ($wIdInt <= 0 || !in_array($wIdInt, $validWeatherIds, true)) {
                    $errors[] = "Invalid weather condition selected.";
                    break;
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $experience = new DrivingExperience($pdo);
            $experience->save(
                $date,
                $startTime,
                $endTime,
                (float)$km,
                (int)$journeyId,
                (int)$surfaceId,
                (int)$trafficId,
                array_map('intval', $weatherIds)
            );
            $success = true;
            $_SESSION['last_experience_date'] = $date;
            $_SESSION['experience_count'] = ($_SESSION['experience_count'] ?? 0) + 1;
        } catch (Exception $e) {
            $errors[] = "Error saving experience. Please try again.";
            error_log("Error saving experience: " . $e->getMessage());
        }
    }
}

$today = date('Y-m-d');
$now   = date('H:i');
include 'templates/new_experience.html';
