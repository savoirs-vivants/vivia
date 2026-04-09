function togglePanel(id) {
    const modal = document.getElementById(id + "-modal");
    const panel = document.getElementById(id + "-panel");

    if (modal.classList.contains("hidden")) {
        modal.classList.remove("hidden");
        setTimeout(() => panel.classList.remove("translate-x-full"), 10);
    } else {
        panel.classList.add("translate-x-full");
        setTimeout(() => modal.classList.add("hidden"), 300);
    }
}

document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-panel]");
    if (trigger) togglePanel(trigger.dataset.panel);
});

document.addEventListener("click", (e) => {
    const toggle = e.target.closest(".js-folder-toggle");
    if (!toggle) return;

    const folder = toggle.closest(".js-folder");
    const content = folder.querySelector(".js-folder-content");
    const chevron = folder.querySelector(".js-folder-chevron");
    const isOpen = toggle.getAttribute("aria-expanded") === "true";

    toggle.setAttribute("aria-expanded", String(!isOpen));
    content.classList.toggle("hidden", isOpen);
    chevron.classList.toggle("rotate-90", !isOpen);
});

document.addEventListener("alpine:init", () => {
    Alpine.data("gestionnaireSearch", (initialSelected = []) => ({
        query: "",
        results: [],
        selectedUsers: initialSelected,

        async search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            const response = await fetch(`/users/search?q=${this.query}`);
            const data = await response.json();
            this.results = data.filter(
                (u) => !this.selectedUsers.find((s) => s.id === u.id),
            );
        },

        addUser(user) {
            this.selectedUsers.push(user);
            this.query = "";
            this.results = [];
        },

        removeUser(id) {
            this.selectedUsers = this.selectedUsers.filter((u) => u.id !== id);
        },
    }));

    Alpine.data("tabsManager", (defaultTab = "avenir") => ({
        activeTab: defaultTab,
        switchTab(name) {
            this.activeTab = name;
        },
    }));
});

function toggleSeance(id) {
    const content = document.getElementById("content-seance-" + id);
    const chev = document.getElementById("chev-" + id);
    const isOpen = !content.classList.contains("hidden");
    content.classList.toggle("hidden");
    chev.style.transform = isOpen ? "" : "rotate(180deg)";
}

function toggleRaison(checkbox, raisonId) {
    const raisonInput = document.getElementById(raisonId);
    const label = document.getElementById(
        raisonId.replace("raison-", "label-"),
    );
    if (checkbox.checked) {
        raisonInput.classList.add("hidden");
        raisonInput.value = "";
        label.textContent = "Présent";
        label.className =
            "ml-3 text-xs font-bold text-[#16987C] w-14 text-center";
    } else {
        raisonInput.classList.remove("hidden");
        raisonInput.focus();
        label.textContent = "Absent";
        label.className =
            "ml-3 text-xs font-bold text-rose-500 w-14 text-center";
    }
}

function toggleAbandonForm(adherentId) {
    document
        .getElementById("abandon-form-" + adherentId)
        .classList.toggle("hidden");
}

document.addEventListener("click", (e) => {
    if (e.target.closest("#btn-add-horaire")) {
        const container = document.getElementById("horaires-container");
        const clone = container.querySelector(".horaire-row").cloneNode(true);
        clone
            .querySelectorAll("select, input")
            .forEach((el) => (el.value = ""));
        clone
            .querySelector(".btn-remove-horaire")
            ?.classList.remove("invisible");
        container.appendChild(clone);
        return;
    }
    const removeBtn = e.target.closest(".btn-remove-horaire");
    if (
        removeBtn &&
        document
            .getElementById("horaires-container")
            ?.querySelectorAll(".horaire-row").length > 1
    ) {
        removeBtn.closest(".horaire-row").remove();
    }
});
