<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Problems</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/contestProblems.css">
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <div class="container">
        <h1 id="contest-title">Codeforces Problems for Contest</h1>
        <table>
            <thead>
                <tr>
                    <th>Index</th>
                    <th>Problem Name</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody id="problems-table-body">
                <!-- Problems will be dynamically inserted here -->
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const urlParams = new URLSearchParams(window.location.search);
            const contestId = urlParams.get('contestId');
            const cfUser = localStorage.getItem('cfUser');

            if (!contestId) {
                document.getElementById('problems-table-body').innerHTML = '<tr><td colspan="3">No contest ID provided.</td></tr>';
                return;
            }

            document.getElementById('contest-title').textContent = `Codeforces Problems for Contest ${contestId}`;

            async function fetchProblemsFromApi() {
                const cacheKey = 'codeforces_contest_problems_cache';
                const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
                const cachedData = localStorage.getItem(cacheKey);
                const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

                if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                    return JSON.parse(cachedData);
                } else {
                    const apiUrl = "https://codeforces.com/api/problemset.problems";
                    const response = await fetch(apiUrl);
                    const data = await response.json();

                    if (data.status === 'OK') {
                        localStorage.setItem(cacheKey, JSON.stringify(data.result.problems));
                        localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());
                        return data.result.problems;
                    } else {
                        return null; // Handle error as per your application's logic
                    }
                }
            }

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

                    if (data.status === 'OK') {
                        localStorage.setItem(cacheKey, JSON.stringify(data.result));
                        localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());
                        return data.result;
                    } else {
                        return null; // Handle error as per your application's logic
                    }
                }
            }

            const problems = await fetchProblemsFromApi();

            if (!problems) {
                document.getElementById('problems-table-body').innerHTML = '<tr><td colspan="3">Failed to fetch problems from Codeforces API.</td></tr>';
                return;
            }

            const userSubmissions = cfUser ? await fetchUserSubmissions(cfUser) : [];
            const problemVerdicts = {};
            if (userSubmissions) {
                userSubmissions.forEach(submission => {
                    const problemKey = submission.problem.contestId + submission.problem.index;
                    if (!problemVerdicts[problemKey] || submission.verdict === 'OK') {
                        problemVerdicts[problemKey] = submission.verdict;
                    }
                });
            }

            const filteredProblems = problems.filter(problem => problem.contestId == contestId);

            filteredProblems.sort((a, b) => {
                if (a.index < b.index) return -1;
                if (a.index > b.index) return 1;
                return 0;
            });

            const problemsTableBody = document.getElementById('problems-table-body');
            problemsTableBody.innerHTML = '';

            filteredProblems.forEach(problem => {
                const index = problem.index;
                const name = problem.name;
                const rating = problem.rating || 'Unrated';
                const problemUrl = `https://codeforces.com/contest/${contestId}/problem/${index}`;
                const problemKey = problem.contestId + problem.index;

                const className = problemVerdicts[problemKey] === 'OK' ? 'ok' : (problemVerdicts[problemKey] ? 'not-ok' : '');

                const row = `
                    <tr class="${className}">
                        <td>${index}</td>
                        <td><a href="${problemUrl}" target="_blank">${name}</a></td>
                        <td>${rating}</td>
                    </tr>
                `;
                problemsTableBody.insertAdjacentHTML('beforeend', row);
            });
        });
    </script>
</body>
</html>
