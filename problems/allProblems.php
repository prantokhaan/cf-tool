<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Problems</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/allProblems.css">
    <script>
        let allProblems = [];

        // Function to fetch problems from Codeforces API with caching
        async function fetchProblemsFromApi() {
            const cacheKey = 'codeforces_problems_cache';
            const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                return JSON.parse(cachedData);
            } else {
                try {
                    const apiUrl = "https://codeforces.com/api/problemset.problems";
                    const response = await fetch(apiUrl);

                    if (!response.ok) {
                        throw new Error(`Failed to fetch problems from Codeforces API: ${response.status} ${response.statusText}`);
                    }

                    const data = await response.json();

                    localStorage.setItem(cacheKey, JSON.stringify(data));
                    localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());

                    return data;
                } catch (error) {
                    console.error('Error fetching problems:', error);
                    return null;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', async function() {
            const problemsData = await fetchProblemsFromApi();
            const problems = problemsData.status === 'OK' ? problemsData.result.problems : [];
            allProblems = problems; // Save the full problem list for live filtering

            const tags = await extractTags(problems);
            populateTagsDropdown(tags);
        });

        async function extractTags(problems){
            let tags = new Set();
            problems.forEach(problem => {
                problem.tags.forEach(tag => tags.add(tag));
            });
            return Array.from(tags);
        }

        async function populateTagsDropdown(tags) {
            const select = document.getElementById('filterByTags');
            tags.forEach(tag => {
                const option = document.createElement('option');
                option.value = tag;
                option.textContent = tag;
                select.appendChild(option);
            });
        }

        // Function to fetch user submissions from Codeforces API based on handle with caching
        async function fetchUserSubmissions(handle) {
            const cacheKey = `user_submissions_problems_${handle}`;
            const cacheTime = 5 * 60 * 1000; // Cache for 5 minutes (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                return JSON.parse(cachedData);
            } else {
                try {
                    const apiUrl = `https://codeforces.com/api/user.status?handle=${encodeURIComponent(handle)}`;
                    const response = await fetch(apiUrl);

                    if (!response.ok) {
                        throw new Error(`Failed to fetch user submissions from Codeforces API: ${response.status} ${response.statusText}`);
                    }

                    const data = await response.json();

                    localStorage.setItem(cacheKey, JSON.stringify(data));
                    localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());

                    return data;
                } catch (error) {
                    console.error('Error fetching user submissions:', error);
                    return null;
                }
            }
        }

        // Function to filter problems based on rating, contest ID, name, or problem ID
        function filterProblems(problems, ratingFilter, minContestIdFilter, maxContestIdFilter, searchQuery, filterTags) {
            return problems.filter(problem => {
                const matchRating = (!ratingFilter || problem.rating == ratingFilter);
                const matchContestId = (!minContestIdFilter || problem.contestId >= minContestIdFilter) &&
                                        (!maxContestIdFilter || problem.contestId <= maxContestIdFilter);
                const matchSearch = (!searchQuery || problem.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                                     `${problem.contestId}${problem.index}`.toLowerCase().includes(searchQuery.toLowerCase()));
                const matchTags = (!filterTags || problem.tags.includes(filterTags));
                return matchRating && matchContestId && matchSearch && matchTags;
            });
        }

        // Function to filter problems live as the user types
        async function filterProblemsLive() {
            const searchQuery = document.getElementById('searchQuery').value.toLowerCase();
            const ratingFilter = document.getElementById('rating').value;
            const minContestIdFilter = document.getElementById('minContestId').value;
            const maxContestIdFilter = document.getElementById('maxContestId').value;
            const tagsFilter = document.getElementById('filterByTags').value;

            localStorage.setItem('ratingFilter', ratingFilter);
            localStorage.setItem('minContestIdFilter', minContestIdFilter);
            localStorage.setItem('maxContestIdFilter', maxContestIdFilter);
            localStorage.setItem('searchQuery', searchQuery);
            localStorage.setItem('tagsFilter', tagsFilter);

            const cfUser = localStorage.getItem('cfUser');
            const userSubmissions = cfUser ? await fetchUserSubmissions(cfUser) : null;

            const filteredProblems = filterProblems(allProblems, ratingFilter, minContestIdFilter, maxContestIdFilter, searchQuery, tagsFilter);
            displayProblems(filteredProblems, userSubmissions, 1, 100);
        }

        // Function to display problems on the page
        let sortOrder = { key: '', order: '' }; // Object to keep track of the sort order for each column

        function sortData(data, key, order) {
            return data.sort((a, b) => {
                if (key === 'problemId') {
                    const idA = `${a.contestId}${a.index}`;
                    const idB = `${b.contestId}${b.index}`;
                    if (idA < idB) return order === 'asc' ? -1 : 1;
                    if (idA > idB) return order === 'asc' ? 1 : -1;
                    return 0;
                } else {
                    if (a[key] < b[key]) return order === 'asc' ? -1 : 1;
                    if (a[key] > b[key]) return order === 'asc' ? 1 : -1;
                    return 0;
                }
            });
        }

        function toggleSortOrder(key) {
            if (sortOrder.key === key) {
                sortOrder.order = sortOrder.order === 'asc' ? 'desc' : 'asc';
            } else {
                sortOrder.key = key;
                sortOrder.order = 'asc';
            }
        }

        async function displayProblems(problems, userSubmissions, page, perPage) {
            const tableBody = document.querySelector('tbody');
            tableBody.innerHTML = '';

            if (sortOrder.key) {
                problems = sortData(problems, sortOrder.key, sortOrder.order);
            }

            const start = (page - 1) * perPage;
            const problemsOnPage = problems.slice(start, start + perPage);

            problemsOnPage.forEach(problem => {
                const userSubmissionStatus = userSubmissions ? getUserSubmissionStatus(userSubmissions, problem) : null;
                const tr = document.createElement('tr');

                if (userSubmissionStatus) {
                    tr.classList.add(userSubmissionStatus);
                }

                tr.innerHTML = `
                    <td>${problem.contestId}${problem.index}</td>
                    <td><a href="https://codeforces.com/problemset/problem/${problem.contestId}/${problem.index}" target="_blank">${problem.name}</a></td>
                    <td>${problem.contestId}</td>
                    <td>${problem.rating ? problem.rating : 'N/A'}</td>
                `;

                tableBody.appendChild(tr);
            });

            displayPagination(problems.length, page, perPage);
        }

        // Function to get user submission status for a problem
        function getUserSubmissionStatus(userSubmissions, problem) {
            if (userSubmissions && userSubmissions.status === 'OK') {
                const submissions = userSubmissions.result;
                for (const submission of submissions) {
                    if (submission.problem.contestId === problem.contestId && submission.problem.index === problem.index) {
                        return submission.verdict === 'OK' ? 'solved' : 'attempted';
                    }
                }
            }
            return null;
        }

        // Function to display pagination
        function displayPagination(totalProblems, currentPage, perPage) {
            const pagination = document.querySelector('.pagination');
            pagination.innerHTML = '';

            const totalPages = Math.ceil(totalProblems / perPage);

            if (currentPage > 1) {
                const prevLink = document.createElement('a');
                prevLink.href = '#';
                prevLink.textContent = 'Previous';
                prevLink.addEventListener('click', () => loadPage(currentPage - 1));
                pagination.appendChild(prevLink);
            }

            for (let i = 1; i <= totalPages; i++) {
                const pageLink = document.createElement('a');
                pageLink.href = '#';
                pageLink.textContent = i;
                if (i === currentPage) {
                    pageLink.classList.add('active');
                }
                pageLink.addEventListener('click', () => loadPage(i));
                pagination.appendChild(pageLink);
            }

            if (currentPage < totalPages) {
                const nextLink = document.createElement('a');
                nextLink.href = '#';
                nextLink.textContent = 'Next';
                nextLink.addEventListener('click', () => loadPage(currentPage + 1));
                pagination.appendChild(nextLink);
            }
        }

        // Function to load a specific page
        async function loadPage(page) {
            const ratingFilter = localStorage.getItem('ratingFilter') || '';
            const minContestIdFilter = localStorage.getItem('minContestIdFilter') || '';
            const maxContestIdFilter = localStorage.getItem('maxContestIdFilter') || '';
            const searchQuery = localStorage.getItem('searchQuery') || '';
            const tagsFilter = localStorage.getItem('tagsFilter') || '';
            const cfUser = localStorage.getItem('cfUser');

            const problemsData = await fetchProblemsFromApi();
            const problems = problemsData.status === 'OK' ? problemsData.result.problems : [];
            allProblems = problems; // Save the full problem list for live filtering
            const filteredProblems = filterProblems(problems, ratingFilter, minContestIdFilter, maxContestIdFilter, searchQuery, tagsFilter);

            const userSubmissions = cfUser ? await fetchUserSubmissions(cfUser) : null;

            displayProblems(filteredProblems, userSubmissions, page, 100);
        }

        // Function to show a random problem
        function showRandomProblem() {
            const ratingFilter = localStorage.getItem('ratingFilter') || '';
            const minContestIdFilter = localStorage.getItem('minContestIdFilter') || '';
            const maxContestIdFilter = localStorage.getItem('maxContestIdFilter') || '';
            const tagsFilter = localStorage.getItem('tagsFilter') || '';

            let url = 'randomProblem.php';
            if (ratingFilter || minContestIdFilter || maxContestIdFilter || tagsFilter) {
                url += '?';
                if (ratingFilter) url += `rating=${ratingFilter}`;
                if (minContestIdFilter) url += `&minContestId=${minContestIdFilter}`;
                if (maxContestIdFilter) url += `&maxContestId=${maxContestIdFilter}`;
                if(tagsFilter) url += `&tags=${tagsFilter}`;
            }

            window.location.href = url;
        }

        // Function to initialize the page
        function initPage() {
            // Restore filters from localStorage
            document.getElementById('rating').value = localStorage.getItem('ratingFilter') || '';
            document.getElementById('minContestId').value = localStorage.getItem('minContestIdFilter') || '';
            document.getElementById('maxContestId').value = localStorage.getItem('maxContestIdFilter') || '';
            document.getElementById('searchQuery').value = localStorage.getItem('searchQuery') || '';
            document.getElementById('filterByTags').value = localStorage.getItem('tagsFilter') || '';

            // Add event listeners
            document.getElementById('filterRating').addEventListener('click', () => {
                localStorage.setItem('ratingFilter', document.getElementById('rating').value);
                loadPage(1);
            });

            document.getElementById('resetRating').addEventListener('click', () => {
                localStorage.removeItem('ratingFilter');
                document.getElementById('rating').value = '';
                loadPage(1);
            });

            document.getElementById('filterContestId').addEventListener('click', () => {
                localStorage.setItem('minContestIdFilter', document.getElementById('minContestId').value);
                localStorage.setItem('maxContestIdFilter', document.getElementById('maxContestId').value);
                loadPage(1);
            });

            document.getElementById('resetContestId').addEventListener('click', () => {
                localStorage.removeItem('minContestIdFilter');
                localStorage.removeItem('maxContestIdFilter');
                document.getElementById('minContestId').value = '';
                document.getElementById('maxContestId').value = '';
                loadPage(1);
            });

            document.querySelector('.random-problem-btn').addEventListener('click', showRandomProblem);

            document.getElementById('search').addEventListener('click', () => {
                localStorage.setItem('searchQuery', document.getElementById('searchQuery').value);
                loadPage(1);
            });

            document.getElementById('resetSearch').addEventListener('click', () => {
                localStorage.removeItem('searchQuery');
                document.getElementById('searchQuery').value = '';
                loadPage(1);
            });

            document.getElementById('searchQuery').addEventListener('input', filterProblemsLive);

            document.getElementById('rating').addEventListener('input', filterProblemsLive);

            document.getElementById('filterByTags').addEventListener('input', () => {
                localStorage.setItem('tagsFilter', document.getElementById('filterByTags').value);
                loadPage(1);
            });

            document.getElementById('resetFilterTags').addEventListener('click', () => {
                localStorage.removeItem('tagsFilter');
                document.getElementById('filterByTags').value = '';
                loadPage(1);
            });

            // Load the first page initially
            loadPage(1);
        }

        // Initialize the page when DOM is fully loaded
        document.addEventListener('DOMContentLoaded', initPage);

        document.querySelectorAll('th[data-sort]').forEach(header => {
    header.addEventListener('click', async function() {
        console.log('clicked');
        const key = this.getAttribute('data-sort');
        toggleSortOrder(key);
        const ratingFilter = localStorage.getItem('ratingFilter') || '';
        const minContestIdFilter = localStorage.getItem('minContestIdFilter') || '';
        const maxContestIdFilter = localStorage.getItem('maxContestIdFilter') || '';
        const searchQuery = localStorage.getItem('searchQuery') || '';
        const cfUser = localStorage.getItem('cfUser');

        const problemsData = await fetchProblemsFromApi();
        const problems = problemsData.status === 'OK' ? problemsData.result.problems : [];
        allProblems = problems; // Save the full problem list for live filtering
        const filteredProblems = filterProblems(problems, ratingFilter, minContestIdFilter, maxContestIdFilter, searchQuery);

        const userSubmissions = cfUser ? await fetchUserSubmissions(cfUser) : null;

        displayProblems(filteredProblems, userSubmissions, 1, 100);
    });
});

    </script>
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <div class="container">
        <h1>Codeforces Problems</h1>

        <form id="filterForm" class="filter-form">
            <div class="filter-group">
                <label for="rating">Filter by Rating:</label>
                <select id="rating" name="rating">
                    <option value="">All</option>
                </select>
                <button type="button" id="filterRating">Filter</button>
                <button type="button" id="resetRating">Reset Rating</button>
            </div>
            <div class="filter-group">
                <label for="minContestId">Filter by Contest ID (min):</label>
                <input type="number" id="minContestId" name="minContestId">
                <label for="maxContestId">Max:</label>
                <input type="number" id="maxContestId" name="maxContestId">
                <button type="button" id="filterContestId">Filter by Contest ID</button>
                <button type="button" id="resetContestId">Reset Contest ID</button>
            </div>
            <div class="filter-group">
                <label for="searchQuery">Search:</label>
                <input type="text" id="searchQuery" name="searchQuery">
                <button type="button" id="search">Search</button>
                <button type="button" id="resetSearch">Reset Search</button>
            </div>
            <div class="filter-group">
                <label for="filterByTags">Filter by Tags:</label>
                <select id="filterByTags" name="filterByTags">
                    <option value="">All</option>
                </select>
                <button type="button" id="resetFilterTags">Reset Tags</button>
            </div>
            <input type="button" class="random-problem-btn" value="Random Problem">
        </form>

        <table>
            <thead>
                <tr>
                    <th data-sort="problemId">Problem ID</th>
                    <th data-sort="name">Problem Name</th>
                    <th data-sort="contestId">Contest ID</th>
                    <th data-sort="rating">Rating</th>
                </tr>
            </thead>
            <tbody>
                <!-- Problems will be dynamically inserted here -->
            </tbody>
        </table>

        <div class="pagination">
            <!-- Pagination links will be dynamically inserted here -->
        </div>
    </div>

    <script>
        const selectRating = document.getElementById('rating');
        for (let i = 800; i <= 3500; i += 100) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i;
            selectRating.appendChild(option);
        }
    </script>
</body>
</html>
