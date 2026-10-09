<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <?php include("header_link.php"); ?>

    <title>Dashboard | SEO & Ranking Suite</title>

</head>

<body data-section="dashboard">
    <!-- Theme Flash Guard Optimization Logic -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <!-- SIDEBAR -->
    <?php include("sidebar-me.php"); ?>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="main-content" id="mainContent">
        
        <!-- TOP NAVIGATION BAR -->
        <?php include("navbar-me.php"); ?>

        <div class="container-fluid p-4 p-lg-5" style="max-width: 1300px;">

            <!-- SYSTEM PARAMETERS ALERTS -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Configuration Saved Successfully
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <!-- HERO INTRO ROW -->
            <section class="row g-4 mb-5 align-items-center">
                <div class="col-lg-12">
                    <span class="custom-badge mb-2 d-inline-block">SEO & Ranking Engine Framework</span>
                    <h1 class="display-6 fw-bold tracking-tight mb-3">Welcome, <?= htmlspecialchars($name) ?></h1>
                    <p class="text-secondary lead fs-6 style-p mb-0" style="max-width: 750px;">
                        Access your dedicated SEO and ranking control suite below. Perform instant site technical audits, extract target search keywords, automate business account ranking tracking, and monitor real-time rank position alerts.
                    </p>
                </div>
            </section>

            <!-- MAIN CORE FEATURE - SEO & RANKING SUITE HUB -->
            <section class="mb-5">
                <div class="custom-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <span class="custom-badge mb-2 d-inline-block">Core Features</span>
                            <h2 class="h3 fw-bold mb-1"><i class="bi bi-cpu-fill text-primary me-2"></i>SEO & Ranking Automation Suite</h2>
                            <p class="text-secondary small mb-0">Select an engine tool below to start auditing, keyword research, or rank tracking.</p>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle fw-semibold px-4" type="button" id="aiHubDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-sliders me-2"></i>Select Feature
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-light" aria-labelledby="aiHubDropdown">
                                <li><button class="dropdown-item active py-2 filter-btn" data-filter="all"><i class="bi bi-grid-fill me-2"></i>Show All Features</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item py-2 filter-btn" data-filter="seo"><i class="bi bi-clipboard-pulse me-2"></i>SEO Audit</button></li>
                                <li><button class="dropdown-item py-2 filter-btn" data-filter="keyword"><i class="bi bi-search me-2"></i>Keyword Research</button></li>
                                <li><button class="dropdown-item py-2 filter-btn" data-filter="ranking"><i class="bi bi-radar me-2"></i>Business Ranking</button></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Filterable Cards Framework Layout -->
                    <div class="row g-4" id="aiCardContainer">
                        
                        <!-- SEO AUDIT CARD -->
                        <div class="col-md-6 col-xl-6 ai-hub-item" data-category="seo">
                            <div class="custom-card ai-card h-100">
                                <div class="icon-box"><i class="bi bi-clipboard-pulse"></i></div>
                                <h3 class="h5 fw-bold mb-2">Technical SEO Audit</h3>
                                <p class="text-secondary small mb-3">Get a comprehensive deep-dive into your website's technical health, visibility, search indexing, and Core Web Vitals performance in under 60 seconds.</p>
                                <a href="seo_audit.php" class="text-primary fw-semibold small text-decoration-none mt-auto">Run SEO Audit &rarr;</a>
                            </div>
                        </div>

                        <!-- KEYWORD RESEARCH CARD -->
                        <div class="col-md-6 col-xl-6 ai-hub-item" data-category="keyword">
                            <div class="custom-card ai-card h-100">
                                <div class="icon-box"><i class="bi bi-search"></i></div>
                                <h3 class="h5 fw-bold mb-2">Google Keyword Research</h3>
                                <p class="text-secondary small mb-3">Uncover high-volume seed keywords, search intent vectors, and competitor keyword opportunities tailored specifically to your target business location.</p>
                                <a href="keyword_research.php" class="text-primary fw-semibold small text-decoration-none mt-auto">Open Keyword Research &rarr;</a>
                            </div>
                        </div>

                        <!-- BUSINESS RANKING CARD -->
                        <div class="col-md-6 col-xl-6 ai-hub-item" data-category="ranking">
                            <div class="custom-card ai-card h-100">
                                <div class="icon-box"><i class="bi bi-radar"></i></div>
                                <h3 class="h5 fw-bold mb-2">Business Account Ranking Engine</h3>
                                <p class="text-secondary small mb-3">Track Google Business Profile local pack rankings, schedule automated daily position scans, and analyze local search presence.</p>
                                <a href="business_account_ranking.php" class="text-primary fw-semibold small text-decoration-none mt-auto">Launch Ranking Engine &rarr;</a>
                            </div>
                        </div>

                        <!-- RANK ALERT ENGINE CARD -->
                        <div class="col-md-6 col-xl-6 ai-hub-item" data-category="ranking">
                            <div class="custom-card ai-card h-100">
                                <div class="icon-box"><i class="bi bi-bell-fill"></i></div>
                                <h3 class="h5 fw-bold mb-2">Rank Alert Engine</h3>
                                <p class="text-secondary small mb-3">Set up automated rank drop and gain alerts to receive instant notifications whenever your target keywords fluctuate in Google search results.</p>
                                <a href="rank_alert_engine.php" class="text-primary fw-semibold small text-decoration-none mt-auto">Configure Rank Alerts &rarr;</a>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- PIPELINE STATUS TRACKS -->
            <section class="mb-5">
                <h2 class="h5 fw-bold mb-3">Engine Infrastructure Status</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="custom-card">
                            <span class="text-uppercase text-secondary tracking-wider small d-block mb-1">SEO Audit Engine Node</span>
                            <h2 class="h4 fw-bold text-success mb-0">Operational</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-card">
                            <span class="text-uppercase text-secondary tracking-wider small d-block mb-1">Keyword Data Scraper</span>
                            <h2 class="h4 fw-bold text-success mb-0">Active / Connected</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-card">
                            <span class="text-uppercase text-secondary tracking-wider small d-block mb-1">Ranking Scraper Cron</span>
                            <h2 class="h4 fw-bold text-success mb-0">Scheduled</h2>
                        </div>
                    </div>
                </div>
            </section>

            <!-- QUICK SHORTCUT DIRECT LINKS -->
            <section class="mb-4">
                <h2 class="h5 fw-bold mb-3">Quick Execution Shortcuts</h2>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="seo_audit.php" class="action-btn-card">
                            <i class="bi bi-clipboard-pulse text-primary"></i> SEO Audit
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="keyword_research.php" class="action-btn-card">
                            <i class="bi bi-search text-primary"></i> Keyword Tool
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="business_account_ranking.php" class="action-btn-card">
                            <i class="bi bi-radar text-primary"></i> Ranking Engine
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="rank_alert_engine.php" class="action-btn-card">
                            <i class="bi bi-bell-fill text-primary"></i> Rank Alerts
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- Bootstrap 5 JavaScript Bundle Core -->
    <?php include("footer_link.php"); ?>
    <!-- UI Core Interactive Logic Layer -->
    <script src="../js/filter.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>