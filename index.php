<?php

// Main entry point for Yggdrasil API Server

require_once __DIR__ . '/src/utils/helpers.php';
require_once __DIR__ . '/src/utils/database.php';

// Get the request URI
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = explode('?', $uri)[0];

// Route the request
switch ($uri) {
    // Meta endpoint
    case '/':
        if ($method === 'GET') {
            require __DIR__ . '/src/meta.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    // Authentication endpoints
    case '/authserver/authenticate':
        if ($method === 'POST') {
            require __DIR__ . '/src/auth/authenticate.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    case '/authserver/refresh':
        if ($method === 'POST') {
            require __DIR__ . '/src/auth/refresh.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    case '/authserver/validate':
        if ($method === 'POST') {
            require __DIR__ . '/src/auth/validate.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    case '/authserver/invalidate':
        if ($method === 'POST') {
            require __DIR__ . '/src/auth/invalidate.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    case '/authserver/signout':
        if ($method === 'POST') {
            require __DIR__ . '/src/auth/signout.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    // Session endpoints
    case '/sessionserver/session/minecraft/join':
        if ($method === 'POST') {
            require __DIR__ . '/src/session/join.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    case '/sessionserver/session/minecraft/hasJoined':
        if ($method === 'GET') {
            require __DIR__ . '/src/session/hasJoined.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    // Profile endpoints
    case '/sessionserver/session/minecraft/profile':
        if ($method === 'GET') {
            require __DIR__ . '/src/profile/profileQuery.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    case '/api/profiles/minecraft':
        if ($method === 'POST') {
            require __DIR__ . '/src/profile/batchProfiles.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    // Texture endpoints
    case preg_match('/^\/api\/user\/profile\/[0-9a-f]{32}\/(skin|cape)$/i', $uri) ? true : false:
        if ($method === 'PUT') {
            require __DIR__ . '/src/texture/uploadTexture.php';
        } elseif ($method === 'DELETE') {
            require __DIR__ . '/src/texture/deleteTexture.php';
        } else {
            sendErrorResponse('Method Not Allowed', 'The method specified is not allowed.', null, 405);
        }
        break;
    
    // Handle 404
    default:
        sendErrorResponse('Not Found', 'The requested endpoint does not exist.', null, 404);
        break;
}
