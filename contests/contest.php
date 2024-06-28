<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Contests</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/contest.css">
    
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <h1>Codeforces Contests</h1>
    <div class="filters">
        <label for="contestFilter">Filter by:</label>
        <input type="text" id="contestFilter" placeholder="Search by Contest ID or Name">
        <button type="button" onclick="resetSearch()">Reset</button>
    </div>
    <div class="filters">
        <label for="filterByName">Filter by Name:</label>
        <select id="filterByName" name="filterByName">
            <option value="all">All</option>
            <option value="div 4">Div. 4</option>
            <option value="div 3">Div. 3</option>
            <option value="div 2">Div. 2</option>
            <option value="div 1">Div. 1</option>
            <option value="div 1+2">Div. 1+2</option>
            <option value="edu">Educational</option>
            <option value="global">Global</option>
            <option value="others">Others</option>
        </select>
        <button type="button" onclick="resetFilter()">Reset</button>
    </div>
    <table>
        <thead>
            <tr>
                <th data-sort="id">Contest ID</th>
                <th data-sort="name">Contest Name</th>
                <th data-sort="startTimeSeconds">Start Time</th>
                <th>Actions</th>
                <th data-sort="solved">Solved</th>
            </tr>
        </thead>
        <tbody id="contestTableBody">
            <!-- Contests will be dynamically inserted here -->
        </tbody>
    </table>
    <div class="pagination" id="pagination">
        <!-- Pagination will be dynamically inserted here -->
    </div>
    <script>
let sortOrder = { key: '', order: '' }; 
let allContests = [];

async function fetchContestsFromApi(){
    const cachekey = 'codeforces_contests_cache';
    const cacheTime = 3600 * 1000; // Cache for 1 hour (in milliseconds)
    const cachedData = localStorage.getItem(cachekey);
    const cacheTimestamp = localStorage.getItem(`${cachekey}_timestamp`);

    if(cachedData && cacheTimestamp && (Date.now() - cacheTimestamp < cacheTime)){
        return JSON.parse(cachedData);
    } else {
        const apiUrl = 'https://codeforces.com/api/contest.list';
        const response = await fetch(apiUrl);
        const data = await response.json();

        if(data.status === 'OK'){
            localStorage.setItem(cachekey, JSON.stringify(data.result));
            localStorage.setItem(`${cachekey}_timestamp`, Date.now().toString());
            return data.result;
        } else {
            return null;
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

function getSolvedProblemsByContest(userSubmissions) {
    const solvedProblems = {};
    if (userSubmissions.length > 0) {
        userSubmissions.forEach(submission => {
            if (submission.verdict === 'OK') {
                const contestId = submission.problem.contestId;
                if (!solvedProblems[contestId]) {
                    solvedProblems[contestId] = 0;
                }
                solvedProblems[contestId] += 1;
            }
        });
    }
    return solvedProblems;
}

function sortData(data, key, order) {
    return data.sort((a, b) => {
        if (key === 'solved') {
            a[key] = parseInt(a[key]);
            b[key] = parseInt(b[key]);
        }
        if (a[key] < b[key]) return order === 'asc' ? -1 : 1;
        if (a[key] > b[key]) return order === 'asc' ? 1 : -1;
        return 0;
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

async function displayContests(contests) {
    const perPage = 100; // Number of contests per page
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.has('page') ? Math.max(1, parseInt(urlParams.get('page'))) : 1;
    const offset = (page - 1) * perPage;

    if (!contests) {
        contests = await fetchContestsFromApi();
    }

    if (contests) {
        const filteredContests = contests.filter(contest => contest.phase !== 'BEFORE');
        
        const cfUser = localStorage.getItem('cfUser');
        const userSubmissions = cfUser ? await fetchUserSubmissions(cfUser) : [];
        const solvedProblems = getSolvedProblemsByContest(userSubmissions);

        filteredContests.forEach(contest => {
            contest.solved = solvedProblems[contest.id] || 0;
        });

        const sortedContests = sortOrder.key ? sortData(filteredContests, sortOrder.key, sortOrder.order) : filteredContests;
        const paginatedContests = sortedContests.slice(offset, offset + perPage);

        const contestTableBody = document.getElementById('contestTableBody');
        contestTableBody.innerHTML = '';
        paginatedContests.forEach(contest => {
            const row = `
                <tr>
                    <td>${contest.id}</td>
                    <td>${contest.name}</td>
                    <td>${new Date(contest.startTimeSeconds * 1000).toLocaleString()}</td>
                    <td><a href='problems.php?contestId=${contest.id}'>View Problems</a></td>
                    <td>${contest.solved}</td>
                `;
            contestTableBody.insertAdjacentHTML('beforeend', row);
        });

        // Pagination controls
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredContests.length / perPage);

        if (page > 1) {
            pagination.innerHTML += `<a href='contest.html?page=${page - 1}'>&laquo; Previous</a>`;
        }

        for (let i = 1; i <= totalPages; i++) {
            if (i === page) {
                pagination.innerHTML += `<span class='active'>${i}</span>`;
            } else {
                pagination.innerHTML += `<a href='contest.html?page=${i}'>${i}</a>`;
            }
        }

        if (page < totalPages) {
            pagination.innerHTML += `<a href='contest.html?page=${page + 1}'>Next &raquo;</a>`;
        }
    } else {
        const contestTableBody = document.getElementById('contestTableBody');
        contestTableBody.innerHTML = '<tr><td colspan="5">Failed to fetch contests from Codeforces API.</td></tr>';
    }
}

function filterContests(contests, filterName, searchQuery) {
    return contests.filter(contest => {
        const matchName = contest.name.toLowerCase().includes(searchQuery.toLowerCase());
        const matchId = contest.id.toString().includes(searchQuery);

        const isFiltered = (filterName === 'all' ||
            (filterName === 'div 4' && contest.name.toLowerCase().includes('div. 4')) ||
            (filterName === 'div 3' && contest.name.toLowerCase().includes('div. 3')) ||
            (filterName === 'div 2' && contest.name.toLowerCase().includes('div. 2') && (!contest.name.toLowerCase().includes('educational'))) ||
            (filterName === 'div 1' && contest.name.toLowerCase().includes('div. 1')) ||
            (filterName === 'div 1+2' && (contest.name.toLowerCase().includes('div. 1 + div. 2'))) ||
            (filterName === 'edu' && contest.name.toLowerCase().includes('educational')) ||
            (filterName === 'global' && contest.name.toLowerCase().includes('global')) ||
            (filterName === 'others' && !contest.name.toLowerCase().includes('div. 1') && !contest.name.toLowerCase().includes('div. 2') && !contest.name.toLowerCase().includes('div. 3') && !contest.name.toLowerCase().includes('div. 4') && !contest.name.toLowerCase().includes('educational') && !contest.name.toLowerCase().includes('global'))
        );

        return matchName || matchId && isFiltered;
    });
}

async function filterContestsLive() {
    const searchQuery = document.getElementById('contestFilter').value;
    const filterName = document.getElementById('filterByName').value;

    localStorage.setItem('contestSearchQuery', searchQuery);
    localStorage.setItem('contestFilterName', filterName);

    const allContests = await fetchContestsFromApi();
    const filteredContests = filterContests(allContests, filterName, searchQuery);

    displayContests(filteredContests);
}

function initPage() {
    const searchQuery = localStorage.getItem('contestSearchQuery') || '';
    const filterName = localStorage.getItem('contestFilterName') || 'all';

    document.getElementById('contestFilter').value = searchQuery;
    document.getElementById('filterByName').value = filterName;

    // Add Event Listeners
    document.getElementById('contestFilter').addEventListener('input', filterContestsLive);
    document.getElementById('filterByName').addEventListener('change', filterContestsLive);

    filterContestsLive();
}

document.addEventListener('DOMContentLoaded', async function() {
    let cfUser = localStorage.getItem('cfUser');
    if (!cfUser) {
        cfUser = prompt("Please enter your Codeforces handle:");
        if (cfUser) {
            localStorage.setItem('cfUser', cfUser);
        }
    }
    await displayContests();

    initPage();
});

function resetSearch() {
    document.getElementById('contestFilter').value = '';
    localStorage.removeItem('contestSearchQuery');
    filterContestsLive();
}

function resetFilter() {
    document.getElementById('filterByName').value = 'all';
    localStorage.removeItem('contestFilterName');
    filterContestsLive();
}
</script>


</body>
</html>
