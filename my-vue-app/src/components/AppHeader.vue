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
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
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
  color: #1d4ed8;
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
  color: #475569;
  font-weight: 500;
  font-size: 0.95rem;
  padding: 0.4rem 0.95rem;
  border-radius: 999px;
  transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.nav-link:hover {
  color: #1d4ed8;
  background: #eef2ff;
}

.nav-link.active {
  color: white;
  background: linear-gradient(135deg, #4f46e5, #2563eb);
  box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
}

.account-chip {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  background: #eef2ff;
  border-radius: 999px;
  padding: 0.25rem 0.9rem;
  border: 1px solid rgba(79, 70, 229, 0.2);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.account-chip:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(79, 70, 229, 0.15);
}

.account-chip .avatar {
  width: 32px;
  height: 32px;
  border-radius: 999px;
  background: linear-gradient(135deg, #4f46e5, #2563eb);
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
  color: #1f2937;
}

.account-chip .label small {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748b;
}

.account-chip .label strong {
  font-size: 0.9rem;
}

.logout-btn {
  border: none;
  background: transparent;
  color: #dc2626;
  font-weight: 600;
  cursor: pointer;
  padding: 0.4rem 0.6rem;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.logout-btn:hover {
  background: rgba(220, 38, 38, 0.1);
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
