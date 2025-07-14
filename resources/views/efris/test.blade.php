<!DOCTYPE html>
<html>
<head>
    <title>EFRIS API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>EFRIS API Test</h1>
    
    <div>
        <button onclick="testDebug()">Test Debug Route</button>
        <button onclick="testStatus()">Test Status Route</button>
        <button onclick="testConfig()">Test Config Route</button>
    </div>
    
    <div id="results" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>
    
    <script>
        function testDebug() {
            console.log('Testing debug route...');
            fetch('/debug/efris')
                .then(response => response.json())
                .then(data => {
                    console.log('Debug response:', data);
                    document.getElementById('results').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    console.error('Debug error:', error);
                    document.getElementById('results').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
        }
        
        function testStatus() {
            console.log('Testing status route...');
            fetch('/efris/get-status')
                .then(response => response.json())
                .then(data => {
                    console.log('Status response:', data);
                    document.getElementById('results').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    console.error('Status error:', error);
                    document.getElementById('results').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
        }
        
        function testConfig() {
            console.log('Testing config route...');
            fetch('/efris/validate-config')
                .then(response => response.json())
                .then(data => {
                    console.log('Config response:', data);
                    document.getElementById('results').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(error => {
                    console.error('Config error:', error);
                    document.getElementById('results').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
        }
    </script>
</body>
</html> 