function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "block";
}

function closeModal() {
    document.getElementById("logoutModal").style.display = "none";
}

function confirmLogout() {
    window.location.href = "../auth/logout.php";
}

