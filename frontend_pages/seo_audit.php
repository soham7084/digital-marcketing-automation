<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include("header_link.php");
    ?>
</head>

<body data-section="seo">
    <div class="dashboard-wrapper">
        <?php
        include("sidebar-me.php");
        ?>
        <main class="main-content">

            <?php
            include("navbar-me.php");
            ?>

            <section class="hero-section position-relative">
                <div class="container">
                    <div class="hero-box mx-auto text-center">
                        <span class="badge hero-badge">
                            AI POWERED SEO ENGINE
                        </span>
                        <h1 class="hero-title mt-4">
                            Unlock Your <span>SEO</span> <br> Potential
                        </h1>
                        <p class="hero-text">
                            Get a comprehensive deep-dive into your website's technical health, visibility and
                            performance in under 60 seconds.
                        </p>

                        <form class="audit-form mt-5" id="auditForm">
                            <input type="hidden" id="userId" value="<?php echo htmlspecialchars($id); ?>">
                            <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">
                            
                            <div class="mb-3 text-start">
                                <label class="form-label">Website URL</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                    <input type="text" id="websiteUrl" class="form-control"
                                        placeholder="https://yourwebsite.com">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-lg-6 text-start">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" id="emailAddress" class="form-control"
                                            placeholder="john@example.com">
                                    </div>
                                </div>
                                <div class="col-lg-6 text-start">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label mb-0">Daily Execution Time</label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="executionSwitch" checked>
                                            <label class="form-check-label text-muted small ms-1" for="executionSwitch">Enable Schedule</label>
                                        </div>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                        <input type="time" id="executionTime" class="form-control" value="09:00">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" id="runAuditBtn" class="btn btn-warning hero-btn w-100">
                                    Run Audit & Save Schedule <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>

                        <small class="hero-note d-block mt-3">
                            Your detailed audit will be displayed instantly and sent to your inbox.
                        </small>

                        <div class="row mt-5 justify-content-center g-4">
                            <div class="col-auto">
                                <div class="trust-item"><i class="bi bi-shield-check"></i> GDPR Compliant</div>
                            </div>
                            <div class="col-auto">
                                <div class="trust-item"><i class="bi bi-lightning-charge"></i> 60 Sec Generation</div>
                            </div>
                            <div class="col-auto">
                                <div class="trust-item"><i class="bi bi-star-fill"></i> 4.9/5 Rating</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="pb-5">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="feature-card h-100">
                                <div class="icon-box"><i class="bi bi-bar-chart-line"></i></div>
                                <h4>Search Indexing</h4>
                                <p>We scan over 200 technical checkpoints that impact your search engine rankings.</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="feature-card h-100">
                                <div class="icon-box"><i class="bi bi-speedometer2"></i></div>
                                <h4>Core Web Vitals</h4>
                                <p>Analyze page speed, CLS, LCP and INP to improve user experience.</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="feature-card h-100">
                                <div class="icon-box"><i class="bi bi-stars"></i></div>
                                <h4>AI Suggestions</h4>
                                <p>Receive actionable AI-powered recommendations to grow your rankings.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- WORKFLOW PROGRESS MODAL -->
    <div class="modal fade" id="progressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="progressModalLabel">
                        <i class="bi bi-cpu-fill text-warning me-2"></i> SEO Audit Workflow Progress
                    </h5>
                </div>
                <div class="modal-body py-4">
                    <p class="text-secondary small mb-3">Workflow: <span class="badge bg-warning bg-opacity-20 text-warning font-monospace">seo audit_1</span></p>
                    
                    <div class="progress mb-4" style="height: 12px; background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden;">
                        <div id="auditProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 15%;"></div>
                    </div>

                    <div class="workflow-steps">
                        <div id="step1" class="d-flex align-items-center mb-3 text-warning">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i> 
                            <div><strong class="d-block small">1. Parameters Saved</strong><span class="text-muted small">Website URL & email registered in database</span></div>
                        </div>
                        <div id="step2" class="d-flex align-items-center mb-3 text-secondary">
                            <i class="bi bi-dash-circle me-2 fs-5"></i>
                            <div><strong class="d-block small">2. Initializing n8n Workflow (seo audit_1)</strong><span class="text-muted small">Connecting AI crawler nodes</span></div>
                        </div>
                        <div id="step3" class="d-flex align-items-center mb-3 text-secondary">
                            <i class="bi bi-dash-circle me-2 fs-5"></i>
                            <div><strong class="d-block small">3. Core Web Vitals & Technical Indexing</strong><span class="text-muted small">Scanning speed, CLS, LCP and 200+ checkpoints</span></div>
                        </div>
                        <div id="step4" class="d-flex align-items-center text-secondary">
                            <i class="bi bi-dash-circle me-2 fs-5"></i>
                            <div><strong class="d-block small">4. Audit Scores & AI Recommendations</strong><span class="text-muted small">Finalizing detailed audit output</span></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-between align-items-center">
                    <span id="progressStatusText" class="text-muted small">Processing... Please wait</span>
                    <a id="btnGoToResult" href="seo_output.php" class="btn btn-warning fw-bold d-none">
                        View Audit Report <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
        include("footer_link.php");
    ?>
    <script src="../js/seo_audit.js?v=<?php echo time(); ?>"></script>
</body>

</html>