<?php

require 'vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST");


$method = $_SERVER["REQUEST_METHOD"];

try {
    match ($method) {
        'GET' => processGetRequests(),
        'POST' => processPostRequests(),
        default => response(405)
    };
} catch (Exception $e) {
    response(500, $e->getMessage());
}

function processGetRequests(): void
{
    $uri = getUri();
    $route = $uri[1] ?? '';

    match ($route) {
        'avg' => getAverageTempFromSensor(),
        'avg_all' => getAverageTempFromAllSensors(),
        'phpinfo' => phpinfo(),
        default => response(404)
    };
}

function processPostRequests(): void
{
    $uri = getUri();
    $route = $uri[1] ?? '';

    $route == 'api' || response(404);

    $route = $uri[2] ?? '';

    match ($route) {
        'readings' => processReadingsRequest(),
        default => response(404)
    };
}

function getAverageTempFromSensor(): void
{
    $temperatureService = new \App\TemperatureService();

    isset($_GET['sensor_uuid']) || response(400);

    $data = $temperatureService->averageTempFromSensor($_GET['sensor_uuid']);
    $response = array_map(
        fn($item) => [$item[0] => $item[1]],
        $data
    );

    response(body: json_encode($response));
}

function getAverageTempFromAllSensors(): void
{
    $temperatureService = new \App\TemperatureService();

    isset($_GET['from'], $_GET['to']) || response(400);

    response(body: $temperatureService->averageTempFromAllSensors($_GET['from'], $_GET['to'])[0] ?? 'N/A');
}

function processReadingsRequest(): void
{
    $input = getInput();
    $sensorService = new \App\SensorService();

    isset($input['reading'], $input['reading']['sensor_uuid'], $input['reading']['temperature']) || response(400);

    response(
        status: 200,
        body: (string)$sensorService->insertLogReport($input['reading'])
    );
}

function getInput(): mixed
{
    return json_decode(file_get_contents('php://input'), true);
}

function getUri(): array
{
    return explode( '/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
}

function response(int $status = 200, string $body = ''): void
{
    header("Content-Type: application/json; charset=UTF-8");

    $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';

    $header = match ($status) {
        400 => "$protocol $status Bad Request",
        401 => "$protocol $status Unauthorized",
        403 => "$protocol $status Forbidden",
        404 => "$protocol $status Not Found",
        405 => "$protocol $status Method Not Allowed",
        500 => "$protocol $status Internal Server Error",
        default => "$protocol 200 OK"
    };

    header($header);

    echo $body;

    exit();
}
