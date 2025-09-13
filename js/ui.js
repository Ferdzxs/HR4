// shadcn-like UI helpers using Tailwind utilities and CSS variables

(function () {
  function clsx(...parts) {
    return parts.filter(Boolean).join(" ");
  }

  function button(
    label,
    { variant = "default", size = "md", id, extra = "" } = {}
  ) {
    const base =
      "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50";
    const variants = {
      default:
        "bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95",
      secondary:
        "bg-[hsl(var(--secondary))] text-[hsl(var(--secondary-foreground))] hover:bg-[hsl(var(--secondary))]/80",
      ghost:
        "hover:bg-[hsl(var(--accent))] hover:text-[hsl(var(--accent-foreground))]",
      outline:
        "border border-[hsl(var(--border))] bg-transparent hover:bg-[hsl(var(--accent))]",
    };
    const sizes = { sm: "h-9 px-3", md: "h-10 px-4", lg: "h-11 px-6" };
    return `<button ${id ? `id="${id}"` : ""} class="${clsx(
      base,
      variants[variant] || variants.default,
      sizes[size] || sizes.md,
      extra
    )}">${label}</button>`;
  }

  function input({
    id,
    placeholder = "",
    type = "text",
    value = "",
    extra = "",
  } = {}) {
    const classes =
      "flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm placeholder:text-[hsl(var(--muted-foreground))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2";
    return `<input id="${
      id || ""
    }" type="${type}" placeholder="${placeholder}" value="${value}" class="${clsx(
      classes,
      extra
    )}" />`;
  }

  function select({ id, options = [], extra = "" } = {}) {
    const classes =
      "flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2";
    return `<select id="${id || ""}" class="${clsx(classes, extra)}">${options
      .map((o) => `<option value="${o.value}">${o.label}</option>`)
      .join("")}</select>`;
  }

  function card({ title, content = "", footer = "" }) {
    return `
      <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
        ${
          title
            ? `<div class="p-4 border-b border-[hsl(var(--border))] font-semibold">${title}</div>`
            : ""
        }
        <div class="p-4 text-sm text-slate-600 dark:text-slate-300">${content}</div>
        ${
          footer
            ? `<div class="p-4 border-t border-[hsl(var(--border))]">${footer}</div>`
            : ""
        }
      </div>
    `;
  }

  function sheet({ open = false, content = "" } = {}) {
    return `
      <div class="fixed inset-0 z-40 ${open ? "block" : "hidden"}" data-sheet>
        <div class="absolute inset-0 bg-black/40" data-sheet-overlay></div>
        <div class="absolute left-0 top-0 h-full w-72 bg-[hsl(var(--background))] border-r border-[hsl(var(--border))] shadow-xl" data-sheet-panel>${content}</div>
      </div>
    `;
  }

  // New helpers
  function kpi({ label, value = "—", sub = "" }) {
    return `
      <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
        <div class="text-xs text-slate-500 mb-1">${label}</div>
        <div class="text-2xl font-semibold">${value}</div>
        ${sub ? `<div class="text-xs text-slate-500 mt-1">${sub}</div>` : ""}
      </div>
    `;
  }

  function table({
    headers = [],
    rows = [],
    empty = "No data",
    controls = "",
  } = {}) {
    return `
      <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
        ${
          controls
            ? `<div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">${controls}</div>`
            : ""
        }
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-[hsl(var(--secondary))]">
              <tr>
                ${headers
                  .map(
                    (h) =>
                      `<th class="text-left px-3 py-2 font-semibold">${h}</th>`
                  )
                  .join("")}
              </tr>
            </thead>
            <tbody>
              ${
                rows.length
                  ? rows
                      .map(
                        (cells) => `
                  <tr class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))]">
                    ${cells
                      .map((c) => `<td class="px-3 py-2 align-top">${c}</td>`)
                      .join("")}
                  </tr>`
                      )
                      .join("")
                  : `<tr><td class="px-3 py-6 text-center text-slate-500" colspan="${headers.length}">${empty}</td></tr>`
              }
            </tbody>
          </table>
        </div>
      </div>
    `;
  }

  function empty({
    title = "Nothing here yet",
    description = "",
    action = "",
  } = {}) {
    return `
      <div class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
        <div class="text-sm font-medium">${title}</div>
        ${
          description
            ? `<div class="text-xs text-slate-500 mt-1">${description}</div>`
            : ""
        }
        ${action ? `<div class="mt-3">${action}</div>` : ""}
      </div>
    `;
  }

  window.ui = { clsx, button, input, select, card, sheet, kpi, table, empty };
})();
