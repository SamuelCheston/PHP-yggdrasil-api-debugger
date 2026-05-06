const API_CONFIG = {
    login: { url: '/login', method: 'POST' },
    register: { url: '/register', method: 'POST' },
    user: { url: '/user', method: 'POST' },
    'email-verification-send': { url: '/email-verification', method: 'POST', action: 'send-verification-code' },
    'email-verification-verify': { url: '/email-verification', method: 'POST', action: 'verify-code' },
    'totp-setup': { url: '/totp/setup', method: 'POST' },
    'totp-verify': { url: '/totp/verify', method: 'POST' },
    'change-username': { url: '/change-username', method: 'POST' },
    'generate-key': { url: '/generate-key', method: 'POST' },
    status: { url: '/status', method: 'GET' },
    'zgg-authenticate': { url: '/authserver/authenticate', method: 'POST', isZgg: true },
    'zgg-refresh': { url: '/authserver/refresh', method: 'POST', isZgg: true },
    'zgg-validate': { url: '/authserver/validate', method: 'POST', isZgg: true },
    'zgg-invalidate': { url: '/authserver/invalidate', method: 'POST', isZgg: true },
    'zgg-signout': { url: '/authserver/signout', method: 'POST', isZgg: true },
    'zgg-join': { url: '/sessionserver/session/minecraft/join', method: 'POST', isZgg: true },
    'zgg-hasJoined': { url: '/sessionserver/session/minecraft/hasJoined', method: 'GET', isZgg: true },
    'zgg-profile': { url: '/sessionserver/session/minecraft/profile/', method: 'GET', isZgg: true },
    'zgg-meta': { url: '/', method: 'GET', isZgg: true },
    'zgg-upload-texture': { url: '/api/user/profile/', method: 'PUT', isZgg: true, isFileUpload: true },
    'zgg-delete-texture': { url: '/api/user/profile/', method: 'DELETE', isZgg: true }
};

let lastResponse = null;

document.addEventListener('DOMContentLoaded', () => {
    setupTabs();
    setupResponseTabs();
    setupForms();
    setupTestConnection();
});

function setupTabs() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.dataset.tab;
            
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

function setupResponseTabs() {
    const tabs = document.querySelectorAll('.response-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.dataset.responseTab;
            
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            document.getElementById('json-response').style.display = targetTab === 'json' ? 'block' : 'none';
            document.getElementById('raw-response').style.display = targetTab === 'raw' ? 'block' : 'none';
        });
    });
}

function setupForms() {
    const forms = document.querySelectorAll('.api-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const apiKey = form.dataset.api;
            await makeRequest(apiKey, form);
        });
    });
}

function setupTestConnection() {
    const button = document.getElementById('test-connection');
    button.addEventListener('click', async () => {
        const baseUrl = getBaseUrl();
        const startTime = Date.now();
        
        updateResponseStatus('loading', 'Testing...');
        
        try {
            const formData = new FormData();
            formData.append('api_url', baseUrl);
            formData.append('method', 'GET');
            formData.append('endpoint', '/status');
            
            const response = await fetch('proxy.php', {
                method: 'POST',
                body: formData
            });
            
            const duration = Date.now() - startTime;
            const data = await response.json();
            
            lastResponse = { raw: JSON.stringify(data, null, 2), parsed: data };
            displayResponse(lastResponse);
            updateResponseStatus('success', `Status: ${response.status}`);
            updateResponseTime(duration);
        } catch (error) {
            const duration = Date.now() - startTime;
            updateResponseStatus('error', 'Connection Failed');
            updateResponseTime(duration);
            displayRawResponse(`Error: ${error.message}`);
        }
    });
}

function getBaseUrl() {
    return document.getElementById('api-url').value.trim();
}

async function makeRequest(apiKey, form) {
    const config = API_CONFIG[apiKey];
    if (!config) return;

    const baseUrl = getBaseUrl();
    const startTime = Date.now();
    
    updateResponseStatus('loading', 'Sending...');
    
    try {
        let endpoint = config.url;
        let body = null;
        const params = new FormData(form);
        
        if (apiKey === 'zgg-upload-texture') {
            const uuid = params.get('uuid');
            const textureType = params.get('textureType');
            endpoint += `${uuid}/${textureType}`;
        } else if (apiKey === 'zgg-delete-texture') {
            const uuid = params.get('uuid');
            const textureType = params.get('textureType');
            endpoint += `${uuid}/${textureType}`;
        } else if (config.method === 'POST') {
            body = buildRequestBody(apiKey, params);
        } else if (config.method === 'GET') {
            if (apiKey === 'zgg-hasJoined') {
                const searchParams = new URLSearchParams();
                params.forEach((value, key) => {
                    if (value) searchParams.append(key, value);
                });
                endpoint += `?${searchParams.toString()}`;
            } else if (apiKey === 'zgg-profile') {
                const uuid = params.get('uuid');
                endpoint += `${uuid}`;
            }
        }
        
        const formData = new FormData();
        formData.append('api_url', baseUrl);
        formData.append('method', config.method);
        formData.append('endpoint', endpoint);
        
        if (apiKey === 'zgg-upload-texture') {
            const file = params.get('file');
            const model = params.get('model');
            const accessToken = params.get('accessToken');
            if (file) formData.append('file', file);
            if (model) formData.append('model', model);
            if (accessToken) formData.append('accessToken', accessToken);
        } else if (apiKey === 'zgg-delete-texture') {
            const accessToken = params.get('accessToken');
            if (accessToken) formData.append('accessToken', accessToken);
        } else if (body) {
            formData.append('body', JSON.stringify(body));
        }
        
        const response = await fetch('proxy.php', {
            method: 'POST',
            body: formData
        });
        
        const duration = Date.now() - startTime;
        updateResponseTime(duration);
        
        let responseText = '';
        try {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                lastResponse = { raw: JSON.stringify(data, null, 2), parsed: data };
                responseText = JSON.stringify(data, null, 2);
            } else {
                responseText = await response.text();
                lastResponse = { raw: responseText, parsed: null };
            }
        } catch {
            responseText = await response.text();
            lastResponse = { raw: responseText, parsed: null };
        }
        
        displayResponse(lastResponse);
        
        if (response.ok) {
            updateResponseStatus('success', `Status: ${response.status}`);
        } else {
            updateResponseStatus('error', `Status: ${response.status}`);
        }
        
    } catch (error) {
        const duration = Date.now() - startTime;
        updateResponseTime(duration);
        updateResponseStatus('error', 'Request Failed');
        displayRawResponse(`Error: ${error.message}`);
    }
}

function buildRequestBody(apiKey, params) {
    const body = {};
    
    if (apiKey === 'email-verification-send') {
        body.action = 'send-verification-code';
        body.email = params.get('email');
    } else if (apiKey === 'email-verification-verify') {
        body.action = 'verify-code';
        body.email = params.get('email');
        body.code = params.get('code');
    } else if (apiKey === 'zgg-authenticate') {
        body.username = params.get('username');
        body.password = params.get('password');
        body.agent = {
            name: "Minecraft",
            version: 1
        };
        if (params.get('clientToken')) {
            body.clientToken = params.get('clientToken');
        }
        if (params.get('requestUser') === 'on') {
            body.requestUser = true;
        }
    } else if (apiKey === 'zgg-refresh') {
        body.accessToken = params.get('accessToken');
        if (params.get('clientToken')) {
            body.clientToken = params.get('clientToken');
        }
    } else if (apiKey === 'zgg-validate' || apiKey === 'zgg-invalidate') {
        body.accessToken = params.get('accessToken');
        if (params.get('clientToken')) {
            body.clientToken = params.get('clientToken');
        }
    } else if (apiKey === 'zgg-signout') {
        body.username = params.get('username');
        body.password = params.get('password');
    } else if (apiKey === 'zgg-join') {
        body.accessToken = params.get('accessToken');
        body.selectedProfile = params.get('selectedProfile');
        body.serverId = params.get('serverId');
    } else {
        params.forEach((value, key) => {
            if (value) {
                body[key] = value;
            }
        });
    }
    
    return body;
}

function displayResponse(response) {
    if (response.parsed) {
        document.getElementById('json-response').textContent = response.raw;
        document.getElementById('raw-response').textContent = response.raw;
    } else {
        document.getElementById('json-response').textContent = 'Unable to parse as JSON';
        document.getElementById('raw-response').textContent = response.raw;
    }
}

function displayRawResponse(text) {
    document.getElementById('json-response').textContent = text;
    document.getElementById('raw-response').textContent = text;
}

function updateResponseStatus(status, text) {
    const statusEl = document.getElementById('response-status');
    statusEl.className = `status-${status}`;
    statusEl.textContent = text;
}

function updateResponseTime(ms) {
    document.getElementById('response-time').textContent = `${ms}ms`;
}

function copyToClipboard() {
    if (lastResponse) {
        navigator.clipboard.writeText(lastResponse.raw).then(() => {
            alert('Copied to clipboard!');
        });
    }
}