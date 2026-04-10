<?php
// Yggdrasil API Test Page
// Tests various Yggdrasil API endpoints using curl()

// Default Yggdrasil server URL
$defaultServer = 'https://authserver.mojang.com';

// Get server URL from POST or use default
$serverUrl = isset($_POST['serverUrl']) ? rtrim($_POST['serverUrl'], '/') : $defaultServer;

// Get selected endpoint from POST
$selectedEndpoint = isset($_POST['endpoint']) ? $_POST['endpoint'] : '';

// Get form data
$formData = $_POST;

// Function to make curl request
function makeCurlRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $headers[] = 'Content-Type: application/json';
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'httpCode' => $httpCode,
        'headers' => $headers,
        'body' => $body
    ];
}

// Function to test authenticate endpoint
function testAuthenticate($serverUrl, $data) {
    $url = $serverUrl . '/authserver/authenticate';
    $requestData = [
        'username' => isset($data['username']) ? $data['username'] : '',
        'password' => isset($data['password']) ? $data['password'] : '',
        'agent' => [
            'name' => 'Minecraft',
            'version' => 1
        ]
    ];
    
    if (isset($data['clientToken'])) {
        $requestData['clientToken'] = $data['clientToken'];
    }
    
    if (isset($data['requestUser'])) {
        $requestData['requestUser'] = filter_var($data['requestUser'], FILTER_VALIDATE_BOOLEAN);
    }
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test refresh endpoint
function testRefresh($serverUrl, $data) {
    $url = $serverUrl . '/authserver/refresh';
    $requestData = [
        'accessToken' => isset($data['accessToken']) ? $data['accessToken'] : ''
    ];
    
    if (isset($data['clientToken'])) {
        $requestData['clientToken'] = $data['clientToken'];
    }
    
    if (isset($data['requestUser'])) {
        $requestData['requestUser'] = filter_var($data['requestUser'], FILTER_VALIDATE_BOOLEAN);
    }
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test validate endpoint
function testValidate($serverUrl, $data) {
    $url = $serverUrl . '/authserver/validate';
    $requestData = [
        'accessToken' => isset($data['accessToken']) ? $data['accessToken'] : ''
    ];
    
    if (isset($data['clientToken'])) {
        $requestData['clientToken'] = $data['clientToken'];
    }
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test invalidate endpoint
function testInvalidate($serverUrl, $data) {
    $url = $serverUrl . '/authserver/invalidate';
    $requestData = [
        'accessToken' => isset($data['accessToken']) ? $data['accessToken'] : ''
    ];
    
    if (isset($data['clientToken'])) {
        $requestData['clientToken'] = $data['clientToken'];
    }
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test signout endpoint
function testSignout($serverUrl, $data) {
    $url = $serverUrl . '/authserver/signout';
    $requestData = [
        'username' => isset($data['username']) ? $data['username'] : '',
        'password' => isset($data['password']) ? $data['password'] : ''
    ];
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test join endpoint
function testJoin($serverUrl, $data) {
    $url = $serverUrl . '/sessionserver/session/minecraft/join';
    $requestData = [
        'accessToken' => isset($data['accessToken']) ? $data['accessToken'] : '',
        'selectedProfile' => isset($data['selectedProfile']) ? $data['selectedProfile'] : '',
        'serverId' => isset($data['serverId']) ? $data['serverId'] : ''
    ];
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test hasJoined endpoint
function testHasJoined($serverUrl, $data) {
    $username = isset($data['username']) ? urlencode($data['username']) : '';
    $serverId = isset($data['serverId']) ? urlencode($data['serverId']) : '';
    $ip = isset($data['ip']) ? '&ip=' . urlencode($data['ip']) : '';
    $url = $serverUrl . "/sessionserver/session/minecraft/hasJoined?username={$username}&serverId={$serverId}{$ip}";
    
    return makeCurlRequest($url);
}

// Function to test profile query endpoint
function testProfileQuery($serverUrl, $data) {
    $uuid = isset($data['uuid']) ? $data['uuid'] : '';
    $unsigned = isset($data['unsigned']) ? '&unsigned=' . urlencode($data['unsigned']) : '';
    $url = $serverUrl . "/sessionserver/session/minecraft/profile/{$uuid}{$unsigned}";
    
    return makeCurlRequest($url);
}

// Function to test batch profiles endpoint
function testBatchProfiles($serverUrl, $data) {
    $url = $serverUrl . '/api/profiles/minecraft';
    $requestData = isset($data['profiles']) ? explode(',', $data['profiles']) : [];
    
    return makeCurlRequest($url, 'POST', $requestData);
}

// Function to test upload texture endpoint
function testUploadTexture($serverUrl, $data) {
    $uuid = isset($data['uuid']) ? $data['uuid'] : '';
    $textureType = isset($data['textureType']) ? $data['textureType'] : 'skin';
    $url = $serverUrl . "/api/user/profile/{$uuid}/{$textureType}";
    
    $headers = [];
    if (isset($data['accessToken'])) {
        $headers[] = 'Authorization: Bearer ' . $data['accessToken'];
    }
    
    // Note: File uploads would require special handling
    return makeCurlRequest($url, 'PUT', null, $headers);
}

// Function to test delete texture endpoint
function testDeleteTexture($serverUrl, $data) {
    $uuid = isset($data['uuid']) ? $data['uuid'] : '';
    $textureType = isset($data['textureType']) ? $data['textureType'] : 'skin';
    $url = $serverUrl . "/api/user/profile/{$uuid}/{$textureType}";
    
    $headers = [];
    if (isset($data['accessToken'])) {
        $headers[] = 'Authorization: Bearer ' . $data['accessToken'];
    }
    
    return makeCurlRequest($url, 'DELETE', null, $headers);
}

// Function to test meta endpoint
function testMeta($serverUrl) {
    $url = $serverUrl . '/';
    return makeCurlRequest($url);
}

// Handle form submission
$response = null;
if ($selectedEndpoint) {
    switch ($selectedEndpoint) {
        case 'authenticate':
            $response = testAuthenticate($serverUrl, $formData);
            break;
        case 'refresh':
            $response = testRefresh($serverUrl, $formData);
            break;
        case 'validate':
            $response = testValidate($serverUrl, $formData);
            break;
        case 'invalidate':
            $response = testInvalidate($serverUrl, $formData);
            break;
        case 'signout':
            $response = testSignout($serverUrl, $formData);
            break;
        case 'join':
            $response = testJoin($serverUrl, $formData);
            break;
        case 'hasJoined':
            $response = testHasJoined($serverUrl, $formData);
            break;
        case 'profileQuery':
            $response = testProfileQuery($serverUrl, $formData);
            break;
        case 'batchProfiles':
            $response = testBatchProfiles($serverUrl, $formData);
            break;
        case 'uploadTexture':
            $response = testUploadTexture($serverUrl, $formData);
            break;
        case 'deleteTexture':
            $response = testDeleteTexture($serverUrl, $formData);
            break;
        case 'meta':
            $response = testMeta($serverUrl);
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yggdrasil API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="checkbox"] {
            margin-right: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .response {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .response h3 {
            margin-top: 0;
        }
        .code {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: monospace;
        }
        .http-code {
            font-weight: bold;
            color: #333;
        }
        .endpoint-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
        .endpoint-section.active {
            display: block;
        }
    </style>
    <script>
        // Show/hide endpoint-specific form fields
        function showEndpointForm() {
            const endpoint = document.getElementById('endpoint').value;
            const sections = document.querySelectorAll('.endpoint-section');
            
            sections.forEach(section => {
                section.classList.remove('active');
            });
            
            if (endpoint) {
                const section = document.getElementById(endpoint + '-section');
                if (section) {
                    section.classList.add('active');
                }
            }
        }
        
        // Initialize on page load
        window.onload = function() {
            showEndpointForm();
        };
    </script>
</head>
<body>
    <div class="container">
        <h1>Yggdrasil API Test</h1>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="serverUrl">Server URL:</label>
                <input type="text" id="serverUrl" name="serverUrl" value="<?php echo htmlspecialchars($serverUrl); ?>" placeholder="e.g., https://authserver.mojang.com">
            </div>
            
            <div class="form-group">
                <label for="endpoint">Select Endpoint:</label>
                <select id="endpoint" name="endpoint" onchange="showEndpointForm()">
                    <option value="">-- Select Endpoint --</option>
                    <option value="meta" <?php echo $selectedEndpoint === 'meta' ? 'selected' : ''; ?>>Meta (GET /)</option>
                    <option value="authenticate" <?php echo $selectedEndpoint === 'authenticate' ? 'selected' : ''; ?>>Authenticate (POST /authserver/authenticate)</option>
                    <option value="refresh" <?php echo $selectedEndpoint === 'refresh' ? 'selected' : ''; ?>>Refresh (POST /authserver/refresh)</option>
                    <option value="validate" <?php echo $selectedEndpoint === 'validate' ? 'selected' : ''; ?>>Validate (POST /authserver/validate)</option>
                    <option value="invalidate" <?php echo $selectedEndpoint === 'invalidate' ? 'selected' : ''; ?>>Invalidate (POST /authserver/invalidate)</option>
                    <option value="signout" <?php echo $selectedEndpoint === 'signout' ? 'selected' : ''; ?>>Signout (POST /authserver/signout)</option>
                    <option value="join" <?php echo $selectedEndpoint === 'join' ? 'selected' : ''; ?>>Join (POST /sessionserver/session/minecraft/join)</option>
                    <option value="hasJoined" <?php echo $selectedEndpoint === 'hasJoined' ? 'selected' : ''; ?>>Has Joined (GET /sessionserver/session/minecraft/hasJoined)</option>
                    <option value="profileQuery" <?php echo $selectedEndpoint === 'profileQuery' ? 'selected' : ''; ?>>Profile Query (GET /sessionserver/session/minecraft/profile/{uuid})</option>
                    <option value="batchProfiles" <?php echo $selectedEndpoint === 'batchProfiles' ? 'selected' : ''; ?>>Batch Profiles (POST /api/profiles/minecraft)</option>
                    <option value="uploadTexture" <?php echo $selectedEndpoint === 'uploadTexture' ? 'selected' : ''; ?>>Upload Texture (PUT /api/user/profile/{uuid}/{textureType})</option>
                    <option value="deleteTexture" <?php echo $selectedEndpoint === 'deleteTexture' ? 'selected' : ''; ?>>Delete Texture (DELETE /api/user/profile/{uuid}/{textureType})</option>
                </select>
            </div>
            
            <!-- Meta Endpoint -->
            <div id="meta-section" class="endpoint-section <?php echo $selectedEndpoint === 'meta' ? 'active' : ''; ?>">
                <h3>Meta Endpoint</h3>
                <p>Tests the root endpoint to get server metadata.</p>
            </div>
            
            <!-- Authenticate Endpoint -->
            <div id="authenticate-section" class="endpoint-section <?php echo $selectedEndpoint === 'authenticate' ? 'active' : ''; ?>">
                <h3>Authenticate Endpoint</h3>
                <div class="form-group">
                    <label for="username">Username/Email:</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo isset($formData['password']) ? htmlspecialchars($formData['password']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="clientToken">Client Token (optional):</label>
                    <input type="text" id="clientToken" name="clientToken" value="<?php echo isset($formData['clientToken']) ? htmlspecialchars($formData['clientToken']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="requestUser" <?php echo isset($formData['requestUser']) && $formData['requestUser'] ? 'checked' : ''; ?>> Request User Info
                    </label>
                </div>
            </div>
            
            <!-- Refresh Endpoint -->
            <div id="refresh-section" class="endpoint-section <?php echo $selectedEndpoint === 'refresh' ? 'active' : ''; ?>">
                <h3>Refresh Endpoint</h3>
                <div class="form-group">
                    <label for="accessToken">Access Token:</label>
                    <input type="text" id="accessToken" name="accessToken" value="<?php echo isset($formData['accessToken']) ? htmlspecialchars($formData['accessToken']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="clientToken">Client Token (optional):</label>
                    <input type="text" id="clientToken" name="clientToken" value="<?php echo isset($formData['clientToken']) ? htmlspecialchars($formData['clientToken']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="requestUser" <?php echo isset($formData['requestUser']) && $formData['requestUser'] ? 'checked' : ''; ?>> Request User Info
                    </label>
                </div>
            </div>
            
            <!-- Validate Endpoint -->
            <div id="validate-section" class="endpoint-section <?php echo $selectedEndpoint === 'validate' ? 'active' : ''; ?>">
                <h3>Validate Endpoint</h3>
                <div class="form-group">
                    <label for="accessToken">Access Token:</label>
                    <input type="text" id="accessToken" name="accessToken" value="<?php echo isset($formData['accessToken']) ? htmlspecialchars($formData['accessToken']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="clientToken">Client Token (optional):</label>
                    <input type="text" id="clientToken" name="clientToken" value="<?php echo isset($formData['clientToken']) ? htmlspecialchars($formData['clientToken']) : ''; ?>">
                </div>
            </div>
            
            <!-- Invalidate Endpoint -->
            <div id="invalidate-section" class="endpoint-section <?php echo $selectedEndpoint === 'invalidate' ? 'active' : ''; ?>">
                <h3>Invalidate Endpoint</h3>
                <div class="form-group">
                    <label for="accessToken">Access Token:</label>
                    <input type="text" id="accessToken" name="accessToken" value="<?php echo isset($formData['accessToken']) ? htmlspecialchars($formData['accessToken']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="clientToken">Client Token (optional):</label>
                    <input type="text" id="clientToken" name="clientToken" value="<?php echo isset($formData['clientToken']) ? htmlspecialchars($formData['clientToken']) : ''; ?>">
                </div>
            </div>
            
            <!-- Signout Endpoint -->
            <div id="signout-section" class="endpoint-section <?php echo $selectedEndpoint === 'signout' ? 'active' : ''; ?>">
                <h3>Signout Endpoint</h3>
                <div class="form-group">
                    <label for="username">Username/Email:</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo isset($formData['password']) ? htmlspecialchars($formData['password']) : ''; ?>">
                </div>
            </div>
            
            <!-- Join Endpoint -->
            <div id="join-section" class="endpoint-section <?php echo $selectedEndpoint === 'join' ? 'active' : ''; ?>">
                <h3>Join Endpoint</h3>
                <div class="form-group">
                    <label for="accessToken">Access Token:</label>
                    <input type="text" id="accessToken" name="accessToken" value="<?php echo isset($formData['accessToken']) ? htmlspecialchars($formData['accessToken']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="selectedProfile">Selected Profile UUID:</label>
                    <input type="text" id="selectedProfile" name="selectedProfile" value="<?php echo isset($formData['selectedProfile']) ? htmlspecialchars($formData['selectedProfile']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="serverId">Server ID:</label>
                    <input type="text" id="serverId" name="serverId" value="<?php echo isset($formData['serverId']) ? htmlspecialchars($formData['serverId']) : ''; ?>">
                </div>
            </div>
            
            <!-- Has Joined Endpoint -->
            <div id="hasJoined-section" class="endpoint-section <?php echo $selectedEndpoint === 'hasJoined' ? 'active' : ''; ?>">
                <h3>Has Joined Endpoint</h3>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($formData['username']) ? htmlspecialchars($formData['username']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="serverId">Server ID:</label>
                    <input type="text" id="serverId" name="serverId" value="<?php echo isset($formData['serverId']) ? htmlspecialchars($formData['serverId']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="ip">IP (optional):</label>
                    <input type="text" id="ip" name="ip" value="<?php echo isset($formData['ip']) ? htmlspecialchars($formData['ip']) : ''; ?>">
                </div>
            </div>
            
            <!-- Profile Query Endpoint -->
            <div id="profileQuery-section" class="endpoint-section <?php echo $selectedEndpoint === 'profileQuery' ? 'active' : ''; ?>">
                <h3>Profile Query Endpoint</h3>
                <div class="form-group">
                    <label for="uuid">Profile UUID:</label>
                    <input type="text" id="uuid" name="uuid" value="<?php echo isset($formData['uuid']) ? htmlspecialchars($formData['uuid']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="unsigned" <?php echo isset($formData['unsigned']) && $formData['unsigned'] ? 'checked' : ''; ?>> Unsigned
                    </label>
                </div>
            </div>
            
            <!-- Batch Profiles Endpoint -->
            <div id="batchProfiles-section" class="endpoint-section <?php echo $selectedEndpoint === 'batchProfiles' ? 'active' : ''; ?>">
                <h3>Batch Profiles Endpoint</h3>
                <div class="form-group">
                    <label for="profiles">Usernames (comma-separated):</label>
                    <input type="text" id="profiles" name="profiles" value="<?php echo isset($formData['profiles']) ? htmlspecialchars($formData['profiles']) : ''; ?>">
                </div>
            </div>
            
            <!-- Upload Texture Endpoint -->
            <div id="uploadTexture-section" class="endpoint-section <?php echo $selectedEndpoint === 'uploadTexture' ? 'active' : ''; ?>">
                <h3>Upload Texture Endpoint</h3>
                <div class="form-group">
                    <label for="uuid">Profile UUID:</label>
                    <input type="text" id="uuid" name="uuid" value="<?php echo isset($formData['uuid']) ? htmlspecialchars($formData['uuid']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="textureType">Texture Type:</label>
                    <select id="textureType" name="textureType">
                        <option value="skin" <?php echo (isset($formData['textureType']) && $formData['textureType'] === 'skin') ? 'selected' : ''; ?>>Skin</option>
                        <option value="cape" <?php echo (isset($formData['textureType']) && $formData['textureType'] === 'cape') ? 'selected' : ''; ?>>Cape</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="accessToken">Access Token (Bearer):</label>
                    <input type="text" id="accessToken" name="accessToken" value="<?php echo isset($formData['accessToken']) ? htmlspecialchars($formData['accessToken']) : ''; ?>">
                </div>
                <p><em>Note: File upload functionality not fully implemented in this test page</em></p>
            </div>
            
            <!-- Delete Texture Endpoint -->
            <div id="deleteTexture-section" class="endpoint-section <?php echo $selectedEndpoint === 'deleteTexture' ? 'active' : ''; ?>">
                <h3>Delete Texture Endpoint</h3>
                <div class="form-group">
                    <label for="uuid">Profile UUID:</label>
                    <input type="text" id="uuid" name="uuid" value="<?php echo isset($formData['uuid']) ? htmlspecialchars($formData['uuid']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="textureType">Texture Type:</label>
                    <select id="textureType" name="textureType">
                        <option value="skin" <?php echo (isset($formData['textureType']) && $formData['textureType'] === 'skin') ? 'selected' : ''; ?>>Skin</option>
                        <option value="cape" <?php echo (isset($formData['textureType']) && $formData['textureType'] === 'cape') ? 'selected' : ''; ?>>Cape</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="accessToken">Access Token (Bearer):</label>
                    <input type="text" id="accessToken" name="accessToken" value="<?php echo isset($formData['accessToken']) ? htmlspecialchars($formData['accessToken']) : ''; ?>">
                </div>
            </div>
            
            <button type="submit">Test Endpoint</button>
        </form>
        
        <?php if ($response): ?>
        <div class="response">
            <h3>Response</h3>
            <p class="http-code">HTTP Status: <?php echo $response['httpCode']; ?></p>
            <h4>Headers:</h4>
            <div class="code"><?php echo htmlspecialchars($response['headers']); ?></div>
            <h4>Body:</h4>
            <div class="code"><?php echo htmlspecialchars($response['body']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>