<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRPAuth & Yggdrasil API Debugger</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #1a1a2e;
            color: #eee;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            opacity: 0.8;
            font-size: 14px;
        }
        .main-container {
            display: flex;
            height: calc(100vh - 80px);
        }
        .sidebar {
            width: 250px;
            background-color: #16213e;
            padding: 20px 0;
            overflow-y: auto;
            border-right: 1px solid #2a2a4a;
        }
        .sidebar-section {
            margin-bottom: 20px;
        }
        .sidebar-title {
            padding: 10px 20px;
            font-size: 12px;
            text-transform: uppercase;
            color: #667eea;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .sidebar-item {
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-item:hover {
            background-color: #1a1a2e;
            border-left-color: #667eea;
        }
        .sidebar-item.active {
            background-color: #2a2a4a;
            border-left-color: #764ba2;
        }
        .sidebar-item .method {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-right: 8px;
        }
        .sidebar-item .method.get { background-color: #4CAF50; }
        .sidebar-item .method.post { background-color: #2196F3; }
        .sidebar-item .method.put { background-color: #FF9800; }
        .sidebar-item .method.delete { background-color: #F44336; }
        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .panel {
            background-color: #16213e;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .panel-header {
            background-color: #1a1a2e;
            padding: 15px 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #2a2a4a;
        }
        .panel-body {
            padding: 20px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
            font-size: 14px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: #1a1a2e;
            border: 1px solid #2a2a4a;
            border-radius: 4px;
            color: #fff;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: 'Consolas', monospace;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background-color: #2a2a4a;
            color: #fff;
        }
        .btn-secondary:hover {
            background-color: #3a3a5a;
        }
        .response-area {
            background-color: #0f0f1a;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Consolas', monospace;
            font-size: 13px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .response-area .success {
            color: #4CAF50;
        }
        .response-area .error {
            color: #F44336;
        }
        .response-area .info {
            color: #2196F3;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #2a2a4a;
            margin-bottom: 20px;
        }
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .tab:hover {
            background-color: #2a2a4a;
        }
        .tab.active {
            border-bottom-color: #667eea;
            background-color: #2a2a4a;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .log-entry {
            background-color: #1a1a2e;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 10px;
            border-left: 3px solid #667eea;
        }
        .log-entry .timestamp {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .log-entry .method {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-right: 10px;
        }
        .log-entry .url {
            color: #667eea;
            font-family: monospace;
        }
        .log-entry .status {
            float: right;
            font-weight: bold;
        }
        .log-entry .status.success { color: #4CAF50; }
        .log-entry .status.error { color: #F44336; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
        }
        .stat-card .label {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 5px;
        }
        .db-query {
            background-color: #0f0f1a;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-family: 'Consolas', monospace;
            font-size: 13px;
        }
        .db-query .query-time {
            color: #4CAF50;
            float: right;
        }
        .clear-btn {
            background-color: #F44336;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .clear-btn:hover {
            background-color: #d32f2f;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge.hrp { background-color: #667eea; }
        .badge.ygg { background-color: #764ba2; }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-state .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 HRPAuth & Yggdrasil API Debugger</h1>
        <div class="subtitle">API Testing | Request Logging | Database Debug</div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">📡 API 选择</div>
                <div class="sidebar-item active" onclick="switchAPI('hrp-auth')">
                    <span class="badge hrp">HRP</span> 认证系统
                </div>
                <div class="sidebar-item" onclick="switchAPI('yggdrasil')">
                    <span class="badge ygg">YGG</span> Yggdrasil API
                </div>
            </div>
            
            <div class="sidebar-section" id="hrp-endpoints">
                <div class="sidebar-title">HRPAuth 端点</div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/login', {email:'', password:''})">
                    <span class="method post">POST</span> /login
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/register', {email:'', username:'', password:''})">
                    <span class="method post">POST</span> /register
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/user', {remember_token:''})">
                    <span class="method post">POST</span> /user
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('GET', '/logout', {})">
                    <span class="method get">GET</span> /logout
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/email-verification', {action:'send-verification-code', email:''})">
                    <span class="method post">POST</span> /email-verification
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/totp/setup', {email:'', remtoken:''})">
                    <span class="method post">POST</span> /totp/setup
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/totp/verify', {email:'', passcode:''})">
                    <span class="method post">POST</span> /totp/verify
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/change-username', {remember_token:'', username:''})">
                    <span class="method post">POST</span> /change-username
                </div>
            </div>
            
            <div class="sidebar-section" id="ygg-endpoints" style="display:none;">
                <div class="sidebar-title">YggdrasilAPI 端点</div>
                <div class="sidebar-item" onclick="selectEndpoint('GET', '/', {})">
                    <span class="method get">GET</span> / (Meta)
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/authserver/authenticate', {username:'', password:'', agent:{name:'Minecraft',version:1}})">
                    <span class="method post">POST</span> /authserver/authenticate
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/authserver/refresh', {accessToken:'', clientToken:''})">
                    <span class="method post">POST</span> /authserver/refresh
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/authserver/validate', {accessToken:''})">
                    <span class="method post">POST</span> /authserver/validate
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/authserver/invalidate', {accessToken:'', clientToken:''})">
                    <span class="method post">POST</span> /authserver/invalidate
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/authserver/signout', {username:'', password:''})">
                    <span class="method post">POST</span> /authserver/signout
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/sessionserver/session/minecraft/join', {accessToken:'', selectedProfile:'', serverId:''})">
                    <span class="method post">POST</span> /sessionserver/session/minecraft/join
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('GET', '/sessionserver/session/minecraft/hasJoined', {username:'', serverId:'', ip:''})">
                    <span class="method get">GET</span> /sessionserver/session/minecraft/hasJoined
                </div>
                <div class="sidebar-item" onclick="selectEndpoint('POST', '/api/profiles/minecraft', [])">
                    <span class="method post">POST</span> /api/profiles/minecraft
                </div>
            </div>
        </div>
        
        <div class="content">
            <div class="tabs">
                <div class="tab active" onclick="switchTab('api-tester')">🔧 API Tester</div>
                <div class="tab" onclick="switchTab('request-log')">📋 Request Log</div>
                <div class="tab" onclick="switchTab('db-debug')">🗄️ DB Debug</div>
            </div>
            
            <div id="api-tester" class="tab-content active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="value" id="totalRequests">0</div>
                        <div class="label">总请求数</div>
                    </div>
                    <div class="stat-card">
                        <div class="value" id="successRate">0%</div>
                        <div class="label">成功率</div>
                    </div>
                    <div class="stat-card">
                        <div class="value" id="avgResponseTime">0ms</div>
                        <div class="label">平均响应</div>
                    </div>
                    <div class="stat-card">
                        <div class="value" id="lastStatus">-</div>
                        <div class="label">上次状态</div>
                    </div>
                </div>
                
                <div class="panel">
                    <div class="panel-header">
                        <span>📡 发送请求</span>
                        <span id="currentEndpoint" style="color:#667eea;font-size:14px;">选择一个端点</span>
                    </div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Base URL</label>
                                <input type="text" id="baseUrl" value="https://hrpauth.samuelcheston.com/" placeholder="API Base URL">
                            </div>
                            <div class="form-group">
                                <label>Method</label>
                                <select id="method">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Endpoint</label>
                            <input type="text" id="endpoint" placeholder="/api/endpoint" value="/">
                        </div>
                        <div class="form-group">
                            <label>Request Body (JSON)</label>
                            <textarea id="requestBody" placeholder='{"key": "value"}'></textarea>
                        </div>
                        <button class="btn btn-primary" onclick="sendRequest()">🚀 发送请求</button>
                    </div>
                </div>
                
                <div class="panel">
                    <div class="panel-header">
                        <span>📥 响应</span>
                        <span id="responseTime" style="color:#aaa;font-size:12px;"></span>
                    </div>
                    <div class="panel-body">
                        <div class="response-area" id="responseArea">
                            <span class="info">响应将显示在这里...</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="request-log" class="tab-content">
                <div class="panel">
                    <div class="panel-header">
                        <span>📋 请求日志</span>
                        <button class="clear-btn" onclick="clearLogs()">🗑️ 清空日志</button>
                    </div>
                    <div class="panel-body" id="logContainer">
                        <div class="empty-state">
                            <div class="icon">📭</div>
                            <div>暂无请求日志</div>
                            <div style="font-size:12px;margin-top:10px;">发送请求后日志将显示在这里</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="db-debug" class="tab-content">
                <div class="panel">
                    <div class="panel-header">
                        <span>🗄️ 数据库调试</span>
                    </div>
                    <div class="panel-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>数据库主机</label>
                                <input type="text" id="dbHost" value="localhost" placeholder="127.0.0.1">
                            </div>
                            <div class="form-group">
                                <label>数据库名</label>
                                <input type="text" id="dbName" placeholder="数据库名">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>用户名</label>
                                <input type="text" id="dbUser" placeholder="root">
                            </div>
                            <div class="form-group">
                                <label>密码</label>
                                <input type="password" id="dbPass" placeholder="密码">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>SQL 查询</label>
                            <textarea id="sqlQuery" placeholder="SELECT * FROM users LIMIT 10;"></textarea>
                        </div>
                        <button class="btn btn-primary" onclick="executeQuery()">▶️ 执行查询</button>
                        
                        <div style="margin-top:20px;">
                            <div style="color:#aaa;margin-bottom:10px;">查询结果:</div>
                            <div class="response-area" id="queryResult">
                                <span class="info">查询结果将显示在这里...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentAPI = 'hrp-auth';
        let requestHistory = [];
        let totalRequests = 0;
        let successCount = 0;
        let responseTimes = [];
        
        function switchAPI(api) {
            currentAPI = api;
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            event.target.closest('.sidebar-item').classList.add('active');
            
            if (api === 'hrp-auth') {
                document.getElementById('hrp-endpoints').style.display = 'block';
                document.getElementById('ygg-endpoints').style.display = 'none';
            } else {
                document.getElementById('hrp-endpoints').style.display = 'none';
                document.getElementById('ygg-endpoints').style.display = 'block';
            }
        }
        
        function switchTab(tabId) {
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            event.target.closest('.tab').classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }
        
        function selectEndpoint(method, endpoint, defaultBody) {
            document.getElementById('method').value = method;
            document.getElementById('endpoint').value = endpoint;
            document.getElementById('currentEndpoint').textContent = method + ' ' + endpoint;
            
            if (Object.keys(defaultBody).length > 0) {
                document.getElementById('requestBody').value = JSON.stringify(defaultBody, null, 2);
            } else {
                document.getElementById('requestBody').value = '';
            }
            
            switchTab('api-tester');
            document.querySelector('.tab').click();
        }
        
        async function sendRequest() {
            const baseUrl = document.getElementById('baseUrl').value.trim();
            const method = document.getElementById('method').value;
            const endpoint = document.getElementById('endpoint').value.trim();
            const body = document.getElementById('requestBody').value.trim();

            if (!baseUrl || !endpoint) {
                alert('请填写完整的 URL 和端点');
                return;
            }

            const fullUrl = baseUrl.endsWith('/') ? baseUrl + endpoint.replace(/^\//, '') : baseUrl + endpoint;

            const responseArea = document.getElementById('responseArea');

            responseArea.innerHTML = '<span class="info">发送请求中...</span>';

            let parsedBody = null;
            if (body) {
                try {
                    parsedBody = JSON.parse(body);
                } catch {
                    parsedBody = body;
                }
            }

            try {
                const response = await fetch('/debugger-api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'proxy',
                        url: fullUrl,
                        method: method,
                        headers: {'Content-Type': 'application/json'},
                        body: parsedBody
                    })
                });

                const data = await response.json();

                let formattedResponse;
                if (data.error) {
                    formattedResponse = data.message || 'Unknown error';
                    responseArea.innerHTML = `<span class="error">❌ 请求失败: ${formattedResponse}</span>`;
                    responseArea.className = 'response-area error';
                    addLogEntry(method, fullUrl, 'ERR', data.response_time_ms || 0, body, formattedResponse);
                    return;
                }

                if (data.response && typeof data.response === 'object') {
                    formattedResponse = JSON.stringify(data.response, null, 2);
                } else {
                    formattedResponse = data.raw_response || data.response || '(空响应)';
                }

                const statusClass = data.http_code >= 200 && data.http_code < 300 ? 'success' : 'error';
                const statusBadge = `<span class="status ${statusClass}">${data.http_code} (${data.response_time_ms}ms)</span>`;

                responseArea.innerHTML = `${statusBadge}\n${formattedResponse}`;
                responseArea.className = `response-area ${statusClass}`;

                document.getElementById('responseTime').textContent = `⏱️ ${data.response_time_ms}ms`;

                totalRequests++;
                responseTimes.push(data.response_time_ms);
                if (data.http_code >= 200 && data.http_code < 300) successCount++;

                updateStats();
                addLogEntry(method, fullUrl, data.http_code, data.response_time_ms, body, formattedResponse);

            } catch (error) {
                responseArea.innerHTML = `<span class="error">❌ 请求失败: ${error.message}</span>`;
                responseArea.className = 'response-area error';
                addLogEntry(method, fullUrl, 'ERR', 0, body, error.message);
            }
        }
        
        function updateStats() {
            document.getElementById('totalRequests').textContent = totalRequests;
            document.getElementById('successRate').textContent = totalRequests > 0 
                ? Math.round((successCount / totalRequests) * 100) + '%' 
                : '0%';
            document.getElementById('avgResponseTime').textContent = responseTimes.length > 0
                ? Math.round(responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length) + 'ms'
                : '0ms';
            document.getElementById('lastStatus').textContent = totalRequests > 0 
                ? (successCount === totalRequests ? '✓' : successCount > 0 ? '⚠' : '✗')
                : '-';
        }
        
        function addLogEntry(method, url, status, duration, requestBody, responseBody) {
            const logContainer = document.getElementById('logContainer');
            const emptyState = logContainer.querySelector('.empty-state');
            if (emptyState) emptyState.remove();
            
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            
            const statusClass = status >= 200 && status < 300 ? 'success' : 'error';
            const timestamp = new Date().toLocaleString('zh-CN');
            
            entry.innerHTML = `
                <div class="timestamp">${timestamp}</div>
                <span class="method ${method.toLowerCase()}">${method}</span>
                <span class="url">${url}</span>
                <span class="status ${statusClass}">${status} (${duration}ms)</span>
                <div style="clear:both;margin-top:10px;">
                    <details>
                        <summary style="cursor:pointer;color:#667eea;">查看详情</summary>
                        <div style="margin-top:10px;">
                            <div style="color:#666;font-size:12px;margin-bottom:5px;">请求体:</div>
                            <pre style="background:#0f0f1a;padding:10px;border-radius:4px;overflow-x:auto;font-size:12px;">${requestBody || '(无)'}</pre>
                            <div style="color:#666;font-size:12px;margin:10px 0 5px;">响应:</div>
                            <pre style="background:#0f0f1a;padding:10px;border-radius:4px;overflow-x:auto;font-size:12px;">${responseBody}</pre>
                        </div>
                    </details>
                </div>
            `;
            
            logContainer.insertBefore(entry, logContainer.firstChild);
        }
        
        function clearLogs() {
            document.getElementById('logContainer').innerHTML = `
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <div>暂无请求日志</div>
                    <div style="font-size:12px;margin-top:10px;">发送请求后日志将显示在这里</div>
                </div>
            `;
        }
        
        async function executeQuery() {
            const host = document.getElementById('dbHost').value;
            const db = document.getElementById('dbName').value;
            const user = document.getElementById('dbUser').value;
            const pass = document.getElementById('dbPass').value;
            const sql = document.getElementById('sqlQuery').value;
            
            if (!sql) {
                alert('请输入 SQL 查询');
                return;
            }
            
            const resultArea = document.getElementById('queryResult');
            resultArea.innerHTML = '<span class="info">执行查询中...</span>';
            
            try {
                const response = await fetch('/debugger-api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'db_query',
                        host: host,
                        db: db,
                        user: user,
                        pass: pass,
                        sql: sql
                    })
                });
                
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    resultArea.innerHTML = `<span class="success">✓ 查询成功</span>\n\n${JSON.stringify(data, null, 2)}`;
                    resultArea.className = 'response-area success';
                } catch {
                    resultArea.innerHTML = text;
                    resultArea.className = 'response-area';
                }
            } catch (error) {
                resultArea.innerHTML = `<span class="error">❌ 查询失败: ${error.message}</span>`;
                resultArea.className = 'response-area error';
            }
        }
    </script>
</body>
</html>