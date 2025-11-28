<template>
  <section class="account-page">
    <div class="card" v-if="session.role">
      <p class="eyebrow">Aktivt konto</p>
      <h1>{{ session.name }}</h1>
      <ul>
        <li><strong>Roll:</strong> {{ roleLabel }}</li>
        <li><strong>ID:</strong> {{ session.id }}</li>
      </ul>

      <div
        v-if="session.role === 'student'"
        class="results"
      >
        <h3>Senaste resultat</h3>
        <div v-if="results && results.length">
          <article
            v-for="result in results"
            :key="result.Result_Id"
            class="result-row"
          >
            <div>
              <p class="title">{{ result.Title }}</p>
              <small>{{ formatType(result.Type) }}</small>
            </div>
            <div class="score">
              <strong>{{ result.Score }}</strong>
              <small v-if="result.Completed">klar</small>
            </div>
          </article>
        </div>
        <p v-else class="empty-state">Inga resultat sparade ännu.</p>
      </div>

      <div class="actions">
        <router-link
          v-if="session.role === 'student'"
          to="/home"
          class="btn secondary"
        >
          Studentvy
        </router-link>
        <router-link
          v-if="session.role === 'admin'"
          to="/admin"
          class="btn secondary"
        >
          Adminpanel
        </router-link>
        <button class="btn" @click="logout">
          Logga ut
        </button>
      </div>
    </div>

    <div class="card empty" v-else>
      <h2>Ingen inloggad användare</h2>
      <p>Logga in som student eller admin för att visa kontodetaljer.</p>
      <div class="actions">
        <router-link to="/login-student" class="btn secondary">Student</router-link>
        <router-link to="/login" class="btn">Admin</router-link>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: "AccountPage",
  data() {
    return {
      session: {
        role: null,
        name: "",
        id: null,
      },
      results: [],
    };
  },
  computed: {
    roleLabel() {
      if (this.session.role === "admin") return "Admin";
      if (this.session.role === "student") return "Student";
      return "";
    },
  },
  mounted() {
    this.refreshSession();
    window.addEventListener("storage", this.refreshSession);
  },
  beforeUnmount() {
    window.removeEventListener("storage", this.refreshSession);
  },
  methods: {
    async refreshSession() {
      const adminId = localStorage.getItem("admin_id");
      const studentId = localStorage.getItem("student_id");

      if (adminId) {
        this.session = {
          role: "admin",
          name: localStorage.getItem("admin_name") || "Admin",
          id: adminId,
        };
        this.results = [];
      } else if (studentId) {
        this.session = {
          role: "student",
          name: localStorage.getItem("student_name") || "Student",
          id: studentId,
        };
        await this.loadResults(studentId);
      } else {
        this.session = {
          role: null,
          name: "",
          id: null,
        };
        this.results = [];
      }
    },
    async loadResults(id) {
      const userId = id || localStorage.getItem("student_id") || localStorage.getItem("user_id");
      if (!userId) {
        this.results = [];
        return;
      }

      try {
        const res = await fetch(`http://localhost/larportalen2025/api/get_user_results.php?user_id=${userId}`);
        this.results = await res.json();
      } catch (err) {
        console.error("Failed loading results", err);
        this.results = [];
      }
    },
    formatType(type) {
      const labels = {
        mcq: "Flerval",
        true_false: "Sant/Falskt",
        ordering: "Ordning",
        match: "Para ihop",
        fill_blank: "Textluckor",
      };
      return labels[type] || type;
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
      }
      this.refreshSession();
    },
  },
};
</script>

<style scoped>
.account-page {
  max-width: 640px;
  margin: 2.5rem auto;
  padding: 0 1rem;
}
.card {
  background: #fff;
  border-radius: 18px;
  padding: 2rem;
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.08);
  border: 1px solid rgba(148, 163, 184, 0.25);
}
.card.empty {
  text-align: center;
}
.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.3em;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.8rem;
}
h1 {
  margin: 0 0 1.25rem;
  font-size: 2rem;
  color: #1f2a37;
}
ul {
  list-style: none;
  padding: 0;
  margin: 0 0 1.75rem;
  display: grid;
  gap: 0.4rem;
}
li strong {
  color: #475569;
  margin-right: 0.4rem;
}
.actions {
  display: flex;
  gap: 0.8rem;
  flex-wrap: wrap;
}
.results {
  margin-bottom: 1.5rem;
  border-top: 1px solid rgba(148, 163, 184, 0.25);
  padding-top: 1.5rem;
}
.results h3 {
  margin: 0 0 0.8rem;
}
.result-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem 0;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}
.result-row:last-child {
  border-bottom: none;
}
.result-row .title {
  margin: 0;
  font-weight: 600;
}
.result-row small {
  color: #64748b;
}
.result-row .score {
  text-align: right;
}
.empty-state {
  color: #94a3b8;
  font-style: italic;
}
.btn {
  border: none;
  cursor: pointer;
  border-radius: 12px;
  padding: 0.7rem 1.4rem;
  font-weight: 600;
  background: linear-gradient(135deg, #4f46e5, #2563eb);
  color: white;
  text-decoration: none;
  text-align: center;
  flex: none;
}
.btn.secondary {
  background: rgba(79, 70, 229, 0.12);
  color: #312e81;
}
.btn.secondary:hover {
  background: rgba(79, 70, 229, 0.25);
}
.btn:hover {
  opacity: 0.95;
}
</style>
