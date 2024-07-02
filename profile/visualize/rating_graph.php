<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contest Rating Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        h1 {
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            color: #000000;
        }
        .select-container {
            text-align: right;
            margin-bottom: 10px;
        }
        select {
            padding: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contest Rating Graph</h1>
        <div class="select-container">
            <select id="dataSelector">
                <option value="rank">Rank</option>
                <option value="newRating">New Rating</option>
                <option value="ratingChanges">Rating Changes</option>
            </select>
        </div>
        <canvas id="ratingChart" width="400" height="200"></canvas>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const username = localStorage.getItem('cfUser');
            if (!username) {
                alert('Please set your Codeforces handle in the local storage.');
                return;
            }

            const fetchContestData = async () => {
                const response = await fetch(`https://codeforces.com/api/user.rating?handle=${username}`);
                const data = await response.json();
                if (data.status !== "OK") {
                    throw new Error("Failed to fetch data");
                }
                return data.result;
            };

            const ctx = document.getElementById('ratingChart').getContext('2d');
            const dataSelector = document.getElementById('dataSelector');

            let contestData = [];
            try {
                contestData = await fetchContestData();
            } catch (error) {
                alert("Error fetching contest data: " + error.message);
                return;
            }

            const labels = contestData.map((contest, index) => index + 1);
            const rankData = contestData.map(contest => contest.rank);
            const newRatingData = contestData.map(contest => contest.newRating);
            const ratingChangesData = contestData.map(contest => contest.newRating - contest.oldRating);

            const getColorForRating = (rating) => {
                if (rating <= 1199) return 'rgba(128, 128, 128, 1)'; // Grey
                if (rating <= 1399) return 'rgba(0, 128, 0, 1)'; // Green
                if (rating <= 1599) return 'rgba(0, 255, 255, 1)'; // Cyan
                if (rating <= 1899) return 'rgba(0, 0, 255, 1)'; // Blue
                if (rating <= 2099) return 'rgba(238, 130, 238, 1)'; // Violet
                if (rating <= 2599) return 'rgba(255, 165, 0, 1)'; // Orange
                return 'rgba(255, 0, 0, 1)'; // Red
            };

            const initialData = rankData;
            const initialLabel = 'Rank';

            let chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: initialLabel,
                        data: initialData,
                        borderColor: initialData.map(value => getColorForRating(value)),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        pointBackgroundColor: initialData.map(value => getColorForRating(value)),
                        pointBorderColor: initialData.map(value => getColorForRating(value)),
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
                                text: 'Value'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Contest Number'
                            }
                        }
                    }
                }
            });

            dataSelector.addEventListener('change', function () {
                const selectedOption = dataSelector.value;
                let newData;
                if (selectedOption === 'rank') {
                    newData = rankData;
                } else if (selectedOption === 'newRating') {
                    newData = newRatingData;
                } else if (selectedOption === 'ratingChanges') {
                    newData = ratingChangesData;
                }
                chart.data.datasets[0].data = newData;
                chart.data.datasets[0].label = selectedOption.charAt(0).toUpperCase() + selectedOption.slice(1).replace(/([A-Z])/g, ' $1');
                chart.data.datasets[0].borderColor = newData.map(value => getColorForRating(value));
                chart.data.datasets[0].pointBackgroundColor = newData.map(value => getColorForRating(value));
                chart.data.datasets[0].pointBorderColor = newData.map(value => getColorForRating(value));
                chart.update();
            });
        });
    </script>
</body>
</html>
