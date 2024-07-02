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
        <div class="solved-options">
            <form id="solveForm" action="save_solution.php" method="POST">
                <input type="hidden" name="username" value="">
                <input type="hidden" name="contestId" value="<?php echo htmlspecialchars($contestId); ?>">
                <input type="hidden" name="index" value="<?php echo htmlspecialchars($index); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                <input type="hidden" name="rating" value="<?php echo htmlspecialchars($rating); ?>">
                <input type="hidden" name="submissionId" id="submissionId" value="">
                <input type="hidden" name="language" id="language" value="">
                <input type="hidden" name="problemTags" id="problemTags" value="">
                <div class="input-field">
                    <label for="timeToSolve">Time Needed (in minutes):</label>
                    <input type="number" id="timeToSolve" name="timeToSolve" required>
                </div>
                <div class="input-field">
                    <label for="solveMethod">How you solved the problem?</label>
                    <select name="solveMethod" id="solveMethod" required>
                        <option value="">Select one</option>
                        <option value="without_test_cases">Without seeing the test cases</option>
                        <option value="with_test_cases">By seeing the test cases</option>
                        <option value="with_editorial">By seeing the editorial</option>
                        <option value="with_solution">By seeing the solution</option>
                    </select>
                </div>
                <button id="addButton">Add Problem</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const contestId = <?php echo json_encode($contestId); ?>;
            const index = <?php echo json_encode($index); ?>;
            const cfUser = localStorage.getItem('cfUser');
            const username = localStorage.getItem('username');
            document.getElementById('solveForm').username.value = username;
            const submissions = JSON.parse(localStorage.getItem(`user_submissions_${cfUser}`)) || [];

            const hasSolved = submissions.some(submission =>
                submission.problem.contestId == contestId && submission.problem.index == index && submission.verdict == 'OK'
            );

            if(!hasSolved){
                const result = confirm('You have not solved this problem yet. Do you want to start solving this problem?: ');
                if(result){
                    window.location.href = `start_solving.php?contestId=${contestId}&index=${index}&name=${encodeURIComponent(<?php echo json_encode($name); ?>)}&rating=<?php echo json_encode($rating); ?>`;
                } else {
                    window.location.href = 'allProblems.php';
                }
            }

            const solvedOptions = document.querySelector('.solved-options');

            document.getElementById('solveForm').addEventListener('submit', function(e){
                e.preventDefault();
                const timeToSolve = document.getElementById('timeToSolve').value;
                const solveMethod = document.getElementById('solveMethod').value;

                const submissions = localStorage.getItem(`user_submissions_${cfUser}`);

                if(submissions){
                    const userSubmissions = JSON.parse(submissions);
                    const submission = userSubmissions.find(submission => submission.problem.contestId == contestId && submission.problem.index == index && submission.verdict == 'OK');
                    document.getElementById('submissionId').value = submission.id;
                    document.getElementById('language').value = submission.programmingLanguage;
                    document.getElementById('problemTags').value = submission.problem.tags.join(',');
                }

                if(timeToSolve && solveMethod){
                    this.timeToSolve.value = Math.ceil(timeToSolve/60);
                    this.solveMethod.value = solveMethod;
                    this.submit();
                } else {
                    alert('Please fill all the fields');
                }
            });
        })
    </script>
</body>
</html>
