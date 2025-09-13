// RBAC configuration - Now handled by PHP backend
// This file is kept for backward compatibility and frontend JavaScript that might need it

// Note: All RBAC configuration is now handled by PHP in includes/rbac.php
// This file is maintained for any frontend JavaScript that might reference these constants

// For backward compatibility, we'll provide empty objects
// The actual RBAC logic is now server-side in PHP
window.HR4_RBAC = {
  ROLES: {},
  SESSION_TIMEOUT_HOURS: {},
  MFA_REQUIRED: new Set(),
  ROLE_MODULES: {},
  SIDEBAR_ITEMS: {},
};
