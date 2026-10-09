document.addEventListener("DOMContentLoaded", () => {
    
    // ==================================================
    // AI Hub Filter Actions (Dashboard Specific)
    // ==================================================
    const filterButtons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.ai-hub-item');
    const dropdownLabel = document.getElementById('aiHubDropdown');

    if (filterButtons.length > 0 && cards.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                
                // Reset active states
                filterButtons.forEach(b => b.classList.remove('active'));
                e.currentTarget.classList.add('active');

                const filterValue = e.currentTarget.getAttribute('data-filter');
                
                // Update dropdown text label
                if (dropdownLabel) {
                    dropdownLabel.innerHTML = `<i class="bi bi-sliders me-2"></i>${e.currentTarget.textContent.trim()}`;
                }

                // Filter the cards
                cards.forEach(card => {
                    if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // ==================================================
    // Mobile "X" Close Button (Secondary Fallback)
    // ==================================================
    // Note: theme.js already closes the sidebar when clicking outside,
    // but this preserves functionality if a direct close button is clicked.
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');
    const sidebar = document.getElementById('sidebar');

    if (closeSidebarBtn && sidebar) {
        closeSidebarBtn.addEventListener('click', () => {
            sidebar.classList.remove('show-mobile');
        });
    }

});// Add your JavaScript code here

