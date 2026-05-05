<?php
$tools = [
    [
        'name' => 'API Debugger',
        'description' => '统一 API 测试工具 - 支持 HRPAuth 和 Yggdrasil API 端点测试、请求日志和数据库调试',
        'icon' => '🔧',
        'url' => 'debugger.php',
        'color' => '#667eea',
        'badge' => '推荐'
    ],
    [
        'name' => 'API Backend',
        'description' => '调试工具后端 API - 处理数据库查询、日志记录等服务器端操作',
        'icon' => '⚙️',
        'url' => 'debugger-api.php',
        'color' => '#764ba2',
        'badge' => null
    ]
];

$stats = [
    'total_endpoints' => 21,
    'hrp_endpoints' => 9,
    'ygg_endpoints' => 12
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRPAuth API Debug Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            color: #eee;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 60px;
        }
        .header h1 {
            font-size: 48px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header .subtitle {
            font-size: 18px;
            color: #aaa;
            margin-bottom: 30px;
        }
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        .tool-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .tool-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color), var(--card-color-dark));
        }
        .tool-card .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .tool-card h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #fff;
        }
        .tool-card p {
            color: #aaa;
            line-height: 1.6;
            font-size: 14px;
        }
        .tool-card .badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .tool-card .arrow {
            position: absolute;
            bottom: 30px;
            right: 30px;
            font-size: 24px;
            opacity: 0.3;
            transition: all 0.3s;
        }
        .tool-card:hover .arrow {
            opacity: 1;
            transform: translateX(5px);
        }
        .info-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-section h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #667eea;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .info-item .method {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            min-width: 60px;
            text-align: center;
        }
        .method.get { background-color: #4CAF50; }
        .method.post { background-color: #2196F3; }
        .method.put { background-color: #FF9800; }
        .method.delete { background-color: #F44336; }
        .info-item .endpoint {
            color: #ccc;
            font-family: 'Consolas', monospace;
            font-size: 13px;
        }
        .footer {
            text-align: center;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 HRPAuth API Debug Portal</h1>
            <p class="subtitle">Yggdrasil API 认证系统调试工具集</p>
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['total_endpoints']; ?></div>
                    <div class="stat-label">Total Endpoints</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['hrp_endpoints']; ?></div>
                    <div class="stat-label">HRPAuth</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['ygg_endpoints']; ?></div>
                    <div class="stat-label">YggdrasilAPI</div>
                </div>
            </div>
        </div>
        
        <div class="tools-grid">
            <?php foreach ($tools as $tool): ?>
            <a href="<?php echo htmlspecialchars($tool['url']); ?>" class="tool-card" style="--card-color: <?php echo $tool['color']; ?>; --card-color-dark: <?php echo $tool['color']; ?>80;">
                <?php if ($tool['badge']): ?>
                <span class="badge"><?php echo $tool['badge']; ?></span>
                <?php endif; ?>
                <div class="icon"><?php echo $tool['icon']; ?></div>
                <h2><?php echo htmlspecialchars($tool['name']); ?></h2>
                <p><?php echo htmlspecialchars($tool['description']); ?></p>
                <span class="arrow">→</span>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div class="info-section">
            <h3>📡 Available Endpoints</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/login</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/register</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/user</span>
                </div>
                <div class="info-item">
                    <span class="method get">GET</span>
                    <span class="endpoint">/logout</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/email-verification</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/totp/setup</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/totp/verify</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/change-username</span>
                </div>
                <div class="info-item">
                    <span class="method get">GET</span>
                    <span class="endpoint">/</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/authserver/authenticate</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/authserver/refresh</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/authserver/validate</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/sessionserver/session/minecraft/join</span>
                </div>
                <div class="info-item">
                    <span class="method get">GET</span>
                    <span class="endpoint">/sessionserver/session/minecraft/hasJoined</span>
                </div>
                <div class="info-item">
                    <span class="method post">POST</span>
                    <span class="endpoint">/api/profiles/minecraft</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>HRPAuth & Yggdrasil API Debug Portal | PHP Environment</p>
        </div>
    </div>
</body>
</html>