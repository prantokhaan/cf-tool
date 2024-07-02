<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Solved Indexes</title>
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
        <h1>Problem Indexes</h1>
        <canvas id="indexChart" width="400" height="200"></canvas>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const username = localStorage.getItem('cfUser');
            if (!username) {
                alert('Please set your Codeforces handle in the local storage.');
                return;
            }

            const cacheKey = `cfSolvedIndex_${username}`;
            const cacheExpiration = 3600 * 1000; // 1 hour

            const fetchData = async () => {
                const response = await fetch(`./visualize/fetch_indexes.php?handle=${username}`);
                const data = await response.json();
                localStorage.setItem(cacheKey, JSON.stringify({ data: data, timestamp: Date.now() }));
                return data;
            };

            const renderChart = (data) => {
                const ctx = document.getElementById('indexChart').getContext('2d');

                const colors = {
                    'A': 'rgba(255, 99, 132, 0.2)',
                    'B': 'rgba(54, 162, 235, 0.2)',
                    'C': 'rgba(255, 206, 86, 0.2)',
                    'D': 'rgba(75, 192, 192, 0.2)',
                    'E': 'rgba(153, 102, 255, 0.2)',
                    'F': 'rgba(255, 159, 64, 0.2)',
                    'G': 'rgba(255, 99, 132, 0.2)',
                    'H': 'rgba(54, 162, 235, 0.2)',
                    'I': 'rgba(255, 206, 86, 0.2)',
                    'J': 'rgba(75, 192, 192, 0.2)',
                    'K': 'rgba(153, 102, 255, 0.2)',
                    'L': 'rgba(255, 159, 64, 0.2)',
                    'M': 'rgba(255, 99, 132, 0.2)',
                    'N': 'rgba(54, 162, 235, 0.2)',
                    'O': 'rgba(255, 206, 86, 0.2)',
                    'P': 'rgba(75, 192, 192, 0.2)',
                    'Q': 'rgba(153, 102, 255, 0.2)',
                    'R': 'rgba(255, 159, 64, 0.2)',
                    'S': 'rgba(255, 99, 132, 0.2)',
                    'T': 'rgba(54, 162, 235, 0.2)',
                    'U': 'rgba(255, 206, 86, 0.2)',
                    'V': 'rgba(75, 192, 192, 0.2)',
                    'W': 'rgba(153, 102, 255, 0.2)',
                    'X': 'rgba(255, 159, 64, 0.2)',
                    'Y': 'rgba(255, 99, 132, 0.2)',
                    'Z': 'rgba(54, 162, 235, 0.2)',
                };

                const labels = Object.keys(data);
                const values = Object.values(data);
                const backgroundColors = labels.map(label => colors[label.charAt(0)] || '#000000');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Problem Indexes',
                            data: values,
                            backgroundColor: backgroundColors,
                            borderColor: 'rgba(0, 0, 0, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    color: 'black'
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
                                    text: 'Problem Index'
                                }
                            }
                        }
                    }
                });
            };

            const cachedData = localStorage.getItem(cacheKey);
            if (cachedData) {
                const { data, timestamp } = JSON.parse(cachedData);
                if (Date.now() - timestamp < cacheExpiration) {
                    renderChart(data);
                } else {
                    const newData = await fetchData();
                    renderChart(newData);
                }
            } else {
                const data = await fetchData();
                renderChart(data);
            }
        });
    </script>
</body>
</html>
