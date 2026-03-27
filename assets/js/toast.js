function showToast(message, type = "success") {
    const toast = document.getElementById("toast");

    toast.innerText = message;
    toast.className = "toast " + type;
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}