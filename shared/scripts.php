<?php
// Shared JavaScript functionality extracted from app.js
?>
<script>
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
  },
  remove(key) {
    localStorage.removeItem(key);
  },
};

// Theme management
function applyTheme() {
  const root = document.documentElement;
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const theme = storage.get("hr4.theme", "system");
  const isDark = theme === "dark" || (theme === "system" && prefersDark);
  root.classList.toggle("dark", !!isDark);
}

function setTheme(next) {
  storage.set("hr4.theme", next);
  applyTheme();
}

// Initialize theme on load
document.addEventListener('DOMContentLoaded', function() {
  applyTheme();
  
  // Theme toggle functionality
  const themeToggle = document.getElementById('themeToggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const currentTheme = storage.get("hr4.theme", "system");
      const next = currentTheme === "dark" ? "light" : currentTheme === "light" ? "system" : "dark";
      setTheme(next);
    });
  }
  
  // Sidebar collapse functionality
  const btnCollapse = document.getElementById('btnCollapse');
  if (btnCollapse) {
    btnCollapse.addEventListener('click', () => {
      const sidebar = document.getElementById('sidebar');
      const isCollapsed = sidebar.dataset.collapsed === 'true';
      sidebar.dataset.collapsed = !isCollapsed;
      // Reload page to apply layout change
      location.reload();
    });
  }
  
  // Mobile sidebar functionality
  const btnSidebar = document.getElementById('btnSidebar');
  if (btnSidebar) {
    btnSidebar.addEventListener('click', () => {
      // Create mobile sidebar overlay
      const existing = document.querySelector("[data-sheet]");
      if (existing) existing.remove();
      
      const sidebarItems = document.querySelectorAll('#sidebar nav a');
      let sidebarHtml = '<nav class="p-2">';
      sidebarItems.forEach(item => {
        sidebarHtml += item.outerHTML;
      });
      sidebarHtml += '</nav>';
      
      const sheetHtml = `
        <div class="fixed inset-0 z-40 block" data-sheet>
          <div class="absolute inset-0 bg-black/40" data-sheet-overlay></div>
          <div class="absolute left-0 top-0 h-full w-72 bg-[hsl(var(--background))] border-r border-[hsl(var(--border))] shadow-xl" data-sheet-panel>${sidebarHtml}</div>
        </div>
      `;
      
      document.body.insertAdjacentHTML('beforeend', sheetHtml);
      const sheetEl = document.querySelector("[data-sheet]");
      sheetEl?.addEventListener("click", (e) => {
        if (e.target && (e.target.matches("[data-sheet]") || e.target.matches("[data-sheet-overlay]"))) {
          sheetEl.remove();
        }
      });
    });
  }
  
  // Logout functionality
  const btnLogout = document.getElementById('btnLogout');
  if (btnLogout) {
    btnLogout.addEventListener('click', () => {
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
      }
    });
  }
});
</script>
