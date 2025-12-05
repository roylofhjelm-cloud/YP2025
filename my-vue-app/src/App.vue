<template>
  <div id="app">
    <AppHeader
      :theme="theme"
      @toggle-theme="toggleTheme"
    />
    <main class="p-6">
      <router-view />
    </main>
  </div>
</template>

<script>
import AppHeader from "./components/AppHeader.vue";

export default {
  name: "App",
  components: { AppHeader },
  data() {
    return {
      theme: this.getInitialTheme(),
    };
  },
  computed: {
    themeClass() {
      return this.theme === "dark" ? "theme-dark" : "theme-light";
    },
  },
  created() {
    this.applyTheme();
  },
  methods: {
    getInitialTheme() {
      const saved = localStorage.getItem("theme");
      if (saved === "dark" || saved === "light") return saved;

      if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
        return "dark";
      }
      return "light";
    },
    toggleTheme() {
      this.theme = this.theme === "dark" ? "light" : "dark";
      this.applyTheme();
    },
    applyTheme() {
      const root = document.documentElement;
      root.classList.remove("theme-light", "theme-dark");
      root.classList.add(this.themeClass);

      document.body.classList.remove("theme-light", "theme-dark");
      document.body.classList.add(this.themeClass);

      localStorage.setItem("theme", this.theme);
    },
  },
};
</script>

<style>
:root {
  --bg: #f9fafb;
  --text: #0f172a;
  --text-muted: #475569;
  --surface: #ffffff;
  --surface-alt: #f8fafc;
  --border: #e2e8f0;
  --shadow-soft: 0 8px 20px rgba(15, 23, 42, 0.05);
  --shadow-strong: 0 30px 70px rgba(15, 23, 42, 0.08);
  --primary: #2563eb;
  --primary-strong: #1d4ed8;
  --primary-gradient: linear-gradient(135deg, #4f46e5, #2563eb);
  --accent: #eef2ff;
  --chip-border: rgba(79, 70, 229, 0.2);
  --chip-shadow: rgba(79, 70, 229, 0.15);
  --danger: #dc2626;
  --danger-soft: rgba(220, 38, 38, 0.12);
  --tag-bg: #e0e7ff;
  --tag-text: #1e3a8a;
  --success-bg: #ecfccb;
  --success-text: #3f6212;
  --error-bg: #fee2e2;
  --error-text: #b91c1c;
  --input-bg: #ffffff;
  --input-border: #cbd5e1;
  --input-text: #0f172a;
}

:root.theme-dark {
  --bg: #0b1220;
  --text: #e5e7eb;
  --text-muted: #cbd5e1;
  --surface: #111827;
  --surface-alt: #0f172a;
  --border: #1f2937;
  --shadow-soft: 0 8px 20px rgba(0, 0, 0, 0.45);
  --shadow-strong: 0 30px 70px rgba(0, 0, 0, 0.45);
  --primary: #60a5fa;
  --primary-strong: #93c5fd;
  --primary-gradient: linear-gradient(135deg, #2563eb, #60a5fa);
  --accent: #1e293b;
  --chip-border: rgba(96, 165, 250, 0.4);
  --chip-shadow: rgba(37, 99, 235, 0.35);
  --danger: #fca5a5;
  --danger-soft: rgba(248, 113, 113, 0.2);
  --tag-bg: #1e293b;
  --tag-text: #c7d2fe;
  --success-bg: rgba(34, 197, 94, 0.15);
  --success-text: #bbf7d0;
  --error-bg: rgba(248, 113, 113, 0.18);
  --error-text: #fecdd3;
  --input-bg: #0f172a;
  --input-border: #1f2937;
  --input-text: #e5e7eb;
}

body {
  margin: 0;
  background-color: var(--bg);
  color: var(--text);
  font-family: 'Inter', sans-serif;
  transition: background-color 0.25s ease, color 0.25s ease;
}

a {
  color: var(--primary);
}

input,
textarea,
select {
  background: var(--input-bg);
  border: 1px solid var(--input-border);
  color: var(--input-text);
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

button {
  font-family: inherit;
}

.theme-dark .bg-white {
  background: var(--surface) !important;
  color: var(--text);
}

.theme-dark .text-gray-700,
.theme-dark .text-gray-500,
.theme-dark .text-gray-400 {
  color: var(--text-muted) !important;
}

.theme-dark .border {
  border-color: var(--border) !important;
}
</style>
