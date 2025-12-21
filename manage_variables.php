<?php
declare(strict_types=1);

require_once 'inc/config.php';
require_once 'inc/db.inc.php';

$errors = [];
$success = false;
$table = $_GET['table'] ?? 'weather';

$allowedTables = ['weather', 'journey_type', 'road_surface', 'traffic_type'];
if (!in_array($table, $allowedTables, true)) {
    $table = 'weather';
}

$tableLabels = [
    'weather' => 'Weather Conditions',
    'journey_type' => 'Journey Types',
    'road_surface' => 'Road Surfaces',
    'traffic_type' => 'Traffic Types'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $postTable = $_POST['table'] ?? '';
    
    if (!in_array($postTable, $allowedTables, true)) {
        $errors[] = "Invalid table.";
    } else {
        if ($postAction === 'add') {
            $label = trim($_POST['label'] ?? '');
            if (empty($label)) {
                $errors[] = "Label is required.";
            } elseif (strlen($label) > 50) {
                $errors[] = "Label must be 50 characters or less.";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO `{$postTable}` (label) VALUES (?)");
                    $stmt->execute([$label]);
                    $success = true;
                    $_SESSION['last_variable_added'] = $label;
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $errors[] = "This label already exists.";
                    } else {
                        $errors[] = "Error adding variable.";
                    }
                }
            }
        } elseif ($postAction === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $label = trim($_POST['label'] ?? '');
            if ($id <= 0) {
                $errors[] = "Invalid ID.";
            } elseif (empty($label)) {
                $errors[] = "Label is required.";
            } elseif (strlen($label) > 50) {
                $errors[] = "Label must be 50 characters or less.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE `{$postTable}` SET label = ? WHERE id = ?");
                    $stmt->execute([$label, $id]);
                    $success = true;
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $errors[] = "This label already exists.";
                    } else {
                        $errors[] = "Error updating variable.";
                    }
                }
            }
        } elseif ($postAction === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                $errors[] = "Invalid ID.";
            } else {
                try {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM driving_experience WHERE id_{$postTable} = ?");
                    if ($postTable === 'weather') {
                        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM experience_weather WHERE weather_id = ?");
                    }
                    $checkStmt->execute([$id]);
                    $inUse = $checkStmt->fetchColumn() > 0;
                    
                    if ($inUse) {
                        $errors[] = "Cannot delete: this variable is in use by existing experiences.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM `{$postTable}` WHERE id = ?");
                        $stmt->execute([$id]);
                        $success = true;
                    }
                } catch (PDOException $e) {
                    $errors[] = "Error deleting variable.";
                }
            }
        }
    }
}
try {
    $items = $pdo->query("SELECT id, label FROM `{$table}` ORDER BY label")->fetchAll();
} catch (PDOException $e) {
    $items = [];
    $errors[] = "Error loading items from database.";
    error_log("Error loading items: " . $e->getMessage());
}

$editId = (int)($_GET['edit'] ?? 0);
$editItem = null;
if ($editId > 0) {
    try {
        $editStmt = $pdo->prepare("SELECT id, label FROM `{$table}` WHERE id = ?");
        $editStmt->execute([$editId]);
        $editItem = $editStmt->fetch();
        if ($editItem === false) {
            $editItem = null;
        }
    } catch (PDOException $e) {
        $editItem = null;
        error_log("Error loading edit item: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Variables - Driving Experience</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="main-container">
    <h1>Manage Variables</h1>

    <?php if ($success): ?>
        <div class="message success-message">
            <span class="message-icon">✓</span>
            <p>Operation completed successfully!</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="message error-message">
            <span class="message-icon">✗</span>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <nav style="margin: 30px 0; display: flex; gap: 10px; flex-wrap: wrap;">
        <?php foreach ($allowedTables as $t): ?>
            <a href="?table=<?= urlencode($t) ?>" 
               style="padding: 10px 20px; background: <?= $table === $t ? '#ff376c' : '#2c2c2c' ?>; 
                      color: #e0e0e0; text-decoration: none; border-radius: 10px;"
               class="red-button">
                <?= htmlspecialchars($tableLabels[$t]) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="report-block">
        <h2><?= htmlspecialchars($tableLabels[$table]) ?></h2>

        <?php if ($editItem): ?>
            <form method="post" style="margin-bottom: 30px; padding: 20px; background: #3a3a3a; border-radius: 10px;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                <label>Edit Label:</label>
                <input type="text" name="label" value="<?= htmlspecialchars($editItem['label']) ?>" 
                       maxlength="50" required style="width: 100%; margin-bottom: 15px;">
                <button type="submit" class="red-button">Update</button>
                <a href="?table=<?= urlencode($table) ?>" class="red-button" style="display: inline-block; margin-left: 10px; text-align: center;">Cancel</a>
            </form>
        <?php else: ?>
            <form method="post" style="margin-bottom: 30px; padding: 20px; background: #3a3a3a; border-radius: 10px;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                <label>Add New Label:</label>
                <input type="text" name="label" maxlength="50" required 
                       placeholder="Enter new <?= strtolower($tableLabels[$table]) ?>" 
                       style="width: 100%; margin-bottom: 15px;">
                <button type="submit" class="red-button">Add</button>
            </form>
        <?php endif; ?>

        <table class="records-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Label</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= htmlspecialchars($item['label']) ?></td>
                        <td>
                            <a href="?table=<?= urlencode($table) ?>&edit=<?= $item['id'] ?>" 
                               style="color: #ff5784; text-decoration: none; margin-right: 15px;">Edit</a>
                            <form method="post" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this item?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #ff7c7c; cursor: pointer; text-decoration: underline;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <a href="index.php" class="red-button back-to-home">Back to Home</a>
</main>

</body>
</html>

