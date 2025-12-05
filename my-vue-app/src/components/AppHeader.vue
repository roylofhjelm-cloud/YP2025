<template>
  <header class="app-header">
    <div class="header-shell">
      <router-link to="/home" class="brand">
        Lärportalen
      </router-link>

      <div class="header-meta">
        <nav v-if="visibleLinks.length" class="nav-links">
          <router-link
            v-for="link in visibleLinks"
            :key="link.to"
            :to="link.to"
            :class="['nav-link', { active: isActive(link.to) }]"
          >
            {{ link.label }}
          </router-link>
        </nav>

        <button
          class="theme-toggle"
          type="button"
          :aria-label="theme === 'dark' ? 'Växla till ljust läge' : 'Växla till mörkt läge'"
          @click="$emit('toggle-theme')"
        >
          <span class="icon">{{ theme === 'dark' ? '🌙' : '☀️' }}</span>
          <span class="label">{{ theme === 'dark' ? 'Mörkt' : 'Ljust' }}</span>
        </button>

        <router-link
          v-if="session.name"
          :to="accountLink"
          class="account-chip"
        >
          <span class="avatar">{{ accountInitials }}</span>
          <span class="label">
            <small>{{ accountRoleLabel }}</small>
            <strong>{{ session.name }}</strong>
          </span>
        </router-link>

        <button
          v-if="session.role"
          class="logout-btn"
          type="button"
          @click="logout"
        >
          Logga ut
        </button>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: "AppHeader",
  props: {
    theme: {
      type: String,
      default: "light",
    },
  },
  emits: ["toggle-theme"],
  data() {
    return {
      session: {
        isStudent: false,
        isAdmin: false,
        name: null,
        role: null,
      },
    };
  },
  created() {
    this.refreshSession();
  },
  mounted() {
    window.addEventListener("storage", this.refreshSession);
  },
  beforeUnmount() {
    window.removeEventListener("storage", this.refreshSession);
  },
  watch: {
    $route() {
      this.refreshSession();
    },
  },
  computed: {
    visibleLinks() {
      const links = [];

      if (this.session.isStudent || this.session.isAdmin) {
        links.push({ to: "/home", label: "Hem" });
      }

      if (this.session.isStudent) {
        links.push({ to: "/materials", label: "Reading" });
      }

      if (this.session.isAdmin) {
        links.push({ to: "/admin", label: "Admin" });
      }

      if (!this.session.isStudent && !this.session.isAdmin) {
        links.push(
          { to: "/login-student", label: "Student" },
          { to: "/login", label: "Admin" }
        );
      }

      return links;
    },
    accountLink() {
      if (this.session.role) return "/account";
      return "/login";
    },
    accountRoleLabel() {
      if (this.session.role === "admin") return "Admin";
      if (this.session.role === "student") return "Student";
      return "";
    },
    accountInitials() {
      if (!this.session.name) return "";
      const parts = this.session.name.trim().split(/\s+/);
      const initials = parts.slice(0, 2).map(p => p[0]?.toUpperCase() || "");
      return initials.join("") || this.session.name[0].toUpperCase();
    },
  },
  methods: {
    refreshSession() {
      const studentId = localStorage.getItem("student_id");
      const adminId = localStorage.getItem("admin_id");

      this.session.isStudent = Boolean(studentId);
      this.session.isAdmin = Boolean(adminId);

      if (this.session.isAdmin) {
        this.session.name = localStorage.getItem("admin_name") || "Admin";
        this.session.role = "admin";
      } else if (this.session.isStudent) {
        this.session.name = localStorage.getItem("student_name") || "Student";
        this.session.role = "student";
      } else {
        this.session.name = null;
        this.session.role = null;
      }
    },
    isActive(target) {
      if (target === "/home") {
        return this.$route.path.startsWith("/home") || this.$route.path.startsWith("/exercise");
      }
      return this.$route.path === target;
    },
    logout() {
      if (this.session.role === "admin") {
        localStorage.removeItem("admin_id");
        localStorage.removeItem("admin_name");
        this.$router.push("/login");
      } else if (this.session.role === "student") {
        localStorage.removeItem("student_id");
        localStorage.removeItem("student_name");
        this.$router.push("/login-student");
      } else {
        localStorage.clear();
        this.$router.push("/login");
      }
      this.refreshSession();
    },
  },
};
</script>

<style scoped>
.app-header {
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  box-shadow: var(--shadow-soft);
  position: sticky;
  top: 0;
  z-index: 20;
  font-family: 'Inter', sans-serif;
}

.header-shell {
  max-width: 1100px;
  margin: 0 auto;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.brand {
  text-decoration: none;
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--primary-strong);
  letter-spacing: 0.01em;
}

.header-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-links {
  display: flex;
  gap: 0.8rem;
  align-items: center;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.nav-link {
  text-decoration: none;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.95rem;
  padding: 0.4rem 0.95rem;
  border-radius: 999px;
  transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.nav-link:hover {
  color: var(--primary);
  background: var(--accent);
}

.nav-link.active {
  color: white;
  background: var(--primary-gradient);
  box-shadow: 0 10px 20px var(--chip-shadow);
}

.theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 1px solid var(--chip-border);
  background: var(--accent);
  color: var(--text);
  border-radius: 999px;
  padding: 0.35rem 0.9rem;
  cursor: pointer;
  font-weight: 600;
  box-shadow: 0 8px 16px var(--chip-shadow);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.theme-toggle .icon {
  font-size: 0.95rem;
}

.theme-toggle .label {
  font-size: 0.85rem;
}

.theme-toggle:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 20px var(--chip-shadow);
}

.account-chip {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  background: var(--accent);
  border-radius: 999px;
  padding: 0.25rem 0.9rem;
  border: 1px solid var(--chip-border);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.account-chip:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px var(--chip-shadow);
}

.account-chip .avatar {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  background: var(--primary-gradient);
  color: white;
  font-size: 0.85rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.account-chip .label {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
  color: var(--text);
}

.account-chip .label small {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
}

.account-chip .label strong {
  font-size: 0.9rem;
}

.logout-btn {
  border: none;
  background: transparent;
  color: var(--danger);
  font-weight: 600;
  cursor: pointer;
  padding: 0.4rem 0.6rem;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.logout-btn:hover {
  background: var(--danger-soft);
}

@media (max-width: 640px) {
  .header-shell {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-meta {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .nav-links {
    width: 100%;
    justify-content: flex-start;
  }

  .account-chip {
    align-self: flex-end;
  }

  .logout-btn {
    align-self: flex-start;
  }
}
</style>
