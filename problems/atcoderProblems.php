<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atcoder Problems</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/allProblems.css">
    <link rel="stylesheet" href="../shared/loader.css">
    <link rel="favicon" href="../images/favicon.png">
    <script>
        let allProblems = [];

        async function fetchProblemsFromApi() {
            document.body.classList.add('loading');
            const cacheKey = 'atcoder_problems_cache';
            const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                document.body.classList.remove('loading');
                return JSON.parse(cachedData);
            } else {
                try {
                    const apiUrl = "https://kenkoooo.com/atcoder/resources/problems.json";
                    const response = await fetch(apiUrl);

                    if (!response.ok) {
                        throw new Error(`Failed to fetch problems from Atcoder API: ${response.status} ${response.statusText}`);
                    }

                    const data = await response.json();
                    localStorage.setItem(cacheKey, JSON.stringify(data));
                    localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());
                    document.body.classList.remove('loading');
                    return data;
                } catch (error) {
                    console.error('Error fetching problems:', error);
                    document.body.classList.remove('loading');
                    return null;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', async function() {
            const problemsData = await fetchProblemsFromApi();
            allProblems = problemsData; // Save the full problem list for live filtering

            initPage();
            loadPage(1); // Load the first page after initializing
        });

        function filterProblems(problems, searchQuery) {
            return problems.filter(problem => {
                const matchSearch = (!searchQuery || problem.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                     `${problem.contest_id}${problem.problem_index}`.toLowerCase().includes(searchQuery.toLowerCase()));
                return matchSearch;
            });
        }

        async function filterProblemsLive() {
            const searchQuery = document.getElementById('searchQuery').value.toLowerCase();
            localStorage.setItem('searchQuery', searchQuery);
            const filteredProblems = filterProblems(allProblems, searchQuery);
            displayProblems(filteredProblems, 1, 100);
        }

        async function displayProblems(problems, page, perPage) {
            const tableBody = document.querySelector('tbody');
            tableBody.innerHTML = '';

            const start = (page - 1) * perPage;
            const end = start + perPage;
            const paginatedProblems = problems.slice(start, end);

            paginatedProblems.forEach(problem => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${problem.contest_id}${problem.problem_index}</td>
                    <td>${problem.name}</td>
                    <td>${problem.contest_id}</td>
                `;

                tableBody.appendChild(row);
            });

            renderPagination(problems.length, page, perPage);
        }

        function renderPagination(totalProblems, currentPage, perPage) {
            const totalPages = Math.ceil(totalProblems / perPage);
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const button = document.createElement('button');
                button.textContent = i;
                button.classList.toggle('active', i === currentPage);
                button.addEventListener('click', () => loadPage(i));
                pagination.appendChild(button);
            }
        }

        async function initPage() {
            const searchQuery = localStorage.getItem('searchQuery') || '';
            document.getElementById('searchQuery').value = searchQuery;
            document.getElementById('searchQuery').addEventListener('input', filterProblemsLive);
        }

        function loadPage(page) {
            document.body.classList.add('loading');
            const searchQuery = document.getElementById('searchQuery').value.toLowerCase();
            const filteredProblems = filterProblems(allProblems, searchQuery);
            displayProblems(filteredProblems, page, 100);
            document.body.classList.remove('loading');
        }
    </script>
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <div class="container">
        <h1>Atcoder Problems</h1>

        <form id="filterForm" class="filter-form">
            <div class="filter-group">
                <label for="searchQuery">Search:</label>
                <input type="text" id="searchQuery" name="searchQuery">
                <button type="button" id="search">Search</button>
                <button type="button" id="resetSearch">Reset Search</button>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Problem ID</th>
                    <th>Problem Name</th>
                    <th>Contest ID</th>
                </tr>
            </thead>
            <tbody>
                <!-- Problems will be dynamically inserted here -->
            </tbody>
        </table>

        <div class="pagination" id="pagination">
            <!-- Pagination links will be dynamically inserted here -->
        </div>
    </div>
</body>
</html>
