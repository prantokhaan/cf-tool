<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solved Problems</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/solvedProblems.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <div class="container">
        <h1>Solved Problems</h1>
        <label for="rating">Select Rating:</label>
        <select id="rating">
            <option value="all">All</option>
            <?php for ($i = 800; $i <= 3500; $i += 100) echo "<option value=\"$i\">$i</option>"; ?>
        </select>
        <button id="reset">Reset</button>

        <table id="problemsTable">
            <thead>
                <tr>
                    <th>Problem ID</th>
                    <th>Problem Name</th>
                    <th>Time Needed</th>
                    <th>How Solved</th>
                    
                </tr>
            </thead>
            <tbody>
                <!-- Data will be inserted here by JavaScript -->
            </tbody>
        </table>
        <div class="calculation">
            <h2>Calculation</h2>
            <h3 class="calc">Total Solved: <span id="solved" class="calcInside"></span></h3>
            <h3 class="calc">Total Tried: <span id="totalTried" class="calcInside"></span></h3>
            <h3 class="calc">Success Rate: <span id="successRate" class="calcInside"></span></h3>
            <h3 class="calc">Time Needed: <span id="timeNeeded" class="calcInside"></span></h3>
            <h3 class="calc">Time Tried: <span id="timeTried" class="calcInside"></span></h3>
            <h3 class="calc">Average Time Needed: <span id="avgSucceed" class="calcInside"></span></h3>
            <h3 class="calc">Average Time Given: <span id="avgGiven" class="calcInside"></span></h3>
        </div>
        <h2>Percentage of Statuses:</h2>
        <table id="statusTable">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Number of Problems</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>With Test Cases</td>
                    <td id="withTestSolved"></td>
                    <td id="withTestPercentage"></td>
                </tr>
                <tr>
                    <td>Without Test Cases</td>
                    <td id="withoutTestSolved"></td>
                    <td id="withoutTestPercentage"></td>
                </tr>
                <tr>
                    <td>With Editorial</td>
                    <td id="withEditorialSolved"></td>
                    <td id="withEditorialPercentage"></td>
                </tr>
                <tr>
                    <td>With Solution</td>
                    <td id="withSolutionSolved"></td>
                    <td id="withSolutionPercentage"></td>
                </tr>
            </tbody>
        </table>
         <div id="statusPieChartContainer">
            <canvas id="statusPieChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratingSelect = document.getElementById('rating');
            const resetButton = document.getElementById('reset');
            const problemsTableBody = document.querySelector('#problemsTable tbody');
            const username = localStorage.getItem('username');
            const solved = document.getElementById('solved');
            const totalTried = document.getElementById('totalTried');
            const successRate = document.getElementById('successRate');
            const timeNeeded = document.getElementById('timeNeeded');
            const timeTried = document.getElementById('timeTried');
            const avgSucceed = document.getElementById('avgSucceed');
            const avgGiven = document.getElementById('avgGiven');
            const withTestSolved = document.getElementById('withTestSolved');
            const withTestPercentage = document.getElementById('withTestPercentage');
            const withoutTestSolved = document.getElementById('withoutTestSolved');
            const withoutTestPercentage = document.getElementById('withoutTestPercentage');
            const withEditorialSolved = document.getElementById('withEditorialSolved');
            const withEditorialPercentage = document.getElementById('withEditorialPercentage');
            const withSolutionSolved = document.getElementById('withSolutionSolved');
            const withSolutionPercentage = document.getElementById('withSolutionPercentage');

            if (!username) {
                console.error('Username is not set in localStorage');
                return;
            }

            const savedRating = localStorage.getItem('myrating');
            if (savedRating) {
                ratingSelect.value = savedRating;
                fetchProblems(savedRating);
            }

            ratingSelect.addEventListener('change', function() {
                const selectedRating = ratingSelect.value;
                localStorage.setItem('myrating', selectedRating);
                fetchProblems(selectedRating);
            });

            resetButton.addEventListener('click', function() {
                localStorage.setItem('myrating', 'all');
                ratingSelect.value = "all";
            });

            

            function fetchProblems(rating) {
                var withoutTestCases = [];
            var withTestCases = [];
            var withEditorial = [];
            var withSolution = [];
                fetch(`fetchSolvedProblems.php?rating=${rating}&username=${username}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        problemsTableBody.innerHTML = "";
                        data.forEach(problem => {
                            const row = document.createElement('tr');
                            var color;
                            if(problem.howSolved === 'with_test_cases'){
                                color = "#41B06E";
                            }else if(problem.howSolved === 'without_test_cases'){
                                color = "#8DECB4";
                            }else if(problem.howSolved === 'with_editorial'){
                                color = "#DD5746";
                            }else if(problem.howSolved === 'with_solution'){
                                color = "#DD5746";
                            }
                            row.innerHTML = `
                                <td>${problem.id}</td>
                                <td><a href="https://codeforces.com/contest/${problem.contestId}/problem/${problem.problemIndex}" target="_blank">${problem.problemName}</a></td>
                                <td>${problem.timeToSolve}</td>
                                <td id="howSolvedStat">${problem.howSolved}</td>
                            `;
                            const howSolvedStat = row.querySelector('#howSolvedStat');
                            howSolvedStat.style.backgroundColor = color;
                            problemsTableBody.appendChild(row);
                        });
                        totalTried.textContent = data.length;
                        var solvedCount = 0;
                        var timeNeededSum = 0;
                        var timeTriedSum = 0;
                        data.forEach(problem => {
                            if(problem.howSolved === 'with_test_cases' || problem.howSolved==='without_test_cases'){
                                solvedCount++;
                                timeNeededSum += parseInt(problem.timeToSolve);
                            }
                            if(problem.howSolved === 'with_test_cases'){
                                withTestCases.push(problem);
                            }else if(problem.howSolved === 'without_test_cases'){
                                withoutTestCases.push(problem);
                            }else if(problem.howSolved === 'with_editorial'){
                                withEditorial.push(problem);
                            }else if(problem.howSolved === 'with_solution'){
                                withSolution.push(problem);
                            }
                            timeTriedSum += parseInt(problem.timeToSolve);
                        });
                        solved.textContent = solvedCount;
                        successRate.textContent = (solvedCount/data.length*100).toFixed(2) + '%';
                        timeNeeded.textContent = timeNeededSum + " minutes";
                        timeTried.textContent = timeTriedSum + " minutes";
                        avgSucceed.textContent = (timeNeededSum/solvedCount).toFixed(2) + " minutes";
                        avgGiven.textContent = (timeTriedSum/data.length).toFixed(2) + " minutes";
                        withTestSolved.textContent = withTestCases.length;
                        withTestPercentage.textContent = (withTestCases.length/data.length*100).toFixed(2) + '%';
                        withoutTestSolved.textContent = withoutTestCases.length;
                        withoutTestPercentage.textContent = (withoutTestCases.length/data.length*100).toFixed(2) + '%';
                        withEditorialSolved.textContent = withEditorial.length;
                        withEditorialPercentage.textContent = (withEditorial.length/data.length*100).toFixed(2) + '%';
                        withSolutionSolved.textContent = withSolution.length;
                        withSolutionPercentage.textContent = (withSolution.length/data.length*100).toFixed(2) + '%';

                        updatePieChart(withTestCases, withoutTestCases, withEditorial, withSolution);
                    })
                    .catch(error => {
                        console.error('There was a problem with the fetch operation:', error);
                    });

            }

            function updatePieChart(withTestCases, withoutTestCases, withEditorial, withSolution) {
                const ctx = document.getElementById('statusPieChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['With Test Cases', 'Without Test Cases', 'With Editorial', 'With Solution'],
                        datasets: [{
                            data: [
                                withTestCases.length,
                                withoutTestCases.length,
                                withEditorial.length,
                                withSolution.length
                            ],
                            backgroundColor: [
                                '#41B06E',
                                '#8DECB4',
                                '#DD5746',
                                '#FFB74D'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
