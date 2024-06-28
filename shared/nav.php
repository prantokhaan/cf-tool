<div>
  <div class="navbar" id="navbar">
    <a href="../index.php">Home</a>
    <a href="../contests/contest.php">Contests</a>
    <a href="../problems/allProblems.php">Problems</a>
    <a href="../auth/login.php" id="login">Login</a>
    <a href="../auth/register.php" id="register">Register</a>
    <div class="dropdown">
      <button class="dropbtn"><i class="fas fa-user"></i> <span id="profile-link"></span></button>
      <div class="dropdown-content">
        <a href="../profile/profile.php">My Profile</a>
        <a href="../profile/change_password.php">Change Password</a>
        <a href="../profile/change_handle.php">Change CF Handle</a>
      </div>
    </div>
    <a href="#" id="logout">Logout</a>
    <a href="javascript:void(0);" class="icon" onclick="toggleNavbar()">
      <i class="fa fa-bars"></i>
    </a>
  </div>

  <!-- Placeholder for content below the navbar area -->
  <div class="content">
    <!-- Your main content here -->
  </div>

  <script>
    function checkLoginStatusForNavBar() {
      var isLoggedIn = true;
      var userName = localStorage.getItem("username");
      console.log(userName);
      if (!userName) {
        isLoggedIn = false;
      }

      if (isLoggedIn) {
        document.getElementById("login").style.display = "none";
        document.getElementById("register").style.display = "none";
        document.getElementById("logout").style.display = "inline-block";
        document.getElementById("profile-link").style.display = "inline-block";
        document.getElementById("profile-link").textContent = userName;
      } else {
        document.getElementById("login").style.display = "inline-block";
        document.getElementById("register").style.display = "inline-block";
        document.getElementById("logout").style.display = "none";
        document.getElementById("profile-link").style.display = "none";
      }
    }

    window.onload = function () {
      checkLoginStatusForNavBar();
    };

    const logOutButton = document.getElementById("logout");
    logOutButton.addEventListener("click", function () {
      logout();
    });

    function logout() {
      // Remove username from localStorage
      localStorage.removeItem("username");
      localStorage.removeItem("cfUser");
      document.cookie = "cfUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      // Redirect to the login page
      window.location.href = "../auth/login.php";
    }

    function toggleNavbar() {
      var navbar = document.getElementById("navbar");
      if (navbar.className === "navbar") {
        navbar.className += " responsive";
      } else {
        navbar.className = "navbar";
      }
    }
  </script>
</div>
