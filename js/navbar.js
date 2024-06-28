function checkLoginStatusForNavBar(){
    var isLoggedIn = true;
    var userName = localStorage.getItem("username");
    console.log(userName);
    if(!userName){
        isLoggedIn = false;
    }

    if(isLoggedIn){
        document.getElementById("login").style.display = "none";
        document.getElementById("register").style.display = "none";
        document.getElementById("logout").style.display = "inline-block";
        document.getElementById("profile-link").style.display = "inline-block";

        // document.querySelector(".auth-button #profile-link span").textContent = userName;
        document.getElementById("profile-link").textContent = userName;
    }else{
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

  // Redirect to the login page
  window.location.href = "../auth/login.php";
}
