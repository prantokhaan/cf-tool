<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeforces Profile</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="favicon" href="../images/favicon.png">
    <link rel="stylesheet" href="./visualize/tags.css">
    <script>
        // Function to fetch user info from Codeforces API
        async function fetchUserInfo(handle) {
            const apiUrl = `https://codeforces.com/api/user.info?handles=${encodeURIComponent(handle)}`;
            try {
                const response = await fetch(apiUrl);
                const data = await response.json();
                if (data.status === 'OK') {
                    return data.result[0];
                }
                throw new Error('Failed to fetch user data');
            } catch (error) {
                console.error('Error fetching user info:', error);
                return null;
            }
        }

        // Function to display user info
        function displayUserInfo(userInfo) {
            const profileInfoContainer = document.querySelector('.profile-info-container');
            const { handle, rating, rank, titlePhoto, maxRating, maxRank } = userInfo;

            // Determine the rating category
            let ratingClass = 'newbie'; // Default to newbie
            switch (rank) {
                case 'pupil':
                    ratingClass = 'pupil';
                    break;
                case 'specialist':
                    ratingClass = 'specialist';
                    break;
                case 'expert':
                    ratingClass = 'expert';
                    break;
                case 'candidate master':
                case 'master':
                case 'international master':
                    ratingClass = 'candidate-master';
                    break;
                case 'grandmaster':
                case 'international grandmaster':
                case 'legendary grandmaster':
                    ratingClass = 'grandmaster';
                    break;
            }

            profileInfoContainer.classList.add(ratingClass);
            profileInfoContainer.innerHTML = `
                <h2>Profile Information</h2>
                <p><strong>Codeforces Handle:</strong> ${handle}</p>
                <p><strong>Rating:</strong> <span class="profile-rating">${rating}</span></p>
                <p><strong>Rank:</strong> ${rank}</p>
                <p><strong>Max Rating: ${maxRating} </strong></p>
                <p><strong>Max Rating: ${maxRank} </strong></p>
            `;
        }

        // Function to initialize the profile page
        async function initProfile() {
            const cfUser = localStorage.getItem('cfUser');
            if (cfUser) {
                const userInfo = await fetchUserInfo(cfUser);
                if (userInfo) {
                    console.log(userInfo);
                    displayUserInfo(userInfo);
                } else {
                    document.querySelector('.profile-info-container').innerHTML = '<p>Failed to fetch user information from Codeforces API.</p>';
                }
            } else {
                document.querySelector('.profile-info-container').innerHTML = '<p>No Codeforces handle found in localStorage.</p>';
            }
        }

        // Function to clear localStorage and redirect to login page on logout
        function logout() {
            localStorage.removeItem('cfUser');
            window.location.href = "../auth/login.php";
        }

        // Initialize the profile page when the window loads
        window.addEventListener('DOMContentLoaded', initProfile);
    </script>
</head>
<body>
    <?php include '../shared/nav.php'; ?>

    <div class="container">
        <h1>Codeforces Profile</h1>
        <div class="profile-info">
            <div class="profile-info-container">
                <!-- Profile information will be populated here -->
            </div>
        </div>
    </div>
    <div style="margin-top: 10px">
        <?php include './visualize/visualize.php'; ?>
    </div>
    <div style="margin-top: 10px;">
        <?php include './visualize/tags_visualize.php'; ?>
    </div>
</body>
</html>
