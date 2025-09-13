// Simple SPA shell with ChatGPT-like UI, mock login, routing, and dark mode

(function () {
  const { ROLES, SIDEBAR_ITEMS, SESSION_TIMEOUT_HOURS } = window.HR4_RBAC;

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

  const state = {
    user: storage.get("hr4.user", null),
    theme: storage.get("hr4.theme", "system"),
    sidebarCollapsed: storage.get("hr4.sidebarCollapsed", false),
  };

  function applyTheme() {
    const root = document.documentElement;
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)"
    ).matches;
    const isDark =
      state.theme === "dark" || (state.theme === "system" && prefersDark);
    root.classList.toggle("dark", !!isDark);
  }

  function setTheme(next) {
    state.theme = next;
    storage.set("hr4.theme", next);
    applyTheme();
  }

  function nowMs() {
    return Date.now();
  }

  async function login(username, password, role, rememberMe = false) {
    // Enhanced validation
    if (!username || !password || !role) {
      return { ok: false, message: "All fields are required" };
    }

    if (username.length < 3) {
      return { ok: false, message: "Username must be at least 3 characters" };
    }

    if (password.length < 6) {
      return { ok: false, message: "Password must be at least 6 characters" };
    }

    try {
      const response = await fetch("api/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username,
          password,
          role,
          rememberMe,
        }),
      });

      const result = await response.json();

      if (result.ok) {
        // Update state with user info from server
        state.user = {
          id: result.user.id,
          username: result.user.username,
          role: result.user.role,
          employee_id: result.user.employee_id,
          first_name: result.user.first_name,
          last_name: result.user.last_name,
          employee_number: result.user.employee_number,
          startedAt: nowMs(),
          expiresAt: nowMs() + 8 * 3600 * 1000, // 8 hours default
          rememberMe,
          lastLogin: nowMs(),
        };

        storage.set("hr4.user", state.user);

        // Store login history for security
        const loginHistory = storage.get("hr4.loginHistory", []);
        loginHistory.unshift({
          username,
          role,
          timestamp: nowMs(),
          ip: "127.0.0.1",
        });
        // Keep only last 10 logins
        storage.set("hr4.loginHistory", loginHistory.slice(0, 10));
      }

      return result;
    } catch (error) {
      console.error("Login error:", error);
      return { ok: false, message: "Network error. Please try again." };
    }
  }

  async function logout() {
    try {
      await fetch("api/logout.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      });
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      state.user = null;
      storage.remove("hr4.user");
      location.hash = "#/login";
    }
  }

  function isSessionValid() {
    if (!state.user) return false;
    return nowMs() < state.user.expiresAt;
  }

  function guard(routeId) {
    if (!isSessionValid()) return false;
    const items = SIDEBAR_ITEMS[state.user.role] || [];
    return items.some((i) => i.id === routeId);
  }

  // UI
  const app = document.getElementById("app");

  function iconGlyph(letter) {
    return `<span class="inline-flex items-center justify-center w-5 h-5 rounded bg-slate-200/60 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 text-[11px] font-semibold">${letter.toUpperCase()}</span>`;
  }

  function renderShell(contentHtml = "", activeId = "", options = {}) {
    const { hideChrome = false } = options;
    const role = state.user?.role;
    const sidebar = role ? SIDEBAR_ITEMS[role] : [];
    if (hideChrome) {
      app.innerHTML = `
        <div class="h-full flex items-center justify-center p-6 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950">
          <div class="w-full max-w-md">${contentHtml}</div>
        </div>
      `;
      return;
    }
    const gridCols = state.sidebarCollapsed
      ? "lg:grid-cols-[72px_1fr]"
      : "lg:grid-cols-[260px_1fr]";
    app.innerHTML = `
      <div class="h-full flex flex-col">
        <header class="sticky top-0 z-20 flex items-center gap-2 px-4 py-2 border-b border-[hsl(var(--border))] bg-[hsl(var(--background))]/70 backdrop-blur">
          ${ui.button(
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
            {
              variant: "ghost",
              size: "sm",
              id: "btnSidebar",
              extra: "lg:hidden",
            }
          )}
          ${ui.button(
            state.sidebarCollapsed
              ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15 19l-7-7 7-7"/></svg>'
              : '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7"/></svg>',
            {
              variant: "ghost",
              size: "sm",
              id: "btnCollapse",
              extra: "hidden lg:inline-flex",
            }
          )}
          <div class="flex-1 flex items-center gap-2">
            <div class="font-semibold">HR4</div>
            <div class="text-xs text-slate-500">Compensation & HR Intelligence</div>
          </div>
          <div class="flex items-center gap-2">
            <div class="relative">${ui.button(
              '<span class="sr-only">Theme</span><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12a9 9 0 11-9-9 7 7 0 009 9z"/></svg><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/></svg>',
              {
                variant: "ghost",
                size: "sm",
                id: "themeToggle",
                extra:
                  "text-slate-600 hover:text-slate-900 dark:text-slate-200",
              }
            )}</div>
            ${
              state.user
                ? `<div class="text-sm">${state.user.username} · <span class="text-slate-500">${state.user.role}</span></div>`
                : ""
            }
            ${
              state.user
                ? ui.button("Logout", {
                    variant: "outline",
                    size: "sm",
                    id: "btnLogout",
                  })
                : ""
            }
          </div>
        </header>
        <div class="flex-1 grid ${gridCols}">
          <aside id="sidebar" data-collapsed="${String(
            !!state.sidebarCollapsed
          )}" class="hidden lg:block border-r border-[hsl(var(--border))] overflow-y-auto">
            <nav class="p-2">
              ${(sidebar || [])
                .map(
                  (item) => `
                <a href="#/${item.id}" data-id="${
                    item.id
                  }" class="group flex items-center gap-2 px-2 py-2 rounded-md text-sm ${
                    activeId === item.id ? "bg-[hsl(var(--accent))]" : ""
                  } hover:bg-[hsl(var(--accent))]">
                  ${iconGlyph(item.icon)}
                  <span class="${state.sidebarCollapsed ? "hidden" : ""}">${
                    item.label
                  }</span>
                </a>
              `
                )
                .join("")}
            </nav>
          </aside>
          <main class="overflow-y-auto">
            ${contentHtml}
          </main>
        </div>
      </div>
    `;

    document.getElementById("themeToggle")?.addEventListener("click", () => {
      const next =
        state.theme === "dark"
          ? "light"
          : state.theme === "light"
          ? "system"
          : "dark";
      setTheme(next);
    });
    document.getElementById("btnLogout")?.addEventListener("click", logout);
    document.getElementById("btnCollapse")?.addEventListener("click", () => {
      state.sidebarCollapsed = !state.sidebarCollapsed;
      storage.set("hr4.sidebarCollapsed", state.sidebarCollapsed);
      // re-render current route to apply layout change
      handleRoute();
    });
    document.getElementById("btnSidebar")?.addEventListener("click", () => {
      const existing = document.querySelector("[data-sheet]");
      if (existing) existing.remove();
      const sheetHtml = ui.sheet({
        open: true,
        content: `
          <nav class="p-2">
            ${(sidebar || [])
              .map(
                (item) => `
              <a href="#/${item.id}" data-id="${
                  item.id
                }" class="group flex items-center gap-2 px-2 py-2 rounded-md text-sm ${
                  activeId === item.id ? "bg-[hsl(var(--accent))]" : ""
                } hover:bg-[hsl(var(--accent))]">
                ${iconGlyph(item.icon)}
                <span>${item.label}</span>
              </a>
            `
              )
              .join("")}
          </nav>
        `,
      });
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
    });
  }

  // Views
  function renderLogin() {
    const roleOptions = Object.values(ROLES)
      .map((r) => `<option value="${r}">${r}</option>`)
      .join("");

    // Get last login info for better UX
    const lastLogin = storage.get("hr4.lastLogin", null);
    const loginHistory = storage.get("hr4.loginHistory", []);

    const html = `
      <div class="space-y-6 animate-fade-in">
        <div class="text-center">
          <div class="inline-flex items-center gap-3 text-brand-600 dark:text-brand-400 mb-2">
            <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white font-bold text-lg shadow-lg">H4</span>
            <div class="text-2xl font-bold">HR4</div>
          </div>
          <div class="text-sm text-slate-500">Compensation & HR Intelligence</div>
          ${
            lastLogin
              ? `<div class="text-xs text-slate-400 mt-1">Last login: ${new Date(
                  lastLogin
                ).toLocaleString()}</div>`
              : ""
          }
        </div>
        
        ${ui.card({
          title: "Welcome back",
          content: `
            <form id="loginForm" class="space-y-5">
              <div class="space-y-2">
                <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Username
                </label>
                <div class="relative">
                  ${ui.input({
                    id: "username",
                    placeholder: "Enter your username",
                    extra: "pl-10",
                  })}
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Password
                </label>
                <div class="relative">
                  ${ui.input({
                    id: "password",
                    type: "password",
                    placeholder: "Enter your password",
                    extra: "pl-10 pr-10",
                  })}
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                  </div>
                  <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="h-4 w-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                  Role
                </label>
                ${ui.select({
                  id: "role",
                  options: [
                    { value: "", label: "Select your role" },
                    ...Object.values(ROLES).map((r) => ({
                      value: r,
                      label: r,
                    })),
                  ],
                })}
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <input id="rememberMe" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded">
                  <label for="rememberMe" class="ml-2 block text-sm text-slate-700 dark:text-slate-300">
                    Remember me
                  </label>
                </div>
                <button type="button" id="forgotPasswordBtn" class="text-sm text-brand-600 hover:text-brand-500 dark:text-brand-400">
                  Forgot password?
                </button>
              </div>
              
              <div class="space-y-3">
              ${ui.button("Sign in", {
                variant: "default",
                size: "lg",
                id: "btnLogin",
                extra: "w-full relative",
              })}
                <div id="loginMsg" class="text-sm text-red-600 dark:text-red-400 text-center hidden"></div>
              </div>
            </form>
          `,
          footer: `
            <div class="text-center space-y-2">
              <div class="text-xs text-slate-500">Demo credentials available for each role</div>
              ${
                loginHistory.length > 0
                  ? `
                <details class="text-xs">
                  <summary class="cursor-pointer text-slate-400 hover:text-slate-600">Recent logins</summary>
                  <div class="mt-2 space-y-1">
                    ${loginHistory
                      .slice(0, 3)
                      .map(
                        (login) => `
                      <div class="text-slate-400">${login.username} (${
                          login.role
                        }) - ${new Date(
                          login.timestamp
                        ).toLocaleDateString()}</div>
                    `
                      )
                      .join("")}
                  </div>
                </details>
              `
                  : ""
              }
            </div>
          `,
        })}
      </div>
    `;
    renderShell(html, "", { hideChrome: true });

    // Enhanced form handling
    const form = document.getElementById("loginForm");
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    const roleSelect = document.getElementById("role");
    const rememberMeCheckbox = document.getElementById("rememberMe");
    const loginButton = document.getElementById("btnLogin");
    const loginMsg = document.getElementById("loginMsg");
    const togglePasswordBtn = document.getElementById("togglePassword");

    // Password visibility toggle
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

    // Auto-fill demo credentials based on role
    roleSelect.addEventListener("change", () => {
      const role = roleSelect.value;
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

      if (demoCredentials[role]) {
        usernameInput.value = demoCredentials[role].username;
        passwordInput.value = demoCredentials[role].password;
      }
    });

    // Form submission with loading state
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      // Clear previous messages
      loginMsg.classList.add("hidden");
      loginMsg.textContent = "";

      // Show loading state
      loginButton.disabled = true;
      loginButton.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Signing in...
      `;

      // Simulate network delay for better UX
      await new Promise((resolve) => setTimeout(resolve, 500));

      const res = await login(
        usernameInput.value.trim(),
        passwordInput.value,
        roleSelect.value,
        rememberMeCheckbox.checked
      );

      if (!res.ok) {
        loginMsg.textContent = res.message;
        loginMsg.classList.remove("hidden");
        loginButton.disabled = false;
        loginButton.innerHTML = "Sign in";
        return;
      }

      // Store last login info
      storage.set("hr4.lastLogin", nowMs());

      // Success animation
      loginButton.innerHTML = `
        <svg class="h-4 w-4 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Success!
      `;

      // Redirect after brief success display
      setTimeout(() => {
        location.hash = "#/dashboard";
      }, 500);
    });

    // Real-time validation
    [usernameInput, passwordInput].forEach((input) => {
      input.addEventListener("input", () => {
        if (loginMsg.textContent) {
          loginMsg.classList.add("hidden");
        }
      });
    });
  }

  function card(title, body) {
    return ui.card({ title, content: body });
  }

  async function renderDashboard() {
    const role = state.user?.role;
    const subtitle = `Role-based overview with quick insights`;

    try {
      // Fetch real dashboard data from API
      const response = await fetch("api/dashboard.php", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const result = await response.json();
      const data = result.ok ? result.data : {};

      // Create dynamic cards based on real data
      const cards = [
        card("Total Employees", data.total_employees || "—"),
        card("Departments", data.total_departments || "—"),
        card("Payroll Entries", data.payroll?.total_payroll_entries || "—"),
        card("Active Benefits", data.benefits?.total_enrollments || "—"),
      ].join("");

      // Role-specific content
      let roleSpecificContent = "";
      if (role === "HR Manager" && data.department_stats) {
        const deptStats = data.department_stats
          .map(
            (dept) =>
              `<div class="flex justify-between text-sm">
            <span>${dept.department_name}</span>
            <span class="font-medium">${dept.employee_count}</span>
          </div>`
          )
          .join("");
        roleSpecificContent = `
          <div class="space-y-2">
            ${card("Department Statistics", deptStats)}
          </div>
        `;
      }

      // Recent activities
      let activitiesContent =
        '<div class="text-sm text-slate-500">No recent activities</div>';
      if (data.recent_activities && data.recent_activities.length > 0) {
        activitiesContent = data.recent_activities
          .map(
            (activity) =>
              `<div class="flex justify-between text-sm">
            <span>${activity.action_type} - ${
                activity.action_description
              }</span>
            <span class="text-slate-500">${new Date(
              activity.action_timestamp
            ).toLocaleDateString()}</span>
          </div>`
          )
          .join("");
      }

      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">${role ? role : "Dashboard"}</h1>
            <p class="text-xs text-slate-500 mt-1">${subtitle}</p>
          </div>
          <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">${cards}</div>
          ${roleSpecificContent}
          <div class="space-y-2">
            ${card("Recent Activities", activitiesContent)}
          </div>
        </section>
      `;
      renderShell(html, "dashboard");
    } catch (error) {
      console.error("Dashboard error:", error);
      // Fallback to basic dashboard
      const cards = [
        card("Headcount", "—"),
        card("Payroll Status", "—"),
        card("Compliance Alerts", "—"),
        card("Quick Actions", "Common functions at a glance"),
      ].join("");
      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">${role ? role : "Dashboard"}</h1>
            <p class="text-xs text-slate-500 mt-1">${subtitle}</p>
          </div>
          <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">${cards}</div>
          <div class="space-y-2">
            ${card("Activity", "Recent actions will appear here")}
          </div>
        </section>
      `;
      renderShell(html, "dashboard");
    }
  }

  function renderPlaceholder(id, title, description) {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <h1 class="text-lg font-semibold">${title}</h1>
        ${card("Overview", description)}
        ${card(
          "Next Steps",
          "Integrate with backend endpoints per HR4 schema when available."
        )}
      </section>
    `;
    renderShell(html, id);
  }

  // Detailed page renderers (Frontend-only, mock data shells)
  async function employeesPage() {
    try {
      // Fetch employees data from API
      const response = await fetch("api/employees.php", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const result = await response.json();
      const employees = result.ok ? result.data : [];

      // Fetch departments for filter
      const deptResponse = await fetch("api/departments.php", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const deptResult = await deptResponse.json();
      const departments = deptResult.ok ? deptResult.data : [];

      // Build department options
      const deptOptions = [
        { value: "", label: "All Departments" },
        ...departments.map((dept) => ({
          value: dept.id,
          label: dept.department_name,
        })),
      ];

      const controls = `
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
          <div class="flex flex-col sm:flex-row gap-2">
            ${ui.input({ id: "empSearch", placeholder: "Search name/number" })}
            ${ui.select({
              id: "empDept",
              options: deptOptions,
            })}
            ${ui.select({
              id: "empStatus",
              options: [
                { value: "", label: "All Status" },
                { value: "Active", label: "Active" },
                { value: "Inactive", label: "Inactive" },
                { value: "Resigned", label: "Resigned" },
              ],
            })}
          </div>
          <div class="flex gap-2">${ui.button("Add Employee", {
            variant: "default",
            size: "sm",
            id: "btnAddEmp",
          })}${ui.button("Export", { variant: "outline", size: "sm" })}</div>
        </div>`;

      // Build employee rows
      const employeeRows = employees.map((emp) => [
        emp.employee_number,
        `${emp.first_name} ${emp.last_name}`,
        emp.department_name || "—",
        emp.position_title || "—",
        `<span class="px-2 py-1 text-xs rounded-full ${
          emp.status === "Active"
            ? "bg-green-100 text-green-800"
            : "bg-gray-100 text-gray-800"
        }">${emp.status}</span>`,
        `<div class="flex gap-1">
          <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewEmployee(${emp.id})">View</button>
          <button class="text-green-600 hover:text-green-800 text-sm" onclick="editEmployee(${emp.id})">Edit</button>
        </div>`,
      ]);

      // Calculate KPIs
      const totalEmployees = employees.length;
      const activeEmployees = employees.filter(
        (emp) => emp.status === "Active"
      ).length;
      const inactiveEmployees = employees.filter(
        (emp) => emp.status === "Inactive"
      ).length;

      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">Employee Management</h1>
            <p class="text-xs text-slate-500 mt-1">Directory, profiles, onboarding and offboarding</p>
          </div>
          <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            ${ui.kpi({ label: "Total Employees", value: totalEmployees })}
            ${ui.kpi({ label: "Active", value: activeEmployees })}
            ${ui.kpi({ label: "Inactive", value: inactiveEmployees })}
            ${ui.kpi({ label: "Departments", value: departments.length })}
          </div>
          ${ui.table({
            headers: [
              "Emp #",
              "Name",
              "Department",
              "Position",
              "Status",
              "Actions",
            ],
            rows: employeeRows,
            empty: ui.empty({
              title: "No employees found",
              description: "Start by adding your first employee record.",
              action: ui.button("Add Employee", {
                variant: "default",
                size: "sm",
              }),
            }),
            controls,
          })}
        </section>`;
      renderShell(html, "employees");
    } catch (error) {
      console.error("Employees page error:", error);
      // Fallback to basic page
      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">Employee Management</h1>
            <p class="text-xs text-slate-500 mt-1">Directory, profiles, onboarding and offboarding</p>
          </div>
          <div class="text-center py-8">
            <p class="text-slate-500">Error loading employees data. Please try again.</p>
          </div>
        </section>`;
      renderShell(html, "employees");
    }
  }

  async function payrollPage() {
    try {
      // Fetch payroll data from API
      const response = await fetch("api/payroll.php", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const result = await response.json();
      const periods = result.ok ? result.data : [];

      const controls = `
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
          <div class="flex gap-2">
            ${ui.select({
              id: "period",
              options: [
                { value: "", label: "All Periods" },
                ...periods.map((period) => ({
                  value: period.id,
                  label:
                    period.period_name ||
                    `${period.start_date} to ${period.end_date}`,
                })),
              ],
            })}
            ${ui.select({
              id: "runStatus",
              options: [
                { value: "", label: "All Status" },
                { value: "Open", label: "Open" },
                { value: "Processed", label: "Processed" },
                { value: "Closed", label: "Closed" },
              ],
            })}
          </div>
          <div>${ui.button("New Run", { variant: "default", size: "sm" })}</div>
        </div>`;

      // Build payroll period rows
      const periodRows = periods.map((period) => [
        period.period_name || `${period.start_date} to ${period.end_date}`,
        "Regular",
        `<span class="px-2 py-1 text-xs rounded-full ${
          period.status === "Processed"
            ? "bg-green-100 text-green-800"
            : period.status === "Open"
            ? "bg-yellow-100 text-yellow-800"
            : "bg-gray-100 text-gray-800"
        }">${period.status}</span>`,
        period.entry_count || 0,
        `<div class="flex gap-1">
          <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewPayrollPeriod(${period.id})">View</button>
          <button class="text-green-600 hover:text-green-800 text-sm" onclick="editPayrollPeriod(${period.id})">Edit</button>
        </div>`,
      ]);

      // Calculate KPIs
      const currentPeriod =
        periods.find((p) => p.status === "Open") || periods[0];
      const totalPeriods = periods.length;
      const processedPeriods = periods.filter(
        (p) => p.status === "Processed"
      ).length;
      const totalAmount = periods.reduce(
        (sum, p) => sum + (p.total_amount || 0),
        0
      );

      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">Payroll</h1>
            <p class="text-xs text-slate-500 mt-1">Processing calendar, approvals and exception handling</p>
          </div>
          <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            ${ui.kpi({
              label: "Current Period",
              value: currentPeriod
                ? currentPeriod.period_name ||
                  `${currentPeriod.start_date} to ${currentPeriod.end_date}`
                : "—",
              sub: currentPeriod
                ? `${currentPeriod.start_date} - ${currentPeriod.end_date}`
                : "No active period",
            })}
            ${ui.kpi({
              label: "Status",
              value: currentPeriod ? currentPeriod.status : "—",
            })}
            ${ui.kpi({
              label: "Total Net Pay",
              value: `₱${totalAmount.toLocaleString()}`,
            })}
            ${ui.kpi({ label: "Processed", value: processedPeriods })}
          </div>
          ${ui.card({
            title: "Processing Calendar",
            content: "Upcoming cycles and approvals.",
          })}
          ${ui.table({
            headers: ["Period", "Run Type", "Status", "Processed", "Actions"],
            rows: periodRows,
            empty: ui.empty({
              title: "No payroll runs",
              description: "Generate a new payroll run to begin processing.",
              action: ui.button("Create Run", {
                variant: "default",
                size: "sm",
              }),
            }),
            controls,
          })}
        </section>`;
      renderShell(html, "payroll");
    } catch (error) {
      console.error("Payroll page error:", error);
      // Fallback to basic page
      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">Payroll</h1>
            <p class="text-xs text-slate-500 mt-1">Processing calendar, approvals and exception handling</p>
          </div>
          <div class="text-center py-8">
            <p class="text-slate-500">Error loading payroll data. Please try again.</p>
          </div>
        </section>`;
      renderShell(html, "payroll");
    }
  }

  async function benefitsPage() {
    try {
      // Fetch benefits data from API
      const response = await fetch("api/benefits.php", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      const result = await response.json();
      const benefits = result.ok ? result.data : [];

      const controls = `
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
          <div class="flex gap-2">
            ${ui.select({
              id: "planFilter",
              options: [
                { value: "", label: "All Plans" },
                ...benefits.map((benefit) => ({
                  value: benefit.id,
                  label: benefit.plan_name || benefit.provider_name,
                })),
              ],
            })}
            ${ui.select({
              id: "enrollStatus",
              options: [
                { value: "", label: "All Status" },
                { value: "Active", label: "Active" },
                { value: "Inactive", label: "Inactive" },
              ],
            })}
          </div>
          <div>${ui.button("Add Enrollment", {
            variant: "default",
            size: "sm",
          })}</div>
        </div>`;

      // Build benefits rows
      const benefitRows = benefits.map((benefit) => [
        benefit.employee_name || "—",
        benefit.plan_name || benefit.provider_name,
        `<span class="px-2 py-1 text-xs rounded-full ${
          benefit.status === "Active"
            ? "bg-green-100 text-green-800"
            : "bg-gray-100 text-gray-800"
        }">${benefit.status}</span>`,
        benefit.enrollment_date || "—",
        `<div class="flex gap-1">
          <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewBenefit(${benefit.id})">View</button>
          <button class="text-green-600 hover:text-green-800 text-sm" onclick="editBenefit(${benefit.id})">Edit</button>
        </div>`,
      ]);

      // Calculate KPIs
      const activeEnrollments = benefits.filter(
        (b) => b.status === "Active"
      ).length;
      const pendingClaims = benefits.filter(
        (b) => b.claim_status === "Pending"
      ).length;
      const totalPremiums = benefits.reduce(
        (sum, b) => sum + (b.monthly_premium || 0),
        0
      );
      const uniqueProviders = new Set(benefits.map((b) => b.provider_name))
        .size;

      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">Benefits Administration</h1>
            <p class="text-xs text-slate-500 mt-1">Plans, enrollments, claims and providers</p>
          </div>
          <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            ${ui.kpi({ label: "Active Enrollments", value: activeEnrollments })}
            ${ui.kpi({ label: "Claims Pending", value: pendingClaims })}
            ${ui.kpi({
              label: "Monthly Premium",
              value: `₱${totalPremiums.toLocaleString()}`,
            })}
            ${ui.kpi({ label: "Providers", value: uniqueProviders })}
          </div>
          ${ui.table({
            headers: ["Employee", "Plan", "Status", "Since", "Actions"],
            rows: benefitRows,
            empty: ui.empty({
              title: "No enrollments found",
              description: "Add an employee to an HMO plan to get started.",
              action: ui.button("Add Enrollment", {
                variant: "default",
                size: "sm",
              }),
            }),
            controls,
          })}
        </section>`;
      renderShell(html, "benefits");
    } catch (error) {
      console.error("Benefits page error:", error);
      // Fallback to basic page
      const html = `
        <section class="p-4 lg:p-6 space-y-4">
          <div>
            <h1 class="text-lg font-semibold">Benefits Administration</h1>
            <p class="text-xs text-slate-500 mt-1">Plans, enrollments, claims and providers</p>
          </div>
          <div class="text-center py-8">
            <p class="text-slate-500">Error loading benefits data. Please try again.</p>
          </div>
        </section>`;
      renderShell(html, "benefits");
    }
  }

  function organizationPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Department Control</h1>
          <p class="text-xs text-slate-500 mt-1">Structure, heads, and budget allocation</p>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          ${ui.kpi({ label: "Departments", value: "—" })}
          ${ui.kpi({ label: "Positions", value: "—" })}
          ${ui.kpi({ label: "Open Roles", value: "—" })}
          ${ui.kpi({ label: "Budget", value: "—" })}
        </div>
        ${ui.card({
          title: "Org Chart",
          content: "Visual org structure placeholder.",
        })}
        ${ui.table({
          headers: ["Department", "Head", "Parent", "Budget"],
          rows: [],
          empty: ui.empty({
            title: "No departments",
            description: "Create departments to build your organization.",
          }),
        })}
      </section>`;
    renderShell(html, "organization");
  }

  function documentsPage(title = "Documents", active = "documents") {
    const controls = `
      <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
        <div class="flex gap-2">
          ${ui.input({ id: "docSearch", placeholder: "Search documents" })}
          ${ui.select({
            id: "docType",
            options: [
              { value: "", label: "All Types" },
              { value: "Policy", label: "Policy" },
              { value: "Form", label: "Form" },
              { value: "Contract", label: "Contract" },
            ],
          })}
        </div>
        <div>${ui.button("Upload", { variant: "default", size: "sm" })}</div>
      </div>`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">${title}</h1>
          <p class="text-xs text-slate-500 mt-1">Library with access logs and versioning</p>
        </div>
        ${ui.table({
          headers: ["Name", "Type", "Updated", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No documents",
            description: "Upload policies, templates, and forms.",
            action: ui.button("Upload", { variant: "default", size: "sm" }),
          }),
          controls,
        })}
      </section>`;
    renderShell(html, active);
  }

  function analyticsPage(title = "Analytics Hub", active = "analytics") {
    const controls = `
      <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
        <div class="flex gap-2">
          ${ui.select({
            id: "range",
            options: [
              { value: "mtd", label: "MTD" },
              { value: "qtd", label: "QTD" },
              { value: "ytd", label: "YTD" },
            ],
          })}
          ${ui.select({
            id: "gran",
            options: [
              { value: "month", label: "Monthly" },
              { value: "quarter", label: "Quarterly" },
            ],
          })}
        </div>
        <div></div>
      </div>`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">${title}</h1>
          <p class="text-xs text-slate-500 mt-1">Real-time workforce and payroll analytics</p>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          ${ui.kpi({ label: "Headcount", value: "—" })}
          ${ui.kpi({ label: "Turnover", value: "—" })}
          ${ui.kpi({ label: "Labor Cost", value: "—" })}
          ${ui.kpi({ label: "Overtime", value: "—" })}
        </div>
        ${ui.card({
          title: "Charts",
          content: "Interactive charts placeholder.",
          footer: controls,
        })}
      </section>`;
    renderShell(html, active);
  }

  function settingsPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Settings</h1>
          <p class="text-xs text-slate-500 mt-1">Users, roles, permissions, and system configuration</p>
        </div>
        ${ui.card({
          title: "User Management",
          content: "Manage users, roles, permissions.",
        })}
        ${ui.card({
          title: "System Configuration",
          content: "Global settings and preferences.",
        })}
      </section>`;
    renderShell(html, "settings");
  }

  // Compensation Manager
  function compensationPlanningPage() {
    const controls = `
      <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
        <div class="flex gap-2">
          ${ui.select({
            id: "cycle",
            options: [
              { value: "", label: "Current Cycle" },
              { value: "prev", label: "Previous" },
            ],
          })}
          ${ui.select({
            id: "approval",
            options: [
              { value: "", label: "All Approvals" },
              { value: "Pending", label: "Pending" },
              { value: "Approved", label: "Approved" },
            ],
          })}
        </div>
        <div>${ui.button("New Plan", { variant: "default", size: "sm" })}</div>
      </div>`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Compensation Planning</h1>
          <p class="text-xs text-slate-500 mt-1">Budgets, increases, equity, and approvals</p>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          ${ui.kpi({ label: "Budget", value: "—" })}
          ${ui.kpi({ label: "Utilization", value: "—" })}
          ${ui.kpi({ label: "Pending Approvals", value: "—" })}
          ${ui.kpi({ label: "Market Index", value: "—" })}
        </div>
        ${ui.table({
          headers: ["Department", "Budget", "Proposed", "Variance", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No planning cycles",
            description: "Create a cycle to start planning.",
            action: ui.button("New Plan", { variant: "default", size: "sm" }),
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "compensation");
  }

  function structuresPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Salary Structures</h1>
          <p class="text-xs text-slate-500 mt-1">Salary bands, position mapping, history</p>
        </div>
        ${ui.table({
          headers: ["Grade", "Min", "Max", "Positions", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No structures",
            description: "Create a salary grade to begin.",
          }),
          controls: `${ui.button("Add Grade", {
            variant: "default",
            size: "sm",
          })}`,
        })}
      </section>`;
    renderShell(html, "structures");
  }

  function meritIncreasesPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Merit Increases</h1>
          <p class="text-xs text-slate-500 mt-1">Review cycles, manager input, batch processing</p>
        </div>
        ${ui.table({
          headers: ["Cycle", "Dept", "Budget", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No merit cycles",
            description: "Create or open a review cycle.",
          }),
          controls: `${ui.button("New Cycle", {
            variant: "default",
            size: "sm",
          })}`,
        })}
      </section>`;
    renderShell(html, "merit");
  }

  function equityAnalysisPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Pay Equity Analysis</h1>
          <p class="text-xs text-slate-500 mt-1">Gap analysis by role, gender, department</p>
        </div>
        ${ui.card({
          title: "Equity Dashboard",
          content: "Charts and analysis placeholder.",
        })}
      </section>`;
    renderShell(html, "equity");
  }

  function budgetManagementPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Budget Management</h1>
          <p class="text-xs text-slate-500 mt-1">Allocation, utilization, variance</p>
        </div>
        ${ui.table({
          headers: ["Department", "Allocated", "Used", "Variance"],
          rows: [],
          empty: ui.empty({
            title: "No budgets",
            description: "Set department budgets to manage spend.",
          }),
        })}
      </section>`;
    renderShell(html, "budget");
  }

  function reportsCenterPage(title = "Reports Center", active = "reports") {
    const controls = `${ui.select({
      id: "format",
      options: [
        { value: "pdf", label: "PDF" },
        { value: "xlsx", label: "Excel" },
      ],
    })}${ui.button("Export", { variant: "outline", size: "sm" })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">${title}</h1>
          <p class="text-xs text-slate-500 mt-1">Standard and custom reports</p>
        </div>
        ${ui.table({
          headers: ["Report", "Type", "Last Run", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No reports",
            description: "Generate or schedule reports here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, active);
  }

  function marketAnalysisPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Market Analysis</h1>
          <p class="text-xs text-slate-500 mt-1">Benchmarks, trends, geographic factors</p>
        </div>
        ${ui.card({
          title: "Market Data",
          content: "Benchmark charts placeholder.",
        })}
      </section>`;
    renderShell(html, "market");
  }

  // Benefits Coordinator
  function claimsProcessingPage() {
    const controls = `${ui.select({
      id: "claimFilter",
      options: [
        { value: "", label: "All" },
        { value: "Pending", label: "Pending" },
        { value: "Approved", label: "Approved" },
        { value: "Rejected", label: "Rejected" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Claims Processing</h1>
          <p class="text-xs text-slate-500 mt-1">Queue, verification, and approvals</p>
        </div>
        ${ui.table({
          headers: ["Employee", "Plan", "Amount", "Date", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No claims in queue",
            description: "New submissions will appear here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "claims");
  }

  function providersPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Provider Network</h1>
          <p class="text-xs text-slate-500 mt-1">Directory, contracts, and performance</p>
        </div>
        ${ui.table({
          headers: ["Provider", "Contact", "Plans", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No providers",
            description: "Add providers to manage coverage.",
          }),
          controls: `${ui.button("Add Provider", {
            variant: "default",
            size: "sm",
          })}`,
        })}
      </section>`;
    renderShell(html, "providers");
  }

  function enrollmentCenterPage() {
    const controls = `${ui.button("Open Enrollment", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Enrollment Center</h1>
          <p class="text-xs text-slate-500 mt-1">Open windows, changes, verification</p>
        </div>
        ${ui.table({
          headers: ["Employee", "Plan", "Status", "Submitted", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No enrollment requests",
            description: "Requests will appear here during enrollment.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "enrollment");
  }

  function benefitsAnalyticsPage() {
    return analyticsPage("Benefits Analytics", "benefits-analytics");
  }

  function memberServicesPage() {
    const controls = `${ui.select({
      id: "inquiryStatus",
      options: [
        { value: "", label: "All" },
        { value: "Open", label: "Open" },
        { value: "Resolved", label: "Resolved" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Member Services</h1>
          <p class="text-xs text-slate-500 mt-1">Inquiries, response times, satisfaction</p>
        </div>
        ${ui.table({
          headers: ["Member", "Subject", "Status", "Updated", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No inquiries",
            description: "Support tickets will appear here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "member");
  }

  // Payroll Admin
  function taxManagementPage() {
    const controls = `${ui.select({
      id: "taxPeriod",
      options: [
        { value: "", label: "All Periods" },
        { value: "q1", label: "Q1" },
        { value: "q2", label: "Q2" },
        { value: "q3", label: "Q3" },
        { value: "q4", label: "Q4" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Tax Management</h1>
          <p class="text-xs text-slate-500 mt-1">Withholding, filings, and regulatory updates</p>
        </div>
        ${ui.card({
          title: "Regulatory Updates",
          content: "Latest updates placeholder.",
        })}
        ${ui.table({
          headers: ["Period", "Withholding", "Filings", "Status"],
          rows: [],
          empty: ui.empty({
            title: "No tax records",
            description: "Configure tax rules and process filings.",
            action: ui.button("Configure", { variant: "outline", size: "sm" }),
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "tax");
  }

  function deductionsControlPage() {
    const controls = `${ui.select({
      id: "deductionType",
      options: [
        { value: "", label: "All Types" },
        { value: "Benefit", label: "Benefit" },
        { value: "Loan", label: "Loan" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Deductions Control</h1>
          <p class="text-xs text-slate-500 mt-1">Benefit deductions, loans, voluntary contributions</p>
        </div>
        ${ui.table({
          headers: ["Employee", "Type", "Amount", "Effective", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No deductions",
            description: "Configured deductions will appear here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "deductions");
  }

  function bankFilesPage() {
    const controls = `${ui.button("Generate File", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Bank Files</h1>
          <p class="text-xs text-slate-500 mt-1">Disbursement generation and reconciliation</p>
        </div>
        ${ui.table({
          headers: ["Run", "Bank", "Status", "Generated", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No bank files",
            description: "Generate files after payroll processing.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "bank");
  }

  function payrollReportsPage() {
    return reportsCenterPage("Reports & Analytics", "reports");
  }

  function complianceCenterPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Compliance Center</h1>
          <p class="text-xs text-slate-500 mt-1">Obligations, audits, risk flags</p>
        </div>
        ${ui.table({
          headers: ["Requirement", "Due", "Status", "Owner"],
          rows: [],
          empty: ui.empty({
            title: "No items",
            description: "Compliance tasks will be listed here.",
          }),
        })}
      </section>`;
    renderShell(html, "compliance");
  }

  // Department Head
  function teamManagementPage() {
    const controls = `
      <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
        <div class="flex gap-2">
          ${ui.input({ id: "teamSearch", placeholder: "Search team" })}
          ${ui.select({
            id: "teamStatus",
            options: [
              { value: "", label: "All Status" },
              { value: "Active", label: "Active" },
              { value: "Leave", label: "On Leave" },
            ],
          })}
        </div>
        <div></div>
      </div>`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Team Management</h1>
          <p class="text-xs text-slate-500 mt-1">Team directory, approvals, and summaries</p>
        </div>
        ${ui.table({
          headers: ["Employee", "Position", "Status", "Costs"],
          rows: [],
          empty: ui.empty({
            title: "No team members",
            description: "Your direct reports will appear here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "team");
  }

  function deptBudgetTrackingPage() {
    return budgetManagementPage();
  }

  function leaveManagementPage() {
    const controls = `${ui.select({
      id: "leaveStatus",
      options: [
        { value: "", label: "All" },
        { value: "Pending", label: "Pending" },
        { value: "Approved", label: "Approved" },
        { value: "Rejected", label: "Rejected" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Leave Management</h1>
          <p class="text-xs text-slate-500 mt-1">Requests, approvals, and balances</p>
        </div>
        ${ui.table({
          headers: ["Employee", "Type", "Dates", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No leave requests",
            description: "Team requests will appear here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "leave");
  }

  function performanceReviewPage() {
    const controls = `${ui.select({
      id: "reviewCycle",
      options: [
        { value: "", label: "Current" },
        { value: "prev", label: "Previous" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Performance Review</h1>
          <p class="text-xs text-slate-500 mt-1">Evaluations, feedback, calibration</p>
        </div>
        ${ui.table({
          headers: ["Employee", "Rating", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No reviews",
            description: "Review items will appear per cycle.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "performance");
  }

  function deptReportsPage() {
    return reportsCenterPage("Department Reports", "reports");
  }

  function documentAccessPage() {
    return documentsPage("Document Access", "documents");
  }

  // Employee Self-Service
  function myProfilePage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">My Profile</h1>
          <p class="text-xs text-slate-500 mt-1">Personal info, employment details, and security</p>
        </div>
        ${ui.card({
          title: "Personal Information",
          content: "Name, contacts, address.",
        })}
        ${ui.card({
          title: "Employment Details",
          content: "Department, position, status.",
        })}
        ${ui.card({
          title: "Security Settings",
          content: "Password & 2FA configuration.",
        })}
      </section>`;
    renderShell(html, "profile");
  }

  function payslipsPage(active = "payslips") {
    const controls = `${ui.select({
      id: "year",
      options: [
        { value: "", label: "All Years" },
        { value: "2025", label: "2025" },
        { value: "2024", label: "2024" },
      ],
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Payslips</h1>
          <p class="text-xs text-slate-500 mt-1">Latest payslips with detailed breakdowns</p>
        </div>
        ${ui.table({
          headers: ["Period", "Net Pay", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No payslips available",
            description: "Your payslips will appear after processing.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, active);
  }

  function benefitsCenterPage() {
    const controls = `${ui.button("Enroll/Update", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Benefits Center</h1>
          <p class="text-xs text-slate-500 mt-1">Active plans, contributions, and claim history</p>
        </div>
        ${ui.table({
          headers: ["Plan", "Status", "Contribution", "Since"],
          rows: [],
          empty: ui.empty({
            title: "No active benefits",
            description: "Your enrollments will show here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "benefits-center");
  }

  function employeeLeaveRequestPage() {
    const controls = `${ui.button("New Request", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Leave Request</h1>
          <p class="text-xs text-slate-500 mt-1">Available balance and request history</p>
        </div>
        ${ui.table({
          headers: ["Type", "Dates", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No leave history",
            description: "Submit a request to see it here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "leave");
  }

  function employeeDocumentsPage() {
    return documentsPage("Documents", "documents");
  }

  function helpCenterPage() {
    const controls = `${ui.button("Submit Ticket", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Help Center</h1>
          <p class="text-xs text-slate-500 mt-1">FAQ, tutorials, and support</p>
        </div>
        ${ui.table({
          headers: ["Topic", "Updated"],
          rows: [],
          empty: ui.empty({
            title: "No articles yet",
            description: "Knowledge base will appear here.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "help");
  }

  // Executive
  function executiveDashboardPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Executive Dashboard</h1>
          <p class="text-xs text-slate-500 mt-1">High-level workforce, cost, and compliance KPIs</p>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
          ${ui.kpi({ label: "Total Headcount", value: "—" })}
          ${ui.kpi({ label: "Total Cost", value: "—" })}
          ${ui.kpi({ label: "Turnover", value: "—" })}
          ${ui.kpi({ label: "Compliance", value: "—" })}
        </div>
        ${ui.card({
          title: "Strategic Indicators",
          content: "KPIs and forecasts.",
        })}
      </section>`;
    renderShell(html, "executive");
  }

  function strategyPage() {
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Strategic Planning</h1>
          <p class="text-xs text-slate-500 mt-1">Objectives, milestones, resource allocation</p>
        </div>
        ${ui.table({
          headers: ["Objective", "Owner", "Due", "Status"],
          rows: [],
          empty: ui.empty({
            title: "No strategic items",
            description: "Add objectives to plan ahead.",
          }),
        })}
      </section>`;
    renderShell(html, "strategy");
  }

  function costAnalysisPage() {
    return analyticsPage("Cost Analysis", "cost");
  }

  function workforceAnalyticsPage() {
    return analyticsPage("Workforce Analytics", "workforce");
  }

  function complianceOverviewPage() {
    return complianceCenterPage();
  }

  function executiveReportsPage() {
    return reportsCenterPage("Executive Reports", "reports");
  }

  function delegationsPage() {
    const controls = `${ui.button("New Delegation", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Delegations</h1>
          <p class="text-xs text-slate-500 mt-1">Temporary roles, approval chains, tracking</p>
        </div>
        ${ui.table({
          headers: ["Delegate", "Role", "From", "To", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No delegations",
            description: "Create delegations for coverage.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "delegations");
  }

  function bulkOperationsPage() {
    const controls = `${ui.button("Start Bulk Update", {
      variant: "default",
      size: "sm",
    })}`;
    const html = `
      <section class="p-4 lg:p-6 space-y-4">
        <div>
          <h1 class="text-lg font-semibold">Bulk Operations</h1>
          <p class="text-xs text-slate-500 mt-1">Mass updates, document processing, validation</p>
        </div>
        ${ui.table({
          headers: ["Job", "Type", "Submitted", "Status", "Actions"],
          rows: [],
          empty: ui.empty({
            title: "No bulk jobs",
            description: "Upload a file to start processing.",
          }),
          controls,
        })}
      </section>`;
    renderShell(html, "bulk");
  }

  const routes = {
    "/login": renderLogin,
    "/dashboard": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      renderDashboard();
    },

    // HR Manager
    "/employees": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("employees"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      employeesPage();
    },
    "/payroll": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("payroll"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      payrollPage();
    },
    "/benefits": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("benefits"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      benefitsPage();
    },
    "/organization": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("organization"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      organizationPage();
    },
    "/documents": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("documents"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      documentsPage();
    },
    "/analytics": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("analytics"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      analyticsPage();
    },
    "/settings": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("settings"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      settingsPage();
    },
    "/delegations": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("delegations"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      delegationsPage();
    },
    "/bulk": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("bulk"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      bulkOperationsPage();
    },

    // Compensation Manager
    "/compensation": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("compensation"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      compensationPlanningPage();
    },
    "/structures": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("structures"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      structuresPage();
    },
    "/merit": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("merit"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      meritIncreasesPage();
    },
    "/equity": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("equity"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      equityAnalysisPage();
    },
    "/budget": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("budget"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      budgetManagementPage();
    },
    "/reports": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("reports"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      reportsCenterPage();
    },
    "/market": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("market"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      marketAnalysisPage();
    },

    // Benefits Coordinator
    "/claims": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("claims"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      claimsProcessingPage();
    },
    "/providers": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("providers"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      providersPage();
    },
    "/enrollment": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("enrollment"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      enrollmentCenterPage();
    },
    "/benefits-analytics": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("benefits-analytics"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      benefitsAnalyticsPage();
    },
    "/member": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("member"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      memberServicesPage();
    },

    // Payroll Admin
    "/tax": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("tax"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      taxManagementPage();
    },
    "/deductions": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("deductions"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      deductionsControlPage();
    },
    "/bank": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("bank"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      bankFilesPage();
    },
    "/payslips": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("payslips"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      payslipsPage();
    },
    "/reports/payroll": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("reports"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      payrollReportsPage();
    },
    "/compliance": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("compliance"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      complianceCenterPage();
    },

    // Department Head
    "/team": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("team"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      teamManagementPage();
    },
    "/budget": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("budget"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      deptBudgetTrackingPage();
    },
    "/leave": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("leave"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      // Role-based leave pages: Department Head vs Employee
      if (state.user?.role === ROLES.DEPT_HEAD) {
        leaveManagementPage();
      } else {
        employeeLeaveRequestPage();
      }
    },
    "/performance": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("performance"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      performanceReviewPage();
    },
    "/reports/department": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("reports"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      deptReportsPage();
    },
    "/documents/department": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("documents"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      documentAccessPage();
    },

    // Employee Self-Service
    "/profile": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("profile"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      myProfilePage();
    },
    "/benefits-center": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("benefits-center"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      benefitsCenterPage();
    },
    "/documents/employee": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("documents"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      employeeDocumentsPage();
    },
    "/help": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("help"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      helpCenterPage();
    },

    // Executive
    "/executive": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("executive"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      executiveDashboardPage();
    },
    "/strategy": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("strategy"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      strategyPage();
    },
    "/cost": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("cost"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      costAnalysisPage();
    },
    "/workforce": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("workforce"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      workforceAnalyticsPage();
    },
    "/compliance/overview": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("compliance"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      complianceOverviewPage();
    },
    "/reports/executive": () => {
      if (!isSessionValid()) return (location.hash = "#/login");
      if (!guard("reports"))
        return renderPlaceholder(
          "dashboard",
          "Unauthorized",
          "You do not have access to this module."
        );
      executiveReportsPage();
    },
  };

  // Generate placeholder routes based on sidebar definitions (fallbacks)
  Object.values(SIDEBAR_ITEMS)
    .flat()
    .forEach(({ id, label }) => {
      if (routes[`/${id}`]) return;
      routes[`/${id}`] = () => {
        if (!isSessionValid()) return (location.hash = "#/login");
        if (!guard(id))
          return renderPlaceholder(
            "dashboard",
            "Unauthorized",
            "You do not have access to this module."
          );
        renderPlaceholder(id, label, `${label} module placeholder.`);
      };
    });

  function handleRoute() {
    applyTheme();
    const hash = location.hash.replace("#", "") || "/login";
    const path = hash.startsWith("/") ? hash : `/${hash}`;
    const fn = routes[path] || routes["/login"];
    fn();
  }

  window.addEventListener("hashchange", handleRoute);
  window.addEventListener("DOMContentLoaded", handleRoute);
})();
