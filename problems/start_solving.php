<?php
$contestId = $_GET['contestId'];
$index = $_GET['index'];
$name = urldecode($_GET['name']);
$rating = $_GET['rating'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Solving</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/solving.css">
    <link rel="favicon" href="../images/favicon.png">
</head>
<body class="solve_container">
    <?php include '../shared/nav.php'; ?>
    <div class="container">
        <h1>Start Solving</h1>
        <div class="problem-details">
            <p><strong>Contest ID:</strong> <?php echo htmlspecialchars($contestId); ?></p>
            <p><strong>Index:</strong> <?php echo htmlspecialchars($index); ?></p>
            <p><strong>Problem Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Rating:</strong> <?php echo htmlspecialchars($rating); ?></p>
        </div>
        <div class="timer">00:00</div>
        <div class="buttons">
            <button id="startButton">Start</button>
            <button id="pauseButton" style="display:none;">Pause</button>
            <button id="stopButton" style="display:none;">Stop</button>
            <button id="finishedButton" style="display:none;">Finished</button>
        </div>
        <div class="solved-options" style="display:none;">
            <form id="solveForm" action="save_solution.php" method="POST">
                <input type="hidden" name="username" value="">
                <input type="hidden" name="contestId" value="<?php echo htmlspecialchars($contestId); ?>">
                <input type="hidden" name="index" value="<?php echo htmlspecialchars($index); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <input type="hidden" name="rating" value="<?php echo htmlspecialchars($rating); ?>">
                <input type="hidden" name="timeToSolve" id="timeToSolve" value="0">
                <input type="hidden" name="submissionId" id="submissionId" value="">
                <input type="hidden" name="language" id="language" value="">
                <input type="hidden" name="problemTags" id="problemTags" value="">
                <select name="solveMethod" id="solveMethod">
                    <option value="">How you solved the problem?</option>
                    <option value="without_test_cases">Without seeing the test cases</option>
                    <option value="with_test_cases">By seeing the test cases</option>
                    <option value="with_editorial">By seeing the editorial</option>
                    <option value="with_solution">By seeing the solution</option>
                </select>
                <button type="submit" id="addButton">Add</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const contestId = <?php echo json_encode($contestId); ?>;
    const index = <?php echo json_encode($index); ?>;
    const cfUser = localStorage.getItem('cfUser');
    const username = localStorage.getItem('username');
    document.getElementById('solveForm').username.value = username;
    const submissions = JSON.parse(localStorage.getItem(`user_submissions_${cfUser}`)) || [];

    const hasSolved = submissions.some(submission => 
        submission.problem.contestId == contestId &&
        submission.problem.index == index &&
        submission.verdict === "OK"
    );

    if (hasSolved) {
        alert('You have already solved this problem.');
        window.location.href = './allProblems.php';
    }

    let timerInterval;
    let timerRunning = false;
    let timerPaused = false;
    let seconds = 0;
    const timerElement = document.querySelector('.timer');
    const startButton = document.getElementById('startButton');
    const pauseButton = document.getElementById('pauseButton');
    const stopButton = document.getElementById('stopButton');
    const finishedButton = document.getElementById('finishedButton');
    const solvedOptions = document.querySelector('.solved-options');

    const updateTimerDisplay = () => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerElement.textContent = `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        document.title = "Time: " + timerElement.textContent;
        document.getElementById('timeToSolve').value = seconds;
    };

    startButton.addEventListener('click', function() {
        if (!timerRunning || timerPaused) {
            clearInterval(timerInterval); // Clear any existing interval before starting a new one
            if (timerPaused) {
                timerPaused = false;
            } else {
                seconds = 0;
                window.open(`https://codeforces.com/problemset/problem/${contestId}/${index}`, '_blank');
            }
            timerRunning = true;
            startButton.style.display = 'none';
            pauseButton.style.display = 'inline-block';
            stopButton.style.display = 'inline-block';
            finishedButton.style.display = 'inline-block';
            timerInterval = setInterval(() => {
                if (!timerPaused) {
                    seconds++;
                    updateTimerDisplay();
                }
            }, 1000);
        }
    });

    pauseButton.addEventListener('click', function() {
        timerPaused = true;
        startButton.style.display = 'inline-block';
        pauseButton.style.display = 'none';
    });

    stopButton.addEventListener('click', function() {
        clearInterval(timerInterval);
        timerRunning = false;
        timerPaused = false;
        seconds = 0;
        updateTimerDisplay();
        startButton.style.display = 'inline-block';
        pauseButton.style.display = 'none';
        stopButton.style.display = 'none';
        finishedButton.style.display = 'none';
        document.title = 'Start Solving';
    });

    finishedButton.addEventListener('click', async function() {
        timerPaused = true;
        pauseButton.style.display = 'none';
        startButton.style.display = 'inline-block';
        try {
            const response = await fetch(`https://codeforces.com/api/user.status?handle=${cfUser}&from=1&count=10`);
            const data = await response.json();
            const latestSubmission = data.result.find(submission => 
                submission.problem.contestId == contestId &&
                submission.problem.index == index &&
                submission.verdict === "OK"
            );

            if (latestSubmission) {
                document.getElementById('submissionId').value = latestSubmission.id;
                document.getElementById('language').value = latestSubmission.programmingLanguage;
                document.getElementById('problemTags').value = latestSubmission.problem.tags.join(',');

                solvedOptions.style.display = 'block';
            } else {
                alert('Don\'t cheat! You need to solve the problem first.');
                timerPaused = false;
                pauseButton.style.display = 'inline-block';
                startButton.style.display = 'none';
            }
        } catch (error) {
            alert('Error fetching submission data. Please try again.');
            timerPaused = false;
            pauseButton.style.display = 'inline-block';
            startButton.style.display = 'none';
        }
    });

    updateTimerDisplay();
});

    </script>
</body>
</html>
