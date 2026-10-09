<?php
include("session_control.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("header_link.php"); ?>
    <style>
    /* Prevent the browser's default calendar icon from taking up layout space */
    #filterDate::-webkit-calendar-picker-indicator {
        position: absolute;
        right: 10px;
        cursor: pointer;
        opacity: 0; /* Hide but keep clickable */
        width: 100%;
    }
    
    /* Ensure the input doesn't shrink when text is long */
    #filterDate {
        flex: 1;
        min-width: 120px;
    }
</style>
</head>

<body data-section="ranking">
    <div class="dashboard-wrapper">
        <?php include("sidebar-me.php"); ?>
        <main class="main-content">
            <?php include("navbar-me.php"); ?>

            <div class="container-fluid px-4 py-4">
                <input type="hidden" id="user_id" value="<?php echo htmlspecialchars($id); ?>">

                <div class="report-container p-4 rounded-4 border border-secondary border-opacity-10 shadow-lg">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="report-main-title fw-bold m-0" style="font-size: 22px;">Ranking Results</h2>

                            <div class="btn-group rounded-3 overflow-hidden border border-secondary border-opacity-20"
                                style="padding: 3px; background: var(--bg-main);">
                                <button id="btnViewAll" class="btn btn-sm text-white px-3 py-1 fw-bold active"
                                    style="background: var(--bg-badge); font-size: 13px; border: none; border-radius: 6px;">All</button>
                                <button id="btnViewFilters" class="btn btn-sm text-muted px-3 py-1 fw-medium"
                                    style="font-size: 13px; border: none; background: transparent;">Filters</button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div id="dateFilterWrapper" class="d-flex align-items-center px-3"
                                style="border-radius: 8px; border: 2px solid #8e959b; background-color: var(--bg-input); height: 42px; gap: 8px;">

                                <i class="bi bi-calendar-event-fill"
                                    style="color: var(--text-placeholder); font-size: 16px;"></i>

                                <input type="date" id="filterDate"
                                    class="form-control border-0 fw-bold shadow-none bg-transparent"
                                    style="font-size: 14px; cursor: pointer; color: var(--text-primary) !important; background: transparent !important; height: 100%; border: none !important; width: 140px;">
                            </div>

                            <button id="btnExport"
                                class="btn btn-rerun d-flex align-items-center gap-2 rounded-3 py-2 px-3 fw-bold m-0 shadow-sm"
                                type="button" style="font-size: 13px; height: 42px;">
                                <i class="bi bi-download text-primary"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div
                                class="report-container p-3 rounded-4 border border-secondary border-opacity-10 shadow-sm">
                                <h5 class="text-muted small mb-3">Search Intent Distribution</h5>
                                <canvas id="intentChart" style="max-height: 200px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border border-secondary border-opacity-10 rounded-3 overflow-hidden">
                        <table class="table table-hover align-middle custom-report-table m-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3 ps-4">Timestamp</th>
                                    <th scope="col" class="py-3">Keyword</th>
                                    <th scope="col" class="py-3">Cluster</th>
                                    <th scope="col" class="py-3">Intent</th>
                                    <th scope="col" class="py-3 text-center">Priority</th>
                                    <th scope="col" class="py-3" style="width: 120px;">No.1 Location</th>
                                    <th scope="col" class="py-3">No.1 Ranking</th>
                                    <th scope="col" class="py-3">No.1 Rating</th>
                                    <th scope="col" class="py-3">Competitors</th>
                                    <th scope="col" class="py-3 pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody id="resultsTableBody">
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">Loading backend database
                                        rows...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between mt-4 gap-3">
                        <span id="pageInfo" class="text-muted small" style="font-size: 13px;">Showing 0-0 of 0
                            keywords</span>
                        <nav aria-label="Keyword pagination list navigator">
                            <ul id="paginationTarget" class="pagination pagination-custom m-0 gap-1 align-items-center">
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </main>
    </div>
    <?php include("footer_link.php"); ?>
    <script src="../js/business_ranking_output.js?v=<?php echo time(); ?>"></script>
</body>

</html>