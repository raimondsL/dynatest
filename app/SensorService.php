<?php

namespace App;

class SensorService
{
    public function __construct(
        private $database = null
    )
    {
        if (!$this->database) {
            $this->database = new SQLiteConnection();
        }
    }

    public function insertLogReport(array $data): null|int
    {
        $sql = 'INSERT INTO log_report(sensor_uuid, temperature, datetime) VALUES(:sensor_uuid, :temperature, :datetime)';
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':sensor_uuid', $data['sensor_uuid']);
        $stmt->bindValue(':temperature', $data['temperature']);
        $stmt->bindValue(':datetime', time());
        $stmt->execute();

        return $this->database->lastInsertId();
    }

    public function getSensor(string $uuid): mixed
    {
        $sql = 'SELECT * FROM sensors WHERE uuid=:uuid LIMIT 1';
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getSensorData(string $uuid): mixed
    {
        $sensor = $this->getSensor($uuid);

        if (!$sensor) {
            return null;
        }

        $url = $sensor['ip'] .'/data';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        curl_close($curl);

        $data = explode(',', $response);

        if (isset($data[0], $data[1])) {
            $this->insertLogReport([
                'sensor_uuid' => $uuid,
                'temperature' => $data[1]
            ]);
        }

        return $this->database->lastInsertId();
    }
}