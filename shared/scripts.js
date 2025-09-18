// Storage utilities
const storage = {
  get(key, fallback = null) {
    try {
      return JSON.parse(localStorage.getItem(key)) ?? fallback;
    } catch {
      return fallback;
    }
  },
  set(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
    // Also set cookie for server-side access
    document.cookie = `hr4_${key.replace(
      ".",
      "_"
    )}=${value}; path=/; max-age=31536000`;
  },
  remove(key) {
    localStorage.removeItem(key);
    document.cookie = `hr4_${key.replace(
      ".",
      "_"
    )}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
  },
};

// Theme management
function applyTheme() {
  const root = document.documentElement;
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const theme = storage.get("hr4.theme", "system");
  const isDark = theme === "dark" || (theme === "system" && prefersDark);
  root.classList.toggle("dark", !!isDark);

  // Update theme toggle icon
  updateThemeIcon(isDark);
}

// Update theme toggle icon based on current theme
function updateThemeIcon(isDark) {
  const themeToggle = document.getElementById("themeToggle");
  if (!themeToggle) return;

  const sunIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
    </svg>
  `;

  const moonIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-300" viewBox="0 0 24 24" fill="currentColor">
      <path d="M21 12a9 9 0 11-9-9 7 7 0 009 9z"/>
    </svg>
  `;

  // Show sun icon when in light mode, moon icon when in dark mode
  themeToggle.innerHTML = `<span class="sr-only">Theme</span>${
    isDark ? moonIcon : sunIcon
  }`;
}

function setTheme(next) {
  storage.set("hr4.theme", next);
  applyTheme();
}

// Initialize theme on load
document.addEventListener("DOMContentLoaded", function () {
  console.log("Scripts.js loaded successfully");
  console.log("Available elements:", {
    sidebar: document.getElementById("sidebar"),
    themeToggle: document.getElementById("themeToggle"),
    btnCollapse: document.getElementById("btnCollapse"),
    btnLogout: document.getElementById("btnLogout"),
  });
  applyTheme();

  // Initialize sidebar state
  const sidebar = document.getElementById("sidebar");
  if (sidebar) {
    const isCollapsed = storage.get("hr4.sidebar.collapsed", false);
    sidebar.dataset.collapsed = isCollapsed.toString();

    // Apply the collapsed state immediately
    const gridContainer = document.querySelector(".flex-1.grid");
    if (gridContainer) {
      if (isCollapsed) {
        gridContainer.className = "flex-1 grid lg:grid-cols-[72px_1fr]";
      } else {
        gridContainer.className = "flex-1 grid lg:grid-cols-[260px_1fr]";
      }
    }

    // Update sidebar labels visibility
    const labels = sidebar.querySelectorAll("nav a span:last-child");
    labels.forEach((label) => {
      if (isCollapsed) {
        label.classList.add("hidden");
      } else {
        label.classList.remove("hidden");
      }
    });
  }

  // Theme toggle functionality
  const themeToggle = document.getElementById("themeToggle");
  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      const currentTheme = storage.get("hr4.theme", "system");
      const next =
        currentTheme === "dark"
          ? "light"
          : currentTheme === "light"
          ? "system"
          : "dark";
      setTheme(next);
    });
  }

  // Sidebar collapse functionality
  const btnCollapse = document.getElementById("btnCollapse");
  if (btnCollapse) {
    btnCollapse.addEventListener("click", () => {
      console.log("Sidebar collapse button clicked");
      const sidebar = document.getElementById("sidebar");
      if (!sidebar) {
        console.error("Sidebar element not found");
        return;
      }

      const isCollapsed = sidebar.dataset.collapsed === "true";
      const newState = !isCollapsed;
      console.log("Current state:", isCollapsed, "New state:", newState);

      // Update sidebar state
      sidebar.dataset.collapsed = newState.toString();
      storage.set("hr4.sidebar.collapsed", newState);

      // Update grid layout
      const gridContainer = document.querySelector(".flex-1.grid");
      if (gridContainer) {
        if (newState) {
          gridContainer.className = "flex-1 grid lg:grid-cols-[72px_1fr]";
        } else {
          gridContainer.className = "flex-1 grid lg:grid-cols-[260px_1fr]";
        }
        console.log("Grid layout updated");
      } else {
        console.error("Grid container not found");
      }

      // Update sidebar labels visibility
      const labels = sidebar.querySelectorAll("nav a span:last-child");
      labels.forEach((label) => {
        if (newState) {
          label.classList.add("hidden");
        } else {
          label.classList.remove("hidden");
        }
      });
      console.log("Updated", labels.length, "sidebar labels");
    });
  } else {
    console.error("Sidebar collapse button not found");
  }

  // Mobile sidebar functionality
  const btnSidebar = document.getElementById("btnSidebar");
  if (btnSidebar) {
    btnSidebar.addEventListener("click", () => {
      // Create mobile sidebar overlay
      const existing = document.querySelector("[data-sheet]");
      if (existing) existing.remove();

      const sidebarItems = document.querySelectorAll("#sidebar nav a");
      let sidebarHtml = '<nav class="p-2">';
      sidebarItems.forEach((item) => {
        sidebarHtml += item.outerHTML;
      });
      sidebarHtml += "</nav>";

      const sheetHtml = `
        <div class="fixed inset-0 z-40 block" data-sheet>
          <div class="absolute inset-0 bg-black/40" data-sheet-overlay></div>
          <div class="absolute left-0 top-0 h-full w-72 bg-[hsl(var(--background))] border-r border-[hsl(var(--border))] shadow-xl" data-sheet-panel>${sidebarHtml}</div>
        </div>
      `;

      document.body.insertAdjacentHTML("beforeend", sheetHtml);
      const sheetEl = document.querySelector("[data-sheet]");

      // Add click handlers to close the mobile sidebar
      const overlay = sheetEl.querySelector("[data-sheet-overlay]");
      const panel = sheetEl.querySelector("[data-sheet-panel]");

      const closeSheet = () => sheetEl.remove();

      overlay?.addEventListener("click", closeSheet);
      sheetEl?.addEventListener("click", (e) => {
        if (e.target === sheetEl) closeSheet();
      });

      // Close on escape key
      const handleEscape = (e) => {
        if (e.key === "Escape") {
          closeSheet();
          document.removeEventListener("keydown", handleEscape);
        }
      };
      document.addEventListener("keydown", handleEscape);
    });
  }

  // Logout functionality
  const btnLogout = document.getElementById("btnLogout");
  if (btnLogout) {
    btnLogout.addEventListener("click", () => {
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
      }
    });
  }

  // Initialize modern UI components
  window.modernUI = new ModernUI();
});

// __________________________________________________________________________________________________________

// Modern UI Components
class ModernUI {
  constructor() {
    this.init();
  }

  init() {
    this.setupTooltips();
    this.setupModals();
    this.setupDropdowns();
    this.setupTabs();
    this.setupCharts();
    this.setupDataTables();
    this.setupFormValidation();
    this.setupAnimations();
    this.setupNotifications();
  }

  // Tooltip functionality
  setupTooltips() {
    const tooltipElements = document.querySelectorAll("[data-tooltip]");
    tooltipElements.forEach((element) => {
      element.addEventListener("mouseenter", this.showTooltip);
      element.addEventListener("mouseleave", this.hideTooltip);
    });
  }

  showTooltip(e) {
    const text = e.target.getAttribute("data-tooltip");
    const tooltip = document.createElement("div");
    tooltip.className =
      "absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-100 dark:text-gray-900 rounded shadow-lg pointer-events-none";
    tooltip.textContent = text;
    tooltip.id = "tooltip";
    document.body.appendChild(tooltip);

    const rect = e.target.getBoundingClientRect();
    tooltip.style.left =
      rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px";
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + "px";
  }

  hideTooltip() {
    const tooltip = document.getElementById("tooltip");
    if (tooltip) {
      tooltip.remove();
    }
  }

  // Modal functionality
  setupModals() {
    const modalTriggers = document.querySelectorAll("[data-modal-target]");
    const modalCloses = document.querySelectorAll("[data-modal-close]");

    modalTriggers.forEach((trigger) => {
      trigger.addEventListener("click", (e) => {
        e.preventDefault();
        const target = document.getElementById(
          trigger.getAttribute("data-modal-target")
        );
        if (target) {
          this.openModal(target);
        }
      });
    });

    modalCloses.forEach((close) => {
      close.addEventListener("click", (e) => {
        e.preventDefault();
        const modal = close.closest(".modal");
        if (modal) {
          this.closeModal(modal);
        }
      });
    });

    // Close modal on backdrop click
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal-backdrop")) {
        this.closeModal(e.target);
      }
    });
  }

  openModal(modal) {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.classList.add("overflow-hidden");
    setTimeout(() => {
      modal.classList.add("opacity-100");
    }, 10);
  }

  closeModal(modal) {
    modal.classList.add("opacity-0");
    setTimeout(() => {
      modal.classList.add("hidden");
      modal.classList.remove("flex", "opacity-100", "opacity-0");
      document.body.classList.remove("overflow-hidden");
    }, 300);
  }

  // Dropdown functionality
  setupDropdowns() {
    const dropdowns = document.querySelectorAll(".dropdown");
    dropdowns.forEach((dropdown) => {
      const toggle = dropdown.querySelector(".dropdown-toggle");
      const menu = dropdown.querySelector(".dropdown-menu");

      if (toggle && menu) {
        toggle.addEventListener("click", (e) => {
          e.stopPropagation();
          this.toggleDropdown(dropdown);
        });
      }
    });

    // Close dropdowns when clicking outside
    document.addEventListener("click", () => {
      dropdowns.forEach((dropdown) => {
        dropdown.classList.remove("open");
      });
    });
  }

  toggleDropdown(dropdown) {
    const isOpen = dropdown.classList.contains("open");
    document
      .querySelectorAll(".dropdown.open")
      .forEach((d) => d.classList.remove("open"));
    if (!isOpen) {
      dropdown.classList.add("open");
    }
  }

  // Tab functionality
  setupTabs() {
    const tabContainers = document.querySelectorAll(".tabs");
    tabContainers.forEach((container) => {
      const tabs = container.querySelectorAll(".tab");
      const panels = container.querySelectorAll(".tab-panel");

      tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
          const target = tab.getAttribute("data-tab");

          // Update tab states
          tabs.forEach((t) => t.classList.remove("active"));
          tab.classList.add("active");

          // Update panel states
          panels.forEach((p) => p.classList.add("hidden"));
          const targetPanel = container.querySelector(
            `[data-panel="${target}"]`
          );
          if (targetPanel) {
            targetPanel.classList.remove("hidden");
          }
        });
      });
    });
  }

  // Chart functionality (using Chart.js if available)
  setupCharts() {
    if (typeof Chart !== "undefined") {
      this.initializeCharts();
    }
  }

  initializeCharts() {
    const chartElements = document.querySelectorAll("[data-chart]");
    chartElements.forEach((element) => {
      const type = element.getAttribute("data-chart-type") || "line";
      const data = JSON.parse(element.getAttribute("data-chart-data") || "{}");
      const options = JSON.parse(
        element.getAttribute("data-chart-options") || "{}"
      );

      new Chart(element, {
        type: type,
        data: data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          ...options,
        },
      });
    });
  }

  // Data table functionality
  setupDataTables() {
    const tables = document.querySelectorAll(".data-table");
    tables.forEach((table) => {
      this.enhanceDataTable(table);
    });
  }

  enhanceDataTable(table) {
    // Add sorting functionality
    const headers = table.querySelectorAll("th[data-sort]");
    headers.forEach((header) => {
      header.style.cursor = "pointer";
      header.classList.add("hover:bg-gray-100", "dark:hover:bg-gray-800");
      header.addEventListener("click", () => {
        this.sortTable(table, header);
      });
    });

    // Add search functionality
    const searchInput = table.parentElement.querySelector(".table-search");
    if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        this.filterTable(table, e.target.value);
      });
    }
  }

  sortTable(table, header) {
    const column = header.getAttribute("data-sort");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const isAscending = header.classList.contains("sort-asc");

    // Remove sort classes from all headers
    table.querySelectorAll("th").forEach((th) => {
      th.classList.remove("sort-asc", "sort-desc");
    });

    // Add appropriate sort class
    header.classList.add(isAscending ? "sort-desc" : "sort-asc");

    // Sort rows
    rows.sort((a, b) => {
      const aVal =
        a.querySelector(`[data-sort-value="${column}"]`)?.textContent || "";
      const bVal =
        b.querySelector(`[data-sort-value="${column}"]`)?.textContent || "";

      if (isAscending) {
        return bVal.localeCompare(aVal);
      } else {
        return aVal.localeCompare(bVal);
      }
    });

    // Reorder rows in DOM
    rows.forEach((row) => tbody.appendChild(row));
  }

  filterTable(table, searchTerm) {
    const tbody = table.querySelector("tbody");
    const rows = tbody.querySelectorAll("tr");

    rows.forEach((row) => {
      const text = row.textContent.toLowerCase();
      const matches = text.includes(searchTerm.toLowerCase());
      row.style.display = matches ? "" : "none";
    });
  }

  // Form validation
  setupFormValidation() {
    const forms = document.querySelectorAll("form[data-validate]");
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (!this.validateForm(form)) {
          e.preventDefault();
        }
      });
    });
  }

  validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll(
      "input[required], select[required], textarea[required]"
    );

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        this.showFieldError(input, "This field is required");
        isValid = false;
      } else {
        this.clearFieldError(input);
      }
    });

    return isValid;
  }

  showFieldError(input, message) {
    this.clearFieldError(input);
    input.classList.add("border-red-500");
    const error = document.createElement("div");
    error.className = "text-red-500 text-xs mt-1";
    error.textContent = message;
    input.parentElement.appendChild(error);
  }

  clearFieldError(input) {
    input.classList.remove("border-red-500");
    const error = input.parentElement.querySelector(".text-red-500");
    if (error) {
      error.remove();
    }
  }

  // Animations
  setupAnimations() {
    // Intersection Observer for fade-in animations
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate-fade-in");
        }
      });
    });

    document.querySelectorAll(".animate-on-scroll").forEach((el) => {
      observer.observe(el);
    });
  }

  // Notifications
  setupNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById("notification-container")) {
      const container = document.createElement("div");
      container.id = "notification-container";
      container.className = "fixed top-4 right-4 z-50 space-y-2";
      document.body.appendChild(container);
    }
  }

  // Utility methods
  showNotification(message, type = "info") {
    const container = document.getElementById("notification-container");
    if (!container) return;

    const notification = document.createElement("div");
    notification.className = `px-4 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ${
      type === "success"
        ? "bg-green-500"
        : type === "error"
        ? "bg-red-500"
        : type === "warning"
        ? "bg-yellow-500"
        : "bg-blue-500"
    }`;
    notification.textContent = message;

    container.appendChild(notification);

    // Animate in
    setTimeout(() => {
      notification.classList.remove("translate-x-full");
    }, 10);

    // Auto remove
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  showLoading(element) {
    element.classList.add("opacity-50", "pointer-events-none");
    const spinner = document.createElement("div");
    spinner.className =
      "absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-900/50";
    spinner.innerHTML =
      '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>';
    element.style.position = "relative";
    element.appendChild(spinner);
  }

  hideLoading(element) {
    element.classList.remove("opacity-50", "pointer-events-none");
    const spinner = element.querySelector(".animate-spin");
    if (spinner) {
      spinner.remove();
    }
  }

  // AJAX helper
  async request(url, options = {}) {
    const defaultOptions = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    };

    const response = await fetch(url, { ...defaultOptions, ...options });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  }
}
