<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$apiUrl = $_POST['api_url'] ?? $_GET['api_url'] ?? '';
$method = $_POST['method'] ?? $_GET['method'] ?? 'GET';
$endpoint = $_POST['endpoint'] ?? $_GET['endpoint'] ?? '';
$body = $_POST['body'] ?? '';
$accessToken = $_POST['accessToken'] ?? '';

if (empty($apiUrl) || empty($endpoint)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing api_url or endpoint']);
    exit();
}

$fullUrl = rtrim($apiUrl, '/') . '/' . ltrim($endpoint, '/');

$curl = curl_init();

curl_setopt($curl, CURLOPT_URL, $fullUrl);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$headers = [
    'Accept: application/json',
];

if (!empty($accessToken)) {
    $headers[] = 'Authorization: Bearer ' . $accessToken;
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filePath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    
    $mime = mime_content_type($filePath);
    $fileData = file_get_contents($filePath);
    
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    
    $postData = "--$delimiter\r\n"
              . "Content-Disposition: form-data; name=\"file\"; filename=\"$fileName\"\r\n"
              . "Content-Type: $mime\r\n\r\n"
              . $fileData . "\r\n";
    
    if (isset($_POST['model']) && !empty($_POST['model'])) {
        $postData .= "--$delimiter\r\n"
                   . "Content-Disposition: form-data; name=\"model\"\r\n\r\n"
                   . $_POST['model'] . "\r\n";
    }
    
    $postData .= "--$delimiter--\r\n";
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    $headers[] = 'Content-Type: multipart/form-data; boundary=' . $delimiter;
} else if (!empty($body) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Content-Length: ' . strlen($body);
}

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);

curl_close($curl);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Curl error: ' . $error]);
    exit();
}

$contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
if (strpos($contentType, 'application/json') !== false || strpos($response, '{') === 0 || strpos($response, '[') === 0) {
    header('Content-Type: application/json');
} else {
    header('Content-Type: text/plain');
}

http_response_code($httpCode);
echo $response;
?>