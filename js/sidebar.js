const sidebar = document.getElementById("sidebar");
const mainContent = document.querySelector(".main-content") || document.getElementById("mainContent");
const sidebarToggle = document.getElementById("sidebarToggle");
const closeSidebarBtn = document.getElementById("closeSidebarBtn");

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        if (window.innerWidth > 992) {
            sidebar.classList.toggle("collapsed");
            if (mainContent) mainContent.classList.toggle("expanded");
        } else {
            sidebar.classList.toggle("show-mobile");
        }
    });
}

if (closeSidebarBtn && sidebar) {
    closeSidebarBtn.addEventListener("click", () => {
        sidebar.classList.remove("show-mobile");
    });
}