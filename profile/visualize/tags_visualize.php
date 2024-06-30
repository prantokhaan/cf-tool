<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Codeforces Solved Problems</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="tagContainer">
        <h1>Tags Solved</h1>
        <div class="chart-container">
            <div class="chart-area">
                <canvas id="tagsChart"></canvas>
            </div>
            <div class="chart-legend" id="chart-legend"></div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const username = localStorage.getItem('cfUser');
            if (!username) {
                alert('Please set your Codeforces handle in the local storage.');
                return;
            }

            const cacheKey = `cfSolvedTags_${username}`;
            const cachedData = localStorage.getItem(cacheKey);
            const cacheExpiration = 3600 * 1000; // 1 hour

            const fetchData = async () => {
                const response = await fetch(`visualize/fetch_tags.php?handle=${username}`);
                const data = await response.json();
                localStorage.setItem(cacheKey, JSON.stringify({ data: data, timestamp: Date.now() }));
                return data;
            };

            const renderChart = (data) => {
                const ctx = document.getElementById('tagsChart').getContext('2d');
                const tags = Object.keys(data);
                const counts = Object.values(data);

                // Generate a unique color for each tag
                const colors = tags.map((_, index) => `hsl(${index * 360 / tags.length}, 100%, 70%)`);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: tags,
                        datasets: [{
                            label: 'Tags Solved',
                            data: counts,
                            backgroundColor: colors,
                            borderColor: '#fff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                const legendContainer = document.getElementById('chart-legend');
                legendContainer.innerHTML = tags.map((tag, index) => {
                    return `<div class="legend-item">
                                <span class="legend-color" style="background-color: ${colors[index]};"></span>${tag}: ${counts[index]}
                            </div>`;
                }).join('');
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
