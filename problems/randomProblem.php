<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Codeforces Problem</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/randomProblem.css">
    <script>
        // Function to fetch problems from Codeforces API with caching
        async function fetchProblemsFromApi() {
            const cacheKey = 'codeforces_problems_cache';
            const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                return JSON.parse(cachedData);
            } else {
                const apiUrl = "https://codeforces.com/api/problemset.problems";
                const response = await fetch(apiUrl);
                const data = await response.json();

                localStorage.setItem(cacheKey, JSON.stringify(data));
                localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());

                return data;
            }
        }

        // Function to fetch user submissions from Codeforces API based on handle with caching
        async function fetchUserSubmissions(handle) {
            const cacheKey = `user_submissions_${handle}`;
            const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                return JSON.parse(cachedData);
            } else {
                const apiUrl = `https://codeforces.com/api/user.status?handle=${encodeURIComponent(handle)}`;
                const response = await fetch(apiUrl);
                const data = await response.json();

                localStorage.setItem(cacheKey, JSON.stringify(data));
                localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());

                return data;
            }
        }

        // Function to filter problems based on rating and contest ID
        function filterProblems(problems, ratingFilter, minContestIdFilter, maxContestIdFilter, solvedProblems, tagsFilter) {
            return problems.filter(problem => {
                const matchRating = (!ratingFilter || problem.rating == ratingFilter);
                const matchContestId = (!minContestIdFilter || problem.contestId >= minContestIdFilter) &&
                                        (!maxContestIdFilter || problem.contestId <= maxContestIdFilter);
                const matchTags = !tagsFilter || problem.tags.includes(tagsFilter);
                const notSolved = !solvedProblems.has(`${problem.contestId}${problem.index}`);
                return matchRating && matchContestId && notSolved && matchTags;
            });
        }

        // Function to get user's solved problems
        async function getSolvedProblems(handle) {
            const userSubmissions = await fetchUserSubmissions(handle);
            const solvedProblems = new Set();
            if (userSubmissions.status === 'OK') {
                userSubmissions.result.forEach(submission => {
                    if (submission.verdict === 'OK') {
                        solvedProblems.add(`${submission.problem.contestId}${submission.problem.index}`);
                    }
                });
            }
            return solvedProblems;
        }

        // Function to show a random problem
        async function showRandomProblem() {
            const ratingFilter = localStorage.getItem('ratingFilter') || '';
            const minContestIdFilter = localStorage.getItem('minContestIdFilter') || '';
            const maxContestIdFilter = localStorage.getItem('maxContestIdFilter') || '';
            const tagsFilter = localStorage.getItem('tagsFilter') || '';
            const cfUser = localStorage.getItem('cfUser');

            const problemsData = await fetchProblemsFromApi();
            const problems = problemsData.status === 'OK' ? problemsData.result.problems : [];
            const solvedProblems = cfUser ? await getSolvedProblems(cfUser) : new Set();

            const filteredProblems = filterProblems(problems, ratingFilter, minContestIdFilter, maxContestIdFilter, solvedProblems, tagsFilter);
            const randomProblem = filteredProblems.length ? filteredProblems[Math.floor(Math.random() * filteredProblems.length)] : null;

            displayRandomProblem(randomProblem);
        }

        // Function to display the random problem on the page
        function displayRandomProblem(problem) {
            const container = document.querySelector('.random-problem-container');
            container.innerHTML = '';

            if (problem) {
                container.innerHTML = `
                    <div class="problem-info">
                        <h2>Problem ID: ${problem.contestId}${problem.index}</h2>
                        <p>Name: ${problem.name}</p>
                        <p>Contest ID: ${problem.contestId}</p>
                        <p>Rating: ${problem.rating ? problem.rating : 'N/A'}</p>
                        <a href="https://codeforces.com/problemset/problem/${problem.contestId}/${problem.index}" target="_blank">View on Codeforces</a>
                    </div>
                    <button class="random-problem-btn" onclick="showRandomProblem()">Generate Another Problem</button>
                `;
            } else {
                container.innerHTML = '<p>No unsolved problems found with the given filters.</p>';
            }
            container.innerHTML += '<a href="allProblems.php" class="back-btn">Back to Problem List</a>';
        }

        // Initialize the page when DOM is fully loaded
        document.addEventListener('DOMContentLoaded', async function() {
            let cfUser = localStorage.getItem('cfUser');
            if (!cfUser) {
                cfUser = prompt("Please enter your Codeforces handle:");
                if (cfUser) {
                    localStorage.setItem('cfUser', cfUser);
                }
            }

            await showRandomProblem();
        });
    </script>
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <h1>Random Codeforces Problem</h1>
    <div class="random-problem-container">
        <!-- Problem will be dynamically inserted here -->
    </div>
</body>
</html>
