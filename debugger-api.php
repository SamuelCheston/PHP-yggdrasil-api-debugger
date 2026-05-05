<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$action = $input['action'];

if ($action === 'proxy') {
    $url = $input['url'] ?? '';
    $method = strtoupper($input['method'] ?? 'GET');
    $headers = $input['headers'] ?? [];
    $body = $input['body'] ?? null;

    if (empty($url)) {
        echo json_encode(['error' => 'URL is required']);
        exit;
    }

    $startTime = microtime(true);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $requestHeaders = [];
    foreach ($headers as $key => $value) {
        $requestHeaders[] = "$key: $value";
    }
    if (!empty($requestHeaders)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
    }

    if ($body && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body) : $body);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseTime = round((microtime(true) - $startTime) * 1000);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        echo json_encode([
            'error' => true,
            'message' => $error,
            'http_code' => 0,
            'response_time_ms' => $responseTime
        ]);
        exit;
    }

    $contentType = '';
    if (preg_match('/Content-Type:\s*([^\s]+)/i', $response, $matches)) {
        $contentType = $matches[1];
    }

    $decodedResponse = null;
    $responseText = $response;

    if (strpos($contentType, 'application/json') !== false) {
        $decodedResponse = json_decode($response, true);
    }

    echo json_encode([
        'http_code' => $httpCode,
        'response_time_ms' => $responseTime,
        'content_type' => $contentType,
        'response' => $decodedResponse ?? $responseText,
        'raw_response' => $response
    ]);
    exit;
}

if ($action === 'db_query') {
    $host = $input['host'] ?? 'localhost';
    $db = $input['db'] ?? '';
    $user = $input['user'] ?? 'root';
    $pass = $input['pass'] ?? '';
    $sql = $input['sql'] ?? '';

    if (empty($sql)) {
        echo json_encode(['error' => 'SQL query is required']);
        exit;
    }

    try {
        $mysqli = new mysqli($host, $user, $pass, $db);

        if ($mysqli->connect_error) {
            echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
            exit;
        }

        $result = $mysqli->query($sql);

        if ($result === false) {
            echo json_encode(['error' => 'Query failed: ' . $mysqli->error]);
            exit;
        }

        if ($result === true) {
            echo json_encode([
                'affected_rows' => $mysqli->affected_rows,
                'insert_id' => $mysqli->insert_id,
                'message' => 'Query executed successfully'
            ]);
        } else {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
            echo json_encode([
                'rows' => $rows,
                'num_rows' => count($rows)
            ]);
        }

        $mysqli->close();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Unknown action']);
