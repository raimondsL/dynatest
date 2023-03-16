<?php

namespace App;

class TemperatureService
{
    public function __construct(
        private $database = null
    )
    {
        if (!$this->database) {
            $this->database = new SQLiteConnection();
        }
    }

    public function averageTempFromAllSensors(string $from, string $to): array
    {
        $sql = "SELECT AVG(temperature) FROM log_report WHERE datetime(datetime, 'unixepoch', 'localtime') BETWEEN date(:from, 'localtime') AND date(:to, 'localtime')";
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':from', $from);
        $stmt->bindValue(':to', $to);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function averageTempFromSensor(string $sensor_uuid): array
    {
        $sql = "SELECT strftime('%Y-%m-%d %H:00', datetime, 'unixepoch', 'localtime'), AVG(temperature) FROM log_report WHERE sensor_uuid=:sensor_uuid GROUP BY strftime('%Y-%m-%d %H', datetime, 'unixepoch', 'localtime')";
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':sensor_uuid', $sensor_uuid);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}