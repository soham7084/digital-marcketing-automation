<aside class="sidebar" id="sidebar">

    <div class="p-4 d-flex justify-content-between align-items-center">
        <div class="sidebar-logo">
            <i class="bi bi-stars me-2"></i>
            SEO & Ranking Suite
        </div>
        <button class="btn d-lg-none text-secondary p-0" id="closeSidebarBtn">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <div class="px-3 d-flex flex-column justify-content-between h-75">
        <div class="nav flex-column gap-1">
            <!-- Dashboard -->
            <a href="dashboard_front.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard_front.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                Dashboard
            </a>

            <!-- CORE FEATURES -->
            <div class="mt-3 mb-2 text-uppercase small fw-semibold text-secondary px-3">
                Core Features
            </div>

            <a href="seo_audit.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'seo_audit.php' ? 'active' : '' ?>">
                <i class="bi bi-clipboard-pulse"></i>
                SEO Audit
            </a>

            <a href="keyword_research.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'keyword_research.php' ? 'active' : '' ?>">
                <i class="bi bi-search"></i>
                Keyword Research
            </a>

            <a href="business_account_ranking.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'business_account_ranking.php' ? 'active' : '' ?>">
                <i class="bi bi-radar"></i>
                Business Ranking
            </a>

            <a href="rank_alert_engine.php" class="menu-link <?= basename($_SERVER['PHP_SELF']) == 'rank_alert_engine.php' ? 'active' : '' ?>">
                <i class="bi bi-bell-fill"></i>
                Rank Alerts
            </a>
        </div>

        <!-- LOGOUT -->
        <div class="pt-4 border-top">
            <a href="logout.php" class="menu-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </div>
    </div>

</aside>