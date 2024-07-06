<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Problems</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/allProblems.css">
    <link rel="stylesheet" href="../shared/loader.css">
    <link rel="favicon" href="../images/favicon.png">
    <script>
        let allProblems = [];

        async function fetchProblemsFromApi() {
            document.body.classList.add('loading');
            const cacheKey = 'codeforces_problems_cache';
            const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                document.body.classList.remove('loading');
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
            const problems = problemsData.status === 'OK' ? problemsData.result.problems : [];
            allProblems = problems; // Save the full problem list for live filtering

            const tags = await extractTags(problems);
            populateTagsDropdown(tags);

            initPage();
            loadPage(1); // Load the first page after initializing
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

        async function fetchUserSubmissions(handle) {
            const cacheKey = `user_submissions_${handle}`;
            const cacheTime = 5 * 60 * 1000; // Cache for 5 minutes (in milliseconds)
            const cachedData = localStorage.getItem(cacheKey);
            const cacheTimestamp = localStorage.getItem(`${cacheKey}_timestamp`);

            if (cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)) {
                return JSON.parse(cachedData); // Ensure cached data is returned in the same format
            } else {
                try {
                    const apiUrl = `https://codeforces.com/api/user.status?handle=${encodeURIComponent(handle)}`;
                    const response = await fetch(apiUrl);

                    if (!response.ok) {
                        throw new Error(`Failed to fetch user submissions from Codeforces API: ${response.status} ${response.statusText}`);
                    }

                    const data = await response.json();
                    const result = data.result; // Store only the result

                    localStorage.setItem(cacheKey, JSON.stringify(result)); // Store only the result in localStorage
                    localStorage.setItem(`${cacheKey}_timestamp`, Date.now().toString());

                    return result; // Return only the result
                } catch (error) {
                    console.error('Error fetching user submissions:', error);
                    return null;
                }
            }
        }

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

    const start = (page - 1) * perPage;
    const end = start + perPage;
    const paginatedProblems = problems.slice(start, end);

    paginatedProblems.forEach(problem => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${problem.contestId}${problem.index}</td>
            <td>${problem.name}</td>
            <td>${problem.contestId}</td>
            <td>${problem.rating || 'N/A'}</td>
            <td>
                <a href="start_solving.php?contestId=${problem.contestId}&index=${problem.index}&name=${encodeURIComponent(problem.name)}&rating=${problem.rating ? problem.rating : 'N/A'}">Start Solving</a>
                <span>||</span>
                <a href="add_to_solved.php?contestId=${problem.contestId}&index=${problem.index}&name=${encodeURIComponent(problem.name)}&rating=${problem.rating ? problem.rating : 'N/A'}">Add to Solved</a>
            </td>
        `;

        const problemId = `${problem.contestId}${problem.index}`;

        if (userSubmissions && userSubmissions.some(sub => sub.problem.contestId == problem.contestId && sub.problem.index == problem.index)) {
            row.style.backgroundColor = 'lightgreen'; // Highlight solved problems
        }

        tableBody.appendChild(row);
    });

    renderPagination(problems.length, page, perPage);
}


function renderPagination(totalProblems, currentPage, perPage) {
    const pagination = document.getElementById('pagination');
    if (!pagination) {
        console.error('Pagination element not found.');
        return;
    }

    pagination.innerHTML = ''; // Clear previous pagination buttons

    const totalPages = Math.ceil(totalProblems / perPage);
    
    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.textContent = i;
        button.classList.toggle('active', i === currentPage);
        button.addEventListener('click', () => loadPage(i));
        pagination.appendChild(button);
    }

    if(currentPage>1){
        const prevButton = document.createElement('button');
        prevButton.textContent = 'Prev';
        prevButton.addEventListener('click', () => loadPage(currentPage-1));
        pagination.insertBefore(prevButton, pagination.firstChild);
    }

    if(currentPage<totalPages){
        const nextButton = document.createElement('button');
        nextButton.textContent = 'Next';
        nextButton.addEventListener('click', () => loadPage(currentPage+1));
        pagination.appendChild(nextButton);
    }
}



        async function initPage() {
            const searchQuery = localStorage.getItem('searchQuery') || '';
            const ratingFilter = localStorage.getItem('ratingFilter') || '';
            const minContestIdFilter = localStorage.getItem('minContestIdFilter') || '';
            const maxContestIdFilter = localStorage.getItem('maxContestIdFilter') || '';
            const tagsFilter = localStorage.getItem('tagsFilter') || '';

            document.getElementById('searchQuery').value = searchQuery;
            document.getElementById('rating').value = ratingFilter;
            document.getElementById('minContestId').value = minContestIdFilter;
            document.getElementById('maxContestId').value = maxContestIdFilter;
            document.getElementById('filterByTags').value = tagsFilter;

            document.getElementById('searchQuery').addEventListener('input', filterProblemsLive);
            document.getElementById('rating').addEventListener('change', filterProblemsLive);
            document.getElementById('minContestId').addEventListener('input', filterProblemsLive);
            document.getElementById('maxContestId').addEventListener('input', filterProblemsLive);
            document.getElementById('filterByTags').addEventListener('change', filterProblemsLive);
        }

        function loadPage(page) {
    document.body.classList.add('loading');
    const searchQuery = document.getElementById('searchQuery').value.toLowerCase();
    const ratingFilter = document.getElementById('rating').value;
    const minContestIdFilter = document.getElementById('minContestId').value;
    const maxContestIdFilter = document.getElementById('maxContestId').value;
    const tagsFilter = document.getElementById('filterByTags').value;

    const cfUser = localStorage.getItem('cfUser');
    fetchUserSubmissions(cfUser).then(userSubmissions => {
        const filteredProblems = filterProblems(allProblems, ratingFilter, minContestIdFilter, maxContestIdFilter, searchQuery, tagsFilter);
        displayProblems(filteredProblems, userSubmissions, page, 100);
        document.body.classList.remove('loading');
    });
}

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
                    <th>Action</th>
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
