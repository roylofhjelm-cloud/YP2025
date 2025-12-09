// API base: point to local XAMPP path when on localhost, otherwise fall back to prod.
const isLocal = typeof window !== "undefined" && window.location.hostname === "localhost";
export const API_BASE = isLocal
  ? "http://localhost/larportalen2025/api"
  : "https://yp2025.rf.gd/api";
