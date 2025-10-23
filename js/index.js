function loadLogs() {
    fetch('ajax/fetch_logs.php')
        .then(response => response.text())
        .then(html => {
        document.querySelector('tbody').innerHTML = html;
        });
    }
    function refreshDashboard() {
    fetch('ajax/fetch_index.php') 
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalroom').textContent = data.totalroom;
            document.getElementById('totalusers').textContent = data.totalusers;
            document.getElementById('totaloccupied').textContent = data.totaloccupied;
            document.getElementById('totalUnoccupied').textContent = data.totalUnoccupied;
        })
        .catch(error => console.error('Error refreshing dashboard:', error));
    }

    setInterval(loadLogs, 3000);
    window.onload = loadLogs;
    setInterval(refreshDashboard, 3000);
    refreshDashboard();