<template>
  <div class="admin-page">
    <h1 class="title">Adminpanel</h1>

    <!-- Tabs -->
    <div class="tabs">
      <button
        class="tab"
        :class="{ active: activeTab === 'users' }"
        @click="activeTab = 'users'"
      >
        👥 Användare
      </button>
      <button
        class="tab"
        :class="{ active: activeTab === 'exercises' }"
        @click="activeTab = 'exercises'"
      >
        📚 Övningar & läsmaterial
      </button>
    </div>

    <!-- USERS TAB -->
    <section v-if="activeTab === 'users'" class="content">
      <h2>Hantera användare</h2>

      <div class="grid">
        <!-- Create user -->
        <form class="card" @submit.prevent="createUser">
          <h3>Skapa ny användare</h3>

          <label>
            Användarnamn
            <input v-model="newUser.username" required />
          </label>

          <label>
            E-post
            <input v-model="newUser.email" type="email" />
          </label>

          <label>
            Lösenord
            <input v-model="newUser.password" type="password" required />
          </label>

          <label>
            Roll
            <select v-model="newUser.role">
              <option value="student">Student</option>
              <option value="admin">Admin</option>
            </select>
          </label>

          <button type="submit" class="btn">➕ Skapa</button>

          <p v-if="formError" class="error">{{ formError }}</p>
          <p v-if="formSuccess" class="success">{{ formSuccess }}</p>
        </form>

        <!-- User list -->
        <div class="card">
          <h3>Befintliga användare</h3>

          <p v-if="loadingUsers">Laddar användare...</p>
          <p v-else-if="users.length === 0">Inga användare hittades.</p>

          <ul v-else class="user-list">
            <li v-for="u in users" :key="u.u_id" class="user-item">
              <div>
                <strong>{{ u.username }}</strong>
                <div class="user-meta">
                  <span>{{ u.email || "ingen e-post" }}</span>
                  <span class="badge">{{ u.role_name || u.role_id }}</span>
                </div>
              </div>
              <div class="user-xp">
                XP: <strong>{{ u.xp ?? 0 }}</strong>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- EXERCISES TAB -->
    <section v-else class="content">
      <h2>Skapa / ändra övningar och läsmaterial</h2>
      <p class="hint">
        Använd formuläret nedan. <strong>Beskrivning</strong> kan fungera som
        läsmaterial/introduktion till uppgiften.
      </p>

      <!-- This is your existing mixed-question builder -->
      <AddExercise />
    </section>
  </div>
</template>

<script>
import AddExercise from "./add-exercise.vue";

export default {
  name: "AdminPage",
  components: { AddExercise },

  data() {
    return {
      activeTab: "users",
      users: [],
      loadingUsers: false,
      formError: "",
      formSuccess: "",
      newUser: {
        username: "",
        email: "",
        password: "",
        role: "student",
      },
      apiBase: "http://localhost/larportalen2025/api",
    };
  },

  mounted() {
    this.loadUsers();
  },

  methods: {
    async loadUsers() {
      this.loadingUsers = true;
      this.formError = "";
      try {
        const res = await fetch("http://localhost/larportalen2025/api/admin.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "users" }),
        });

        const data = await res.json();
        console.log("👥 Loaded users:", data);

        if (data.success) {
          this.users = data.users;
        } else {
          alert("Kunde inte hämta användare");
        }
      } catch (err) {
        console.error("Error loading users", err);
        this.formError = "Kunde inte hämta användare.";
      } finally {
        this.loadingUsers = false;
      }
    },

    async createUser() {
      this.formError = "";
      this.formSuccess = "";

      if (!this.newUser.username || !this.newUser.password) {
        alert("Användarnamn och lösenord krävs");
        return;
      }

      const payload = {
        action: "create_user",
        username: this.newUser.username,
        email: this.newUser.email || null,
        password: this.newUser.password,
        role: (() => {
          const map = { student: 1, teacher: 2, admin: 3 };
          const r = this.newUser.role;
          if (typeof r === "string" && map[r]) return map[r];
          const num = Number(r);
          return [1, 2, 3].includes(num) ? num : 1;
        })(), // default student
      };

      console.log("📤 Creating user:", payload);

      const res = await fetch("http://localhost/larportalen2025/api/admin.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const data = await res.json();
      console.log("📥 Result:", data);

      if (data.success) {
        alert("✔️ Användare skapad!");

        // Reload user list automatically
        this.loadUsers();

        // Clear form
        this.newUser = { username: "", email: "", password: "", role: "student" };
      } else {
        alert("❌ " + (data.message || "Fel vid skapande"));
      }
    },
  },
};
</script>

<style scoped>
.admin-page {
  max-width: 1100px;
  margin: 2rem auto;
  padding: 1.5rem;
}
.title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}
.tabs {
  display: inline-flex;
  background: #e5e7eb;
  border-radius: 999px;
  padding: 0.25rem;
  margin-bottom: 1.5rem;
}
.tab {
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  background: transparent;
  cursor: pointer;
  font-weight: 500;
}
.tab.active {
  background: white;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.2);
}
.content h2 {
  margin-bottom: 0.75rem;
}
.grid {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.2fr);
  gap: 1.5rem;
}
.card {
  background: white;
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: 0 6px 20px rgba(15, 23, 42, 0.07);
}
label {
  display: block;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}
input,
textarea,
select {
  width: 100%;
  margin-top: 0.25rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  padding: 0.45rem 0.6rem;
  font-size: 0.9rem;
}
.btn {
  margin-top: 0.5rem;
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 0.75rem;
  background: #2563eb;
  color: white;
  font-weight: 600;
  cursor: pointer;
}
.btn:hover {
  background: #1d4ed8;
}
.user-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.user-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}
.user-meta {
  font-size: 0.8rem;
  color: #6b7280;
}
.badge {
  display: inline-block;
  margin-left: 0.4rem;
  padding: 0.1rem 0.5rem;
  border-radius: 999px;
  background: #eef2ff;
  color: #4338ca;
  font-size: 0.7rem;
}
.user-xp {
  font-size: 0.9rem;
  align-self: center;
}
.error {
  margin-top: 0.5rem;
  color: #b91c1c;
}
.success {
  margin-top: 0.5rem;
  color: #16a34a;
}
.hint {
  margin-bottom: 1rem;
  color: #6b7280;
  font-size: 0.9rem;
}
@media (max-width: 800px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
