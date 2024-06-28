const logOutButton = document.getElementById("logout");
logOutButton.addEventListener("click", function (){
    logOut();
});

function logOut(){
    localStorage.removeItem("userName");
    // remove cookie with name cfUser
    document.cookie = "cfUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    window.location.href = "../auth/login.php";
}