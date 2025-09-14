<?php
// Shared JavaScript functionality extracted from app.js

// Helper function to get sidebar state from localStorage (client-side)
function getSidebarState() {
    return isset($_COOKIE['hr4_sidebar_collapsed']) ? 
           filter_var($_COOKIE['hr4_sidebar_collapsed'], FILTER_VALIDATE_BOOLEAN) : 
           false;
}
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
    // Also set cookie for server-side access
    document.cookie = `hr4_${key.replace('.', '_')}=${value}; path=/; max-age=31536000`;
  },
  remove(key) {
    localStorage.removeItem(key);
    document.cookie = `hr4_${key.replace('.', '_')}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
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
  
  // Initialize sidebar state
  const sidebar = document.getElementById('sidebar');
  if (sidebar) {
    const isCollapsed = storage.get("hr4.sidebar.collapsed", false);
    sidebar.dataset.collapsed = isCollapsed;
    
    // Apply the collapsed state immediately
    const gridContainer = document.querySelector('.flex-1.grid');
    if (gridContainer) {
      if (isCollapsed) {
        gridContainer.className = 'flex-1 grid lg:grid-cols-[72px_1fr]';
      } else {
        gridContainer.className = 'flex-1 grid lg:grid-cols-[260px_1fr]';
      }
    }
    
    // Update sidebar labels visibility
    const labels = sidebar.querySelectorAll('nav a span:last-child');
    labels.forEach(label => {
      if (isCollapsed) {
        label.classList.add('hidden');
      } else {
        label.classList.remove('hidden');
      }
    });
  }
  
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
      console.log('Sidebar collapse button clicked');
      const sidebar = document.getElementById('sidebar');
      if (!sidebar) {
        console.error('Sidebar element not found');
        return;
      }
      
      const isCollapsed = sidebar.dataset.collapsed === 'true';
      const newState = !isCollapsed;
      console.log('Current state:', isCollapsed, 'New state:', newState);
      
      // Update sidebar state
      sidebar.dataset.collapsed = newState;
      storage.set("hr4.sidebar.collapsed", newState);
      
      // Update grid layout
      const gridContainer = document.querySelector('.flex-1.grid');
      if (gridContainer) {
        if (newState) {
          gridContainer.className = 'flex-1 grid lg:grid-cols-[72px_1fr]';
        } else {
          gridContainer.className = 'flex-1 grid lg:grid-cols-[260px_1fr]';
        }
        console.log('Grid layout updated');
      } else {
        console.error('Grid container not found');
      }
      
      // Update sidebar labels visibility
      const labels = sidebar.querySelectorAll('nav a span:last-child');
      labels.forEach(label => {
        if (newState) {
          label.classList.add('hidden');
        } else {
          label.classList.remove('hidden');
        }
      });
      console.log('Updated', labels.length, 'sidebar labels');
    });
  } else {
    console.error('Sidebar collapse button not found');
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
        if (e.key === 'Escape') {
          closeSheet();
          document.removeEventListener('keydown', handleEscape);
        }
      };
      document.addEventListener('keydown', handleEscape);
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
