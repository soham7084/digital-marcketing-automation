<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("header_link.php"); ?>
</head>

<body data-section="keyword">

    <div class="dashboard-wrapper">

        <?php include("sidebar-me.php"); ?>

        <main class="main-content">

            <?php include("navbar-me.php"); ?>

            <div class="container-fluid px-4 py-5">

                <div class="text-center mb-5">
                    <span class="badge hero-badge text-uppercase tracking-wider">
                        Google Keyword Research Engine
                    </span>
                    <h1 class="hero-title mt-3 fs-2 fw-bold">
                        Powerful Google Search insights for any business niche
                    </h1>
                </div>

                <div class="hero-box mx-auto p-4 p-md-5" style="max-width: 800px;">

                    <form id="keywordEngineForm">

                        <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-folder-fill"></i> Project Topic
                            </label>
                            <input type="text" id="project_topic" name="project_topic" class="form-control"
                                placeholder="e.g., AI Software, Real Estate, Fitness, SaaS..." required>
                        </div>

                        <div class="mb-4 position-relative">
                            <label class="form-label">
                                <i class="bi bi-key-fill"></i> Seed Keywords
                            </label>
                            <textarea id="seed_keywords" name="seed_keywords" class="form-control" rows="4"
                                placeholder="Enter keywords separated by commas..." required></textarea>
                            <span
                                class="badge position-absolute bottom-0 end-0 m-3 bg-warning bg-opacity-10 text-warning">
                                INPUT READY
                            </span>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-translate"></i> Target Language
                                </label>
                                <select id="language" class="form-select">
                                    <option value="EN" selected>English (EN)</option>
                                    <option value="ES">Spanish (ES)</option>
                                    <option value="FR">French (FR)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-geo-alt-fill"></i> Business Location
                                </label>
                                <select id="location" class="form-select">
                                    <option value="" disabled selected>Select Location...</option>
                                    <option value="Kolkata, West Bengal, India">Kolkata, West Bengal</option>
                                    <option value="Mumbai, Maharashtra, India">Mumbai, Maharashtra</option>
                                    <option value="Delhi, Delhi, India">Delhi, Delhi</option>
                                    <option value="Bangalore, Karnataka, India">Bangalore, Karnataka</option>
                                    <option value="Chennai, Tamil Nadu, India">Chennai, Tamil Nadu</option>
                                    <option value="Hyderabad, Telangana, India">Hyderabad, Telangana</option>
                                    <option value="Ahmedabad, Gujarat, India">Ahmedabad, Gujarat</option>
                                    <option value="Pune, Maharashtra, India">Pune, Maharashtra</option>
                                    <option value="Jaipur, Rajasthan, India">Jaipur, Rajasthan</option>
                                    <option value="Kochi, Kerala, India">Kochi, Kerala</option>
                                </select>
                            </div>
                        </div>

                        <div class="automation-card p-4 rounded-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Automation & Scheduling</h6>
                                    <small class="text-muted">Recurrent keyword tracking</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="executionSwitch" checked>
                                    <label class="form-check-label">
                                        Enable Daily Execution
                                    </label>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Execution Time</label>
                                    <input type="time" id="executionTime" class="form-control" value="10:00">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label d-none d-sm-block">&nbsp;</label>
                                    <a href="keyword_output.php"
                                        class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center fw-medium shadow-sm"
                                        style="height: 45px;">
                                        <i class="bi bi-box-arrow-up-right me-2"></i> View Saved Data
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="submitBtn" class="btn btn-primary-gradient py-3 fw-bold">
                                Save & Schedule Execution <i class="bi bi-calendar-check-fill"></i>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </main>
    </div>

    <?php include("footer_link.php"); ?>
    <script src="../js/keyword_research.js?v=<?php echo time(); ?>"></script>

</body>

</html>