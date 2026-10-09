document.addEventListener("DOMContentLoaded", () => {

    // ==================================================
    // 1. Theme Toggle
    // ==================================================
    const bodyElement = document.body;
    const themeToggleBtns = document.querySelectorAll("#theme-toggle, #themeToggleBtn, .theme-toggle-btn");
    const activeTheme = localStorage.getItem("theme");

    function updateThemeUI(isLight) {
        if (isLight) {
            bodyElement.classList.add("light-theme");
        } else {
            bodyElement.classList.remove("light-theme");
        }

        // Update single-icon elements if present (e.g. #themeIcon in navbar.php)
        const themeIcons = document.querySelectorAll("#themeIcon");
        themeIcons.forEach(icon => {
            if (isLight) {
                icon.classList.remove("bi-moon-stars-fill", "bi-moon-stars");
                icon.classList.add("bi-sun-fill");
            } else {
                icon.classList.remove("bi-sun-fill", "bi-sun");
                icon.classList.add("bi-moon-stars-fill");
            }
        });
    }

    // Apply saved theme on load
    if (activeTheme === "light") {
        updateThemeUI(true);
    } else {
        updateThemeUI(false);
    }

    // Attach click listeners to all theme toggle buttons
    themeToggleBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            const isLight = !bodyElement.classList.contains("light-theme");
            updateThemeUI(isLight);
            localStorage.setItem("theme", isLight ? "light" : "dark");
        });
    });

    // ==================================================
    // 2. Sidebar Toggle
    // ==================================================
    const sidebarToggle = document.getElementById("sidebarToggle");
    const closeSidebarBtn = document.getElementById("closeSidebarBtn");
    const sidebar = document.getElementById("sidebar") || document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content") || document.getElementById("mainContent");

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (window.innerWidth > 992) {
                sidebar.classList.toggle("collapsed");
                if (mainContent) {
                    mainContent.classList.toggle("expanded");
                }
            } else {
                sidebar.classList.toggle("show-mobile");
            }
        });
    }

    if (closeSidebarBtn && sidebar) {
        closeSidebarBtn.addEventListener("click", (e) => {
            e.preventDefault();
            sidebar.classList.remove("show-mobile");
        });
    }

    // Close mobile sidebar on outside click
    document.addEventListener("click", (e) => {
        if (
            window.innerWidth <= 992 &&
            sidebar &&
            sidebar.classList.contains("show-mobile") &&
            !sidebar.contains(e.target) &&
            sidebarToggle &&
            !sidebarToggle.contains(e.target)
        ) {
            sidebar.classList.remove("show-mobile");
        }
    });

    // ==================================================
    // 3. Sidebar Active Menu Highlight
    // ==================================================
    const currentSection = document.body.dataset.section;
    const pageNameElement = document.getElementById("page_name");

    document.querySelectorAll(".sidebar-menu a, .menu-link").forEach(link => {
        if (currentSection && link.dataset.section === currentSection) {
            link.classList.add("active");
            if (pageNameElement && link.dataset.page) {
                pageNameElement.textContent = link.dataset.page;
            }
        }
    });

});