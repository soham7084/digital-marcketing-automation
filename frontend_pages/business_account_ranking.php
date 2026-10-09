<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("header_link.php"); ?>
</head>

<body data-section="ranking">

    <div class="dashboard-wrapper">

        <?php include("sidebar-me.php"); ?>

        <main class="main-content">

            <?php include("navbar-me.php"); ?>

            <div class="container-fluid px-4 py-5">

                <div class="hero-box mx-auto p-4 p-md-5" style="max-width: 800px;">

                    <div class="d-flex align-items-center mb-4 pb-3"
                        style="border-bottom: 1px solid var(--border-color);">
                        <div class="icon-box mb-0 me-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">Ranking Engine Configuration</h4>
                    </div>

                    <form id="rankingEngineForm">

                        <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">

                        <div class="mb-5 pt-2">
                            <h6 class="text-uppercase mb-3 d-flex align-items-center"
                                style="color: var(--text-muted); font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-building me-2"></i> BUSINESS IDENTITY
                            </h6>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Business Name</label>
                                    <input type="text" id="business_name" name="business_name" class="form-control"
                                        placeholder="e.g. Acme Corporation" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Location / Zone</label>
                                    <select id="user_location" name="user_location" class="form-select" required>
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
                        </div>

                        <div class="mb-5">
                            <h6 class="text-uppercase mb-3 d-flex align-items-center"
                                style="color: var(--text-muted); font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                                <i class="bi bi-exclamation-circle-fill text-warning me-2"></i> PRIORITY PARAMETERS
                            </h6>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Ranking Sensitivity Level</label>
                                <span class="badge" id="levelDisplay"
                                    style="background-color: #f97316; color: #fff; padding: 6px 14px; border-radius: 50px; font-size: 12px;">Level
                                    3</span>
                            </div>

                            <input type="range" class="form-range" id="priority_min" min="1" max="5" value="3" step="1">

                            <div class="d-flex justify-content-between mt-2"
                                style="color: var(--text-placeholder); font-size: 11px;">
                                <span>1</span>
                                <span>2</span>
                                <span>3</span>
                                <span>4</span>
                                <span>5</span>
                            </div>
                            <small class="d-block mt-2 fst-italic" style="color: var(--text-placeholder);">Scale 1
                                (Broad/Low Priority) to 5 (Precise/High Priority Competitors)</small>
                        </div>

                        <div class="automation-card p-4 rounded-3 mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3"
                                style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <div>
                                    <h6 class="text-uppercase mb-0 d-flex align-items-center"
                                        style="color: var(--text-muted); font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                                        <i class="bi bi-clock-history me-2"></i> AUTOMATION SCHEDULE
                                    </h6>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="form-check-label text-uppercase fw-bold me-3"
                                        style="font-size: 11px; color: var(--text-muted);">
                                        ENABLE DAILY EXECUTION
                                    </label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="ExecutionSwitch" checked>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Execution Time</label>
                                    <div class="input-group">
                                        <input type="time" id="run_time" class="form-control border-end-0"
                                            value="09:00" required>
                                        <span class="input-group-text bg-transparent"><i
                                                class="bi bi-clock text-muted"></i></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Ranking History</label>
                                    <a href="business_account_ranking_output.php"
                                        class="btn w-100 d-flex align-items-center justify-content-center fw-medium"
                                        style="height: 58px; background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-muted); border-radius: 10px;">
                                        View Results <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" id="submitBtn"
                                class="btn py-3 fw-bold d-flex justify-content-center align-items-center"
                                style="background-color: #a5b4fc; color: #1e1b4b; border-radius: 10px; font-size: 16px;">
                                <i class="bi bi-lightning-charge-fill me-2"></i> Execute & Save Configuration
                            </button>
                        </div>
                    </form>
                    <div
                        class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-secondary">

                        <div class="d-flex align-items-center">
                            <span class="status-dot me-2"></span>
                            <span class="small text-muted">Automation Active</span>
                        </div>
                    </div>
                </div>
                <div class="container-fluid p-4">
                    <div class="row">
                        <div class="col-12 col-xl-10 mx-auto">
                            <div class="config-container p-4">

                                <div class="d-grid mt-4">
                                    <a href="rank_alert_engine.php"
                                        class="btn py-3 fw-bold d-flex justify-content-center align-items-center"
                                        style="background-color: #a5b4fc; color: #1e1b4b; border-radius: 10px; font-size: 16px;">
                                        <i class="bi bi-lightning-charge-fill me-2"></i> Save Alert Settings
                                    </a>
                                </div>

                                <div
                                    class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-secondary">

                                    <div class="d-flex align-items-center">
                                        <span class="status-dot me-2"></span>
                                        <span class="small text-muted">Automation Active</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include("footer_link.php"); ?>
    <script src="../js/ranking_engine.js?v=<?php echo time(); ?>"></script>

</body>

</html>