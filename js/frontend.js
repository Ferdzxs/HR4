// Frontend JavaScript for HR4 SPA - Works with PHP backend
// This file handles client-side interactions and API calls

(function () {
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
  const theme = storage.get("hr4.theme", "system");

  function applyTheme() {
    const root = document.documentElement;
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)"
    ).matches;
    const isDark = theme === "dark" || (theme === "system" && prefersDark);
    root.classList.toggle("dark", !!isDark);
  }

  function setTheme(next) {
    storage.set("hr4.theme", next);
    applyTheme();
  }

  // Initialize theme
  applyTheme();

  // API functions
  async function apiCall(endpoint, options = {}) {
    try {
      const response = await fetch(`api/${endpoint}`, {
        headers: {
          "Content-Type": "application/json",
          ...options.headers,
        },
        ...options,
      });
      return await response.json();
    } catch (error) {
      console.error(`API call to ${endpoint} failed:`, error);
      return { ok: false, message: "Network error. Please try again." };
    }
  }

  // Login function
  async function login(username, password, role, rememberMe = false) {
    if (!username || !password || !role) {
      return { ok: false, message: "All fields are required" };
    }

    if (username.length < 3) {
      return { ok: false, message: "Username must be at least 3 characters" };
    }

    if (password.length < 6) {
      return { ok: false, message: "Password must be at least 6 characters" };
    }

    const result = await apiCall("login.php", {
      method: "POST",
      body: JSON.stringify({
        username,
        password,
        role,
        rememberMe,
      }),
    });

    if (result.ok) {
      // Store login info
      storage.set("hr4.user", result.user);
      storage.set("hr4.lastLogin", Date.now());

      // Store login history
      const loginHistory = storage.get("hr4.loginHistory", []);
      loginHistory.unshift({
        username,
        role,
        timestamp: Date.now(),
        ip: "127.0.0.1",
      });
      storage.set("hr4.loginHistory", loginHistory.slice(0, 10));
    }

    return result;
  }

  // Logout function
  async function logout() {
    try {
      await apiCall("logout.php", { method: "POST" });
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      storage.remove("hr4.user");
      window.location.href = "?page=login";
    }
  }

  // Utility functions
  function nowMs() {
    return Date.now();
  }

  // Event listeners
  document.addEventListener("DOMContentLoaded", function () {
    // Theme toggle
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

    // Logout button
    const logoutBtn = document.getElementById("btnLogout");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", logout);
    }

    // Sidebar collapse
    const collapseBtn = document.getElementById("btnCollapse");
    if (collapseBtn) {
      collapseBtn.addEventListener("click", () => {
        // Toggle sidebar state via PHP session
        fetch("api/sidebar.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "toggle" }),
        }).then(() => {
          location.reload();
        });
      });
    }

    // Mobile sidebar
    const sidebarBtn = document.getElementById("btnSidebar");
    if (sidebarBtn) {
      sidebarBtn.addEventListener("click", () => {
        const existing = document.querySelector("[data-sheet]");
        if (existing) existing.remove();

        // Get sidebar items from PHP
        const sidebar = document.querySelector("#sidebar nav");
        if (sidebar) {
          const sheetHtml = `
                        <div class="fixed inset-0 z-50" data-sheet>
                            <div class="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm" data-sheet-overlay></div>
                            <div class="fixed inset-y-0 left-0 z-50 h-full w-3/4 border-r bg-background p-6 sm:max-w-sm">
                                ${sidebar.innerHTML}
                            </div>
                        </div>
                    `;
          document.body.insertAdjacentHTML("beforeend", sheetHtml);

          const sheetEl = document.querySelector("[data-sheet]");
          sheetEl?.addEventListener("click", (e) => {
            if (
              e.target &&
              (e.target.matches("[data-sheet]") ||
                e.target.matches("[data-sheet-overlay]"))
            ) {
              sheetEl.remove();
            }
          });
        }
      });
    }

    // Login form handling
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
      const usernameInput = document.getElementById("username");
      const passwordInput = document.getElementById("password");
      const roleSelect = document.getElementById("role");
      const rememberMeCheckbox = document.getElementById("rememberMe");
      const loginButton = document.getElementById("btnLogin");
      const loginMsg = document.getElementById("loginMsg");
      const togglePasswordBtn = document.getElementById("togglePassword");

      // Password visibility toggle
      if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener("click", () => {
          const type = passwordInput.type === "password" ? "text" : "password";
          passwordInput.type = type;
          const icon = togglePasswordBtn.querySelector("svg");
          if (type === "text") {
            icon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                        `;
          } else {
            icon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        `;
          }
        });
      }

      // Auto-fill demo credentials based on role
      if (roleSelect) {
        const demoCredentials = {
          "HR Manager": { username: "hr.manager", password: "manager123" },
          "Compensation Manager": {
            username: "comp.manager",
            password: "comp123",
          },
          "Benefits Coordinator": {
            username: "benefits.coord",
            password: "benefits123",
          },
          "Payroll Administrator": {
            username: "payroll.admin",
            password: "payroll123",
          },
          "Department Head": { username: "dept.head", password: "dept123" },
          "Hospital Employee": { username: "employee", password: "emp123" },
          "Hospital Management": { username: "executive", password: "exec123" },
        };

        roleSelect.addEventListener("change", () => {
          const role = roleSelect.value;
          if (demoCredentials[role]) {
            usernameInput.value = demoCredentials[role].username;
            passwordInput.value = demoCredentials[role].password;
          }
        });
      }

      // Form submission
      loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Clear previous messages
        if (loginMsg) {
          loginMsg.classList.add("hidden");
          loginMsg.textContent = "";
        }

        // Show loading state
        if (loginButton) {
          loginButton.disabled = true;
          loginButton.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    `;
        }

        // Simulate network delay for better UX
        await new Promise((resolve) => setTimeout(resolve, 500));

        const res = await login(
          usernameInput.value.trim(),
          passwordInput.value,
          roleSelect.value,
          rememberMeCheckbox.checked
        );

        if (!res.ok) {
          if (loginMsg) {
            loginMsg.textContent = res.message;
            loginMsg.classList.remove("hidden");
          }
          if (loginButton) {
            loginButton.disabled = false;
            loginButton.innerHTML = "Sign in";
          }
          return;
        }

        // Success animation
        if (loginButton) {
          loginButton.innerHTML = `
                        <svg class="h-4 w-4 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Success!
                    `;
        }

        // Redirect after brief success display
        setTimeout(() => {
          window.location.href = "?page=dashboard";
        }, 500);
      });

      // Real-time validation
      [usernameInput, passwordInput].forEach((input) => {
        if (input) {
          input.addEventListener("input", () => {
            if (loginMsg && loginMsg.textContent) {
              loginMsg.classList.add("hidden");
            }
          });
        }
      });
    }
  });

  // Global functions for onclick handlers
  window.logout = logout;
  window.toggleSidebar = function () {
    fetch("api/sidebar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "toggle" }),
    }).then(() => {
      location.reload();
    });
  };

  // Placeholder functions for employee actions
  window.viewEmployee = function (id) {
    console.log("View employee:", id);
    // TODO: Implement employee view modal
  };

  window.editEmployee = function (id) {
    console.log("Edit employee:", id);
    // TODO: Implement employee edit modal
  };

  window.viewPayrollPeriod = function (id) {
    console.log("View payroll period:", id);
    // TODO: Implement payroll period view
  };

  window.editPayrollPeriod = function (id) {
    console.log("Edit payroll period:", id);
    // TODO: Implement payroll period edit
  };

  window.viewBenefit = function (id) {
    console.log("View benefit:", id);
    // TODO: Implement benefit view
  };

  window.editBenefit = function (id) {
    console.log("Edit benefit:", id);
    // TODO: Implement benefit edit
  };
})();
