document.addEventListener("DOMContentLoaded", function () {

    const userId = document.getElementById("user_id")?.value || 0;
    const filterDateInput = document.getElementById("filterDate");
    const exportCsvBtn = document.getElementById("exportCsvBtn");
    
    // Toggle Buttons
    const btnToggleAll = document.getElementById("btnToggleAll");
    const btnToggleFilters = document.getElementById("btnToggleFilters");
    
    // Global State Variables for Pagination
    let myIntentChart = null;
    let currentKeywordData = []; 
    let currentPage = 1;
    const rowsPerPage = 15;

    if (userId == 0) {
        const tbody = document.getElementById("keywordRowsTarget");
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Session expired. Please log in.</td></tr>`;
        }
        return;
    }

    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const dateString = `${yyyy}-${mm}-${dd}`;
    
    if (filterDateInput) {
        filterDateInput.value = dateString;
    }

    function fetchKeywordData(dateToFetch) {
        const tbody = document.getElementById("keywordRowsTarget");
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted">Loading backend database rows... <span class="spinner-border spinner-border-sm" role="status"></span></td></tr>`;
        }
        
        const fetchUrl = `../api/keyword/keyword_research_data_fetch.php?user_id=${userId}&date=${dateToFetch}`;

        fetch(fetchUrl, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok && response.status !== 404) {
                throw new Error(data.error || data.message || "Backend response connection unstable.");
            }
            
            return data.data || []; 
        })
        .then(dataArray => {
            currentKeywordData = Array.isArray(dataArray) ? dataArray : []; 
            currentPage = 1; // Reset to page 1 whenever we fetch new data
            renderTableAndPagination(); // Call combined render function
            updateIntentChart(currentKeywordData);
        })
        .catch(error => {
            console.error("Error piping keyword table:", error);
            currentKeywordData = []; 
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load keyword analysis results: ${escapeHtml(error.message)}</td></tr>`;
            }
        });
    }

    function updateIntentChart(data) {
        const canvas = document.getElementById('intentChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        
        if (!data || !Array.isArray(data) || data.length === 0) {
            if (myIntentChart) {
                myIntentChart.destroy();
                myIntentChart = null;
            }
            return;
        }

        // Process data: Count occurrences of each search intent
        const counts = data.reduce((acc, row) => {
            const rawIntent = (row.search_intent || 'N/A').trim().toLowerCase();
            const intent = rawIntent ? rawIntent.charAt(0).toUpperCase() + rawIntent.slice(1) : 'N/A';
            acc[intent] = (acc[intent] || 0) + 1;
            return acc;
        }, {});

        const labels = Object.keys(counts);
        const values = Object.values(counts);

        // Destroy existing chart to prevent canvas ghosting
        if (myIntentChart) myIntentChart.destroy();

        myIntentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#f97316', '#a855f7', '#3b82f6', '#4caf50', '#64748b'], 
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'right', 
                        labels: { color: '#94a3b8', font: { size: 10 } } 
                    } 
                }
            }
        });
    }

    function setToggleState(state) {
        if (!btnToggleAll || !btnToggleFilters) return;
        if (state === 'all') {
            btnToggleAll.className = "btn btn-sm text-white px-3 py-1 fw-bold";
            btnToggleAll.style.background = "var(--bg-badge)";
            btnToggleAll.style.boxShadow = "0 2px 4px rgba(0,0,0,0.2)";
            
            btnToggleFilters.className = "btn btn-sm text-muted px-3 py-1 fw-medium";
            btnToggleFilters.style.background = "transparent";
            btnToggleFilters.style.boxShadow = "none";
        } else {
            btnToggleFilters.className = "btn btn-sm text-white px-3 py-1 fw-bold";
            btnToggleFilters.style.background = "var(--bg-badge)";
            btnToggleFilters.style.boxShadow = "0 2px 4px rgba(0,0,0,0.2)";
            
            btnToggleAll.className = "btn btn-sm text-muted px-3 py-1 fw-medium";
            btnToggleAll.style.background = "transparent";
            btnToggleAll.style.boxShadow = "none";
        }
    }

    if (btnToggleAll && btnToggleFilters) {
        btnToggleAll.addEventListener('click', () => {
            setToggleState('all');
            fetchKeywordData('all'); 
        });

        btnToggleFilters.addEventListener('click', () => {
            setToggleState('filters');
            fetchKeywordData(filterDateInput ? filterDateInput.value : dateString); 
        });
    }

    if (filterDateInput) {
        filterDateInput.addEventListener('change', function(e) {
            const selectedDate = e.target.value;
            if (selectedDate) {
                setToggleState('filters');
                fetchKeywordData(selectedDate);
            }
        });
    }

    // Initial Load
    setToggleState('all');
    fetchKeywordData('all');

    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function() {
            if (!currentKeywordData || currentKeywordData.length === 0) {
                alert("There is no data to export for this view.");
                return;
            }
            const isAllMode = btnToggleAll && (btnToggleAll.style.background !== "transparent");
            const timeTag = isAllMode ? "All_Data" : (filterDateInput ? filterDateInput.value : "Date");
            const filename = `keyword_research_${timeTag}.csv`;
            downloadCSV(currentKeywordData, filename);
        });
    }

    // =========================================================
    // PAGINATION & TABLE LOGIC
    // =========================================================

    function renderTableAndPagination() {
        populateKeywordTable();
        renderPaginationControls();
    }

    function populateKeywordTable() {
        const tbody = document.getElementById("keywordRowsTarget");
        const countText = document.getElementById("showingCountText");
        const paginationTarget = document.getElementById("paginationTarget");

        if (!tbody) return;
        tbody.innerHTML = ""; 

        if (!currentKeywordData || currentKeywordData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No keywords found for this selection.</td></tr>`;
            if (countText) countText.innerText = "Showing 0-0 of 0 keywords";
            if (paginationTarget) paginationTarget.innerHTML = "";
            return;
        }

        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const paginatedRows = currentKeywordData.slice(startIndex, endIndex);

        paginatedRows.forEach(item => {
            const intentText = item.search_intent || "N/A";
            const priorityVal = parseInt(item.priority) || 0;

            let intentClass = "intent-informational";
            const lowerIntent = intentText.toLowerCase();
            if (lowerIntent === "commercial") intentClass = "intent-commercial";
            if (lowerIntent === "transactional") intentClass = "intent-transactional";

            let priorityClass = priorityVal >= 4 ? "priority-high" : "priority-med";

            const rowHTML = `
                <tr>
                    <td class="ps-4 keyword-text-cell">${escapeHtml(item.keyword)}</td>
                    <td><span class="cluster-pill">${escapeHtml(item.cluster)}</span></td>
                    <td><span class="intent-pill ${intentClass}">${escapeHtml(intentText)}</span></td>
                    <td class="text-center"><span class="priority-circle ${priorityClass}">${priorityVal}</span></td>
                    <td class="pe-4 text-muted small">${escapeHtml(item.get_time)}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", rowHTML);
        });

        if (countText) {
            const displayStart = startIndex + 1;
            const displayEnd = Math.min(endIndex, currentKeywordData.length);
            countText.innerText = `Showing ${displayStart}-${displayEnd} of ${currentKeywordData.length} keywords`;
        }
    }

    function renderPaginationControls() {
        const paginationTarget = document.getElementById("paginationTarget");
        if (!paginationTarget) return;

        const totalPages = Math.ceil(currentKeywordData.length / rowsPerPage);
        
        if (totalPages <= 1) {
            paginationTarget.innerHTML = "";
            return;
        }

        let html = "";

        const prevDisabled = (currentPage === 1) ? "disabled" : "";
        html += `<li class="page-item ${prevDisabled}"><a class="page-link page-action" href="#" data-action="prev"><i class="bi bi-chevron-left"></i></a></li>`;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link page-action" href="#" data-action="1">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted px-1">•••</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = (i === currentPage) ? "active" : "";
            html += `<li class="page-item ${activeClass}"><a class="page-link page-action" href="#" data-action="${i}">${i}</a></li>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link border-0 bg-transparent text-muted px-1">•••</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link page-action" href="#" data-action="${totalPages}">${totalPages}</a></li>`;
        }

        const nextDisabled = (currentPage === totalPages) ? "disabled" : "";
        html += `<li class="page-item ${nextDisabled}"><a class="page-link page-action" href="#" data-action="next"><i class="bi bi-chevron-right"></i></a></li>`;

        paginationTarget.innerHTML = html;

        const actionLinks = paginationTarget.querySelectorAll('.page-action');
        actionLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (this.parentElement.classList.contains('disabled') || this.parentElement.classList.contains('active')) return;

                const action = this.getAttribute('data-action');
                
                if (action === 'prev') {
                    currentPage--;
                } else if (action === 'next') {
                    currentPage++;
                } else {
                    currentPage = parseInt(action);
                }

                renderTableAndPagination();
            });
        });
    }

    function downloadCSV(data, filename) {
        if (!data || data.length === 0) return alert("No data available to export.");

        const now = new Date();
        const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        
        const headers = ["Keyword", "Cluster", "Search Intent", "Priority", "Date Created"];
        
        const csvRows = data.map(r => [
            r.keyword, 
            r.cluster, 
            r.search_intent, 
            r.priority, 
            r.get_time
        ].map(val => `"${String(val || '').replace(/"/g, '""')}"`).join(","));

        const customMessage = `\n"Thank You To Be A Beliver Of Action Ai 😊"`+
                                `\n"NOTE: increase the width of the Date Created to see the timestamp."`;
        const csvString = [headers.join(","), ...csvRows].join("\n") + customMessage;

        const blob = new Blob(['\uFEFF', csvString], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement("a");
        link.href = url;
        link.download = `keyword_research_${dateStr}.csv`;
        link.style.visibility = "hidden";
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        const txt = document.createElement("textarea");
        txt.innerHTML = String(str);
        const decoded = txt.value;
        return decoded
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});