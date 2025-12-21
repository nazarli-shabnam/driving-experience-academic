<?php
declare(strict_types=1);

class DrivingExperience
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function save(
        string $date,
        string $startTime,
        string $endTime,
        float $km,
        int $journeyId,
        int $surfaceId,
        int $trafficId,
        array $weatherIds
    ): void {
        if (empty($weatherIds)) {
            throw new InvalidArgumentException("At least one weather condition is required.");
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO driving_experience
                (driving_date, start_time, end_time, mileage_km, id_journey, id_surface, id_traffic)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $date,
                $startTime,
                $endTime,
                $km,
                $journeyId,
                $surfaceId,
                $trafficId
            ]);

            $experienceId = (int)$this->pdo->lastInsertId();

            if ($experienceId <= 0) {
                throw new RuntimeException("Failed to get experience ID.");
            }

            $stmtWeather = $this->pdo->prepare("
                INSERT INTO experience_weather (experience_id, weather_id)
                VALUES (?, ?)
            ");

            foreach ($weatherIds as $weatherId) {
                $weatherIdInt = (int)$weatherId;
                if ($weatherIdInt <= 0) {
                    throw new InvalidArgumentException("Invalid weather ID: $weatherId");
                }
                $stmtWeather->execute([$experienceId, $weatherIdInt]);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
