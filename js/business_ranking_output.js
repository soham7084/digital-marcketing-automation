document.addEventListener("DOMContentLoaded", function () {
    const userId = document.getElementById("user_id")?.value;
    if (!userId) return;

    const apiPath = "../api/business_ranking/business_result_fetch.php";

    let allData = [];
    let currentPage = 1;
    let isFilterMode = false;
    const rowsPerPage = 15;

    // UI Elements
    const tableBody = document.getElementById("resultsTableBody");
    const dateFilter = document.getElementById("filterDate");
    const btnAll = document.getElementById("btnViewAll");
    const btnFilters = document.getElementById("btnViewFilters");
    const btnExport = document.getElementById("btnExport");
    const paginationTarget = document.getElementById("paginationTarget");
    const pageInfo = document.getElementById("pageInfo");

    // Toggle Mode Logic
    function setActive(activeBtn, inactiveBtn) {
        activeBtn.classList.add('fw-bold', 'text-white');
        activeBtn.style.background = "var(--bg-badge)";
        inactiveBtn.classList.remove('fw-bold', 'text-white');
        inactiveBtn.classList.add('text-muted');
        inactiveBtn.style.background = "transparent";
    }

    btnFilters.addEventListener("click", function () {
        isFilterMode = true;
        setActive(btnFilters, btnAll);
        if (dateFilter.value) fetchResults(dateFilter.value);
    });

    btnAll.addEventListener("click", function () {
        isFilterMode = false;
        setActive(btnAll, btnFilters);
        fetchResults();
    });

    dateFilter.addEventListener("change", function () {
        if (isFilterMode) fetchResults(this.value);
    });

    // CSV Export Logic
    btnExport.addEventListener("click", function () {
        if (allData.length === 0) return alert("No data available to export.");

        const now = new Date();
        const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        const headers = ["Timestamp", "Keyword", "Cluster", "Intent", "Priority", "Location", "Ranking", "Rating", "Competitors", "Status"];

        const csvRows = allData.map(r => [
            r.timestamp, r.keyword, r.cluster, r.search_intent, r.priority,
            r.number_one_location, r.number_one_ranking, r.number_one_rating,
            r.top_3_competitors, r.status
        ].map(val => `"${String(val || '').replace(/"/g, '""')}"`).join(","));

        const customMessage = `\n"Thank You To Be A Beliver Of Action Ai 😊"`;
        const csvString = [headers.join(","), ...csvRows].join("\n") + customMessage;
        const blob = new Blob(['\uFEFF', csvString], { type: 'text/csv;charset=utf-8;' });

        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `ranking_report_${dateStr}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    let myIntentChart = null; // Global variable to hold chart instance

    function updateChart(data) {
        const canvas = document.getElementById('intentChart');
        if (!canvas) return;
        
        if (!data || !Array.isArray(data) || data.length === 0) {
            if (myIntentChart) {
                myIntentChart.destroy();
                myIntentChart = null;
            }
            return;
        }
        
        const ctx = canvas.getContext('2d');

        // Count intents
        const counts = data.reduce((acc, row) => {
            const rawIntent = (row.search_intent || 'N/A').trim().toLowerCase();
            const intent = rawIntent ? rawIntent.charAt(0).toUpperCase() + rawIntent.slice(1) : 'N/A';
            acc[intent] = (acc[intent] || 0) + 1;
            return acc;
        }, {});

        const labels = Object.keys(counts);
        const values = Object.values(counts);

        // Destroy existing chart if it exists to prevent overlap
        if (myIntentChart) myIntentChart.destroy();

        myIntentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#f97316', '#a855f7', '#3b82f6', '#4caf50', '#64748b'], // Matches your pill colors
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { color: '#94a3b8', fontSize: 10 } } }
            }
        });
    }

    function fetchResults(filterDate = null) {
        let url = `${apiPath}?user_id=${userId}`;
        if (isFilterMode && filterDate) url += `&filter_date=${filterDate}`;

        fetch(url).then(res => res.json()).then(res => {
            if (res.success) {
                allData = res.data || [];
                currentPage = 1;
                displayPage(1);
                updateChart(allData);
            } else {
                allData = [];
                displayPage(1);
            }
        });
    }

    function displayPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const pageData = allData.slice(start, start + rowsPerPage);

        pageInfo.innerText = allData.length > 0
            ? `Showing ${start + 1}-${Math.min(start + rowsPerPage, allData.length)} of ${allData.length} keywords`
            : "Showing 0-0 of 0 keywords";

        // Inside your displayPage function:
        tableBody.innerHTML = pageData.map(row => {
            // 1. Intent Logic (Pill)
            const intent = (row.search_intent || '').toLowerCase();
            let intentClass = "intent-informational";
            if (['commercial', 'transactional', 'navigational'].includes(intent)) {
                intentClass = `intent-${intent}`;
            }

            // 2. Priority Logic (Circle)
            const priority = parseInt(row.priority) || 0;
            const priorityClass = priority >= 4 ? "priority-high" : "priority-med";

            // 3. Status Logic (Label/Pill)
            const status = (row.status || '').toLowerCase();
            const statusClass = status.includes('top') ? 'status-active' : 'status-inactive';

            const loc = row.number_one_location || '';
            const mapUrl = loc;

            return `<tr>
        <td class="ps-3 text-muted small">${row.timestamp || '-'}</td>
        <td class="fw-bold">${row.keyword || '-'}</td>
        <td><span class="cluster-pill">${row.cluster || '-'}</span></td>
        <td><span class="intent-pill ${intentClass}">${row.search_intent || '-'}</span></td>
        <td class="text-center"><span class="priority-circle ${priorityClass}">${row.priority || '-'}</span></td>
        <td style="max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            <a href="${mapUrl}" target="_blank" title="${loc}">${loc || '-'}</a>
        </td>
        <td class="fw-bold">#${row.number_one_ranking || '-'}</td>
        <td class="text-center">${row.number_one_rating || '-'}</td>
        <td>${row.top_3_competitors || '-'}</td>
        <td><span class="status-pill ${statusClass}">${row.status || '-'}</span></td>
    </tr>`;
        }).join('');
        renderPagination();
    }

    function renderPagination() {
        const totalPages = Math.ceil(allData.length / rowsPerPage);
        if (allData.length === 0) { paginationTarget.innerHTML = ""; return; }

        let html = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${currentPage - 1})"><i class="bi bi-chevron-left"></i></a></li>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted px-1">•••</span></li>`;
            }
        }
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${currentPage + 1})"><i class="bi bi-chevron-right"></i></a></li>`;
        paginationTarget.innerHTML = html;
    }

    window.changePage = function (p) {
        event.preventDefault();
        if (p >= 1 && p <= Math.ceil(allData.length / rowsPerPage)) displayPage(p);
    };

    fetchResults();
});