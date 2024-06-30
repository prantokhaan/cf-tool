<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Codeforces Solved Problems</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
        }
        .error, .success {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Problem Ratings</h1>
        <canvas id="problemsChart" width="400" height="200"></canvas>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const username = localStorage.getItem('cfUser');
            if (!username) {
                alert('Please set your Codeforces handle in the local storage.');
                return;
            }

            const cacheKey = `cfSolvedProblems_${username}`;
            const cachedData = localStorage.getItem(cacheKey);
            const cacheExpiration = 3600 * 1000; // 1 hour

            const fetchData = async () => {
                const response = await fetch(`./visualize/fetch_problems.php?handle=${username}`);
                const data = await response.json();
                localStorage.setItem(cacheKey, JSON.stringify({ data: data, timestamp: Date.now() }));
                return data;
            };

            const renderChart = (data) => {
                const ctx = document.getElementById('problemsChart').getContext('2d');
                
                const colors = {
                    800: '#BFBFBF', 
                    900: '#BFBFBF', 
                    1000: '#BFBFBF', 
                    1100: '#BFBFBF', 
                    1200: '#64FF64', 
                    1300: '#64FF64', 
                    1400: '#52F6BF', 
                    1500: '#52F6BF', 
                    1600: '#8C8CFF', 
                    1700: '#8C8CFF', 
                    1800: '#8C8CFF', 
                    1900: '#FF71FF', 
                    2000: '#FF71FF', 
                    2100: '#FFD194',
                    2200: '#FFD194',
                    2300: '#FFD194',
                    2400: '#FF8484',
                    2500: '#FF8484',
                    2600: '#FF2C2C',
                    2700: '#FF2C2C',
                    2800: '#FF2C2C',
                    2900: '#FF2C2C',
                    3000: '#B21919',
                    3100: '#B21919',
                    3200: '#B21919',
                    3300: '#B21919',
                    3400: '#B21919',
                    3500: '#B21919'
                };

                const labels = Object.keys(data);
                const values = Object.values(data);
                const backgroundColors = labels.map(label => colors[label] || '#000000');
                const borderColor = 'black'

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Problems Solved',
                            data: values,
                            backgroundColor: backgroundColors,
                            borderColor: borderColor,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    color: 'rgb(0, 0, 0)'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Problems Solved'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Rating'
                                }
                            }
                        }
                    }
                });
            };

            if (cachedData) {
                const parsedCache = JSON.parse(cachedData);
                const { data, timestamp } = parsedCache;

                if (Date.now() - timestamp < cacheExpiration) {
                    renderChart(data);
                } else {
                    fetchData().then(renderChart);
                }
            } else {
                fetchData().then(renderChart);
            }
        });
    </script>
</body>
</html>
