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
                <div class="container-fluid p-4">
                    <div class="row">
                        <div class="col-12 col-xl-10 mx-auto">
                            <div class="config-container p-4">
                                <form id="alertEngineForm">
                                    <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h4 class="mb-1">Rank Alert Setup</h4>
                                            <p class="text-muted mb-0 small">Configure execution parameters and view
                                                analytical history for keyword tracking.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-muted small">Enable Daily Execution</span>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="alertExecutionSwitch" checked>
                                            </div>
                                            <div class="bg-input p-2 rounded-circle border border-secondary d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <i class="bi bi-exclamation-triangle text-primary"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="automation-card p-4 h-100 rounded">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="bi bi-clock me-2 text-primary"></i>
                                                    <span class="fw-bold">Daily Execution</span>
                                                </div>
                                                <p class="text-muted small mb-4">Set the specific time for the system to
                                                    run
                                                    the audit and dispatch notifications to your team.</p>

                                                <label class="form-label">SCHEDULED TIME</label>
                                                <div class="position-relative">
                                                    <input type="time" id="runTimeInput" class="form-control"
                                                        value="">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div
                                                class="automation-card p-4 h-100 rounded d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex align-items-center mb-3">
                                                        <i class="bi bi-clock-history me-2 text-primary"></i>
                                                        <span class="fw-bold">Historical Data</span>
                                                    </div>
                                                    <p class="text-muted small">Access the complete archive of past rank
                                                        alerts, volatility scores, and keyword movement logs.</p>
                                                </div>
                                                <a href="rank_alert_output.php"
                                                    class="btn w-100 d-flex align-items-center justify-content-center fw-medium"
                                                    style="height: 58px; background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-muted); border-radius: 10px;">
                                                    View Results <i class="bi bi-arrow-right ms-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid mt-4">
                                        <button type="submit" id="alertSubmitBtn"
                                            class="btn py-3 fw-bold d-flex justify-content-center align-items-center"
                                            style="background-color: #a5b4fc; color: #1e1b4b; border-radius: 10px; font-size: 16px;">
                                            <i class="bi bi-lightning-charge-fill me-2"></i> Save Alert Settings
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
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include("footer_link.php"); ?>
    <script src="../js/rank_alert_engine.js?v=<?php echo time(); ?>"></script>

</body>

</html>