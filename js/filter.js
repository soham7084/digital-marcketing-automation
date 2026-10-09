const filterButtons = document.querySelectorAll(".filter-btn");
const cards = document.querySelectorAll(".ai-hub-item");
const dropdown = document.getElementById("aiHubDropdown");

filterButtons.forEach(button => {

    button.addEventListener("click", e => {

        filterButtons.forEach(btn =>
            btn.classList.remove("active")
        );

        e.currentTarget.classList.add("active");

        const filter =
            e.currentTarget.dataset.filter;

        if (dropdown) {

            dropdown.innerHTML =
                `<i class="bi bi-sliders me-2"></i>${e.currentTarget.textContent.trim()}`;

        }

        cards.forEach(card => {

            card.style.display =
                filter === "all" ||
                card.dataset.category === filter
                    ? "block"
                    : "none";

        });

    });

});