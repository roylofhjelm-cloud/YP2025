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
              <div class="user-actions">
                <button class="text-btn" @click="startEdit(u)">Redigera</button>
                <button class="text-btn danger" @click="deleteUser(u)">Ta bort</button>
              </div>
            </li>
          </ul>

          <div v-if="editingUser" class="edit-panel">
            <h4>Redigera {{ editingUser.username }}</h4>
            <label>
              Användarnamn
              <input v-model="editingUser.username" />
            </label>
            <label>
              E-post
              <input v-model="editingUser.email" type="email" />
            </label>
            <label>
              Nytt lösenord (valfritt)
              <input v-model="editingUser.password" type="password" />
            </label>
            <label>
              Roll
              <select v-model="editingUser.role">
                <option value="student">Student</option>
                <option value="teacher">Lärare</option>
                <option value="admin">Admin</option>
              </select>
            </label>
            <div class="edit-actions">
              <button class="btn" @click="saveEdit">Spara</button>
              <button class="btn secondary" type="button" @click="cancelEdit">Avbryt</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- EXERCISES TAB -->
    <section v-else class="content">
      <div class="two-col">
        <div class="card">
          <div class="card-header">
            <div>
              <h3>Övningar</h3>
              <p class="hint">Skapa nytt eller redigera befintliga.</p>
            </div>
            <button class="text-btn" @click="loadExercises">↻ Uppdatera</button>
          </div>

          <div class="list" v-if="loadingExercises">Laddar övningar...</div>
          <div class="list" v-else>
            <div v-for="ex in exercises" :key="ex.Exercise_Id" class="list-row">
              <div>
                <strong>{{ ex.Title || 'Namnlös' }}</strong>
                <div class="muted">{{ ex.Type }}</div>
              </div>
              <div class="row-actions">
                <button class="text-btn" @click="startEditExercise(ex)">Redigera</button>
                <button class="text-btn danger" @click="deleteExercise(ex)">Ta bort</button>
              </div>
            </div>
            <p v-if="exercises.length === 0" class="muted">Inga övningar hittades.</p>
          </div>

          <div v-if="editExercise" class="edit-panel">
            <h4>Redigera övning</h4>
            <label>Titel
              <input v-model="editExercise.Title" />
            </label>
            <label>Beskrivning
              <textarea v-model="editExercise.Description"></textarea>
            </label>
            <label>Typ
              <select v-model="editExercise.Type">
                <option value="mcq">mcq</option>
                <option value="true_false">true_false</option>
                <option value="match">match</option>
                <option value="ordering">ordering</option>
                <option value="fill_blank">fill_blank</option>
                <option value="mixed">mixed</option>
              </select>
            </label>
            <div class="edit-actions">
              <button class="btn" @click="saveExerciseEdit">Spara</button>
              <button class="btn secondary" type="button" @click="cancelExerciseEdit">Avbryt</button>
            </div>

            <h4>Frågor</h4>
            <div
              v-for="(q, idx) in editExerciseQuestions"
              :key="idx"
              class="question-block"
            >
              <div class="question-row">
                <select v-model="q.type">
                  <option value="mcq">Flerval</option>
                  <option value="true_false">Sant/Falskt</option>
                  <option value="ordering">Ordning</option>
                  <option value="match">Para ihop</option>
                  <option value="fill_blank">Textluckor</option>
                </select>
                <button class="text-btn danger" type="button" @click="removeEditQuestion(idx)">🗑️</button>
              </div>
              <component
                :is="getEditor(q.type)"
                v-model="editExerciseQuestions[idx].data"
              />
            </div>
            <button class="btn secondary" type="button" @click="addEditQuestion">+ Lägg till fråga</button>
          </div>

          <div class="divider"></div>
          <h4>Skapa ny övning</h4>
          <AddExercise />
        </div>

        <div class="card">
          <div class="card-header">
            <div>
              <h3>Läsmaterial</h3>
              <p class="hint">Hantera artiklar.</p>
            </div>
            <button class="text-btn" @click="loadMaterials">↻ Uppdatera</button>
            <router-link class="btn" to="/add-material">+ Lägg till</router-link>
          </div>

          <div class="list" v-if="loadingMaterials">Laddar läsmaterial...</div>
          <div class="list" v-else>
            <div v-for="m in materials" :key="m.Material_Id" class="list-row">
              <div>
                <strong>{{ m.Title }}</strong>
                <div class="muted">{{ m.Created_At }}</div>
              </div>
              <div class="row-actions">
                <button class="text-btn" @click="startEditMaterial(m)">Redigera</button>
                <button class="text-btn danger" @click="deleteMaterial(m)">Ta bort</button>
              </div>
            </div>
            <p v-if="materials.length === 0" class="muted">Inga läsmaterial hittades.</p>
          </div>

          <div v-if="editMaterial" class="edit-panel">
            <h4>Redigera material</h4>
            <label>Titel
              <input v-model="editMaterial.Title" />
            </label>
            <label>Innehåll
              <textarea v-model="editMaterial.Content" rows="4"></textarea>
            </label>
            <div class="edit-actions">
              <button class="btn" @click="saveMaterialEdit">Spara</button>
              <button class="btn secondary" type="button" @click="cancelMaterialEdit">Avbryt</button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import AddExercise from "./add-exercise.vue";
import MCQEditor from "@/components/exercises/editors/MCQEditor.vue";
import TrueFalseEditor from "@/components/exercises/editors/TrueFalseEditor.vue";
import OrderingEditor from "@/components/exercises/editors/OrderingEditor.vue";
import MatchEditor from "@/components/exercises/editors/MatchEditor.vue";
import FillBlankEditor from "@/components/exercises/editors/FillBlankEditor.vue";
import { API_BASE } from "@/apiConfig";

export default {
  name: "AdminPage",
  components: { AddExercise, MCQEditor, TrueFalseEditor, OrderingEditor, MatchEditor, FillBlankEditor },

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
      editingUser: null,
      exercises: [],
      materials: [],
      loadingExercises: false,
      loadingMaterials: false,
      editExercise: null,
      editMaterial: null,
      editExerciseQuestions: [],
      apiBase: API_BASE,
    };
  },

  mounted() {
    this.loadUsers();
    this.loadExercises();
    this.loadMaterials();
  },

  methods: {
    csrfToken() {
      return localStorage.getItem("csrf_token") || "";
    },
    async loadUsers() {
      this.loadingUsers = true;
      this.formError = "";
      try {
        const res = await fetch(`${this.apiBase}/admin.php`, {
          method: "POST",
          credentials: "include",
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
        csrf_token: this.csrfToken(),
        role: (() => {
          const map = { student: 1, teacher: 2, admin: 3 };
          const r = this.newUser.role;
          if (typeof r === "string" && map[r]) return map[r];
          const num = Number(r);
          return [1, 2, 3].includes(num) ? num : 1;
        })(), // default student
      };

      console.log("📤 Creating user:", payload);

      const res = await fetch(`${this.apiBase}/admin.php`, {
        method: "POST",
        credentials: "include",
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

    startEdit(user) {
      const role = user.role_id || user.role_name || "student";
      const roleMap = { 1: "student", 2: "teacher", 3: "admin", Student: "student", Admin: "admin" };
      this.editingUser = {
        id: user.u_id,
        username: user.username,
        email: user.email || "",
        password: "",
        role: roleMap[role] || "student",
      };
    },

    cancelEdit() {
      this.editingUser = null;
    },

    async saveEdit() {
      if (!this.editingUser) return;
      const payload = {
        action: "update_user",
        id: this.editingUser.id,
        username: this.editingUser.username,
        email: this.editingUser.email,
        csrf_token: this.csrfToken(),
        role: (() => {
          const map = { student: 1, teacher: 2, admin: 3 };
          const r = this.editingUser.role;
          if (typeof r === "string" && map[r]) return map[r];
          const num = Number(r);
          return [1, 2, 3].includes(num) ? num : 1;
        })(),
      };
      if (this.editingUser.password) {
        payload.password = this.editingUser.password;
      }

      const res = await fetch(`${this.apiBase}/admin.php`, {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        alert("✔️ Användare uppdaterad");
        this.editingUser = null;
        this.loadUsers();
      } else {
        alert("❌ " + (data.message || "Fel vid uppdatering"));
      }
    },

    async deleteUser(user) {
      const id = user.u_id;
      if (!id) return;
      if (!confirm(`Ta bort ${user.username}?`)) return;

      const res = await fetch(`${this.apiBase}/admin.php`, {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "delete_user", id, csrf_token: this.csrfToken() }),
      });
      const data = await res.json();
      if (data.success || data.deleted) {
        this.loadUsers();
      } else {
        alert("❌ Kunde inte ta bort användare");
      }
    },

    getEditor(type) {
      return {
        mcq: "MCQEditor",
        true_false: "TrueFalseEditor",
        ordering: "OrderingEditor",
        match: "MatchEditor",
        fill_blank: "FillBlankEditor",
      }[type] || "div";
    },

    normalizeType(t) {
      const map = {
        mcq: "mcq",
        true_false: "true_false",
        ordering: "ordering",
        match: "match",
        fill_blank: "fill_blank",
        "true-false": "true_false",
        trueFalse: "true_false",
        order: "ordering",
        "fill-blanks": "fill_blank",
        fillBlank: "fill_blank",
        "match-pairs": "match",
        pairs: "match",
      };
      return map[t] ?? "mcq";
    },

    addEditQuestion() {
      this.editExerciseQuestions.push({ type: "mcq", data: {} });
    },
    removeEditQuestion(idx) {
      this.editExerciseQuestions.splice(idx, 1);
    },

    async loadExercises() {
      this.loadingExercises = true;
      try {
        const res = await fetch(`${this.apiBase}/exercises.php`, { credentials: "include" });
        const data = await res.json();
        this.exercises = data.exercises || [];
      } catch (err) {
        console.error("Error loading exercises", err);
      } finally {
        this.loadingExercises = false;
      }
    },

    async startEditExercise(ex) {
      this.editExercise = null;
      this.editExerciseQuestions = [];
      try {
        const res = await fetch(`${this.apiBase}/exercise.php?id=${ex.Exercise_Id}`);
        const data = await res.json();
        if (data.exercise) {
          this.editExercise = { ...data.exercise };
          this.editExerciseQuestions = (data.questions || []).map((q) => ({
            type: this.normalizeType(q.Question_Type || q.type || q.Data?.type),
            data: q.Data?.data || q.Data || q.data || {},
          }));
        }
      } catch (err) {
        console.error("Error loading exercise", err);
      }
    },
    cancelExerciseEdit() {
      this.editExercise = null;
      this.editExerciseQuestions = [];
    },
    async saveExerciseEdit() {
      if (!this.editExercise?.Exercise_Id) return;
      const res = await fetch(`${this.apiBase}/exercises.php`, {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": this.csrfToken() },
        body: JSON.stringify({
          Exercise_Id: this.editExercise.Exercise_Id,
          Title: this.editExercise.Title,
          Description: this.editExercise.Description,
          Type: this.editExercise.Type,
          csrf_token: this.csrfToken(),
          Data: {
            questions: this.editExerciseQuestions.map((q) => ({
              type: this.normalizeType(q.type),
              data: q.data,
            })),
          },
        }),
      });
      const data = await res.json();
      if (data.success) {
        this.editExercise = null;
        this.editExerciseQuestions = [];
        this.loadExercises();
      } else {
        alert("❌ Kunde inte spara övning");
      }
    },
    async deleteExercise(ex) {
      if (!confirm(`Ta bort övning "${ex.Title || ex.Exercise_Id}"?`)) return;
      const res = await fetch(`${this.apiBase}/exercises.php`, {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": this.csrfToken() },
        body: JSON.stringify({ action: "delete", Exercise_Id: ex.Exercise_Id, csrf_token: this.csrfToken() }),
      });
      const data = await res.json();
      if (data.success) {
        this.loadExercises();
      } else {
        alert("❌ Kunde inte ta bort övning");
      }
    },

    async loadMaterials() {
      this.loadingMaterials = true;
      try {
        const res = await fetch(`${this.apiBase}/materials.php`, { credentials: "include" });
        const data = await res.json();
        this.materials = Array.isArray(data) ? data : data.materials || [];
      } catch (err) {
        console.error("Error loading materials", err);
      } finally {
        this.loadingMaterials = false;
      }
    },
    startEditMaterial(m) {
      this.editMaterial = { ...m };
    },
    cancelMaterialEdit() {
      this.editMaterial = null;
    },
    async saveMaterialEdit() {
      if (!this.editMaterial?.Material_Id) return;
      const res = await fetch(`${this.apiBase}/materials.php`, {
        method: "PUT",
        credentials: "include",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": this.csrfToken() },
        body: JSON.stringify({
          Material_Id: this.editMaterial.Material_Id,
          Title: this.editMaterial.Title,
          Content: this.editMaterial.Content,
          csrf_token: this.csrfToken(),
        }),
      });
      const data = await res.json();
      if (data.success) {
        this.editMaterial = null;
        this.loadMaterials();
      } else {
        alert("❌ Kunde inte spara material");
      }
    },
    async deleteMaterial(m) {
      if (!confirm(`Ta bort material "${m.Title}"?`)) return;
      const res = await fetch(`${this.apiBase}/materials.php`, {
        method: "POST",
        credentials: "include",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": this.csrfToken() },
        body: JSON.stringify({ action: "delete", Material_Id: m.Material_Id, csrf_token: this.csrfToken() }),
      });
      const data = await res.json();
      if (data.success) {
        this.loadMaterials();
      } else {
        alert("❌ Kunde inte ta bort material");
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
  background: var(--surface-alt);
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
  background: var(--surface);
  box-shadow: var(--shadow-soft);
}
.content h2 {
  margin-bottom: 0.75rem;
}
.grid {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.2fr);
  gap: 1.5rem;
}
.two-col {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 1.5rem;
}
.card {
  background: var(--surface);
  border-radius: 16px;
  padding: 1.25rem;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
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
  border: 1px solid var(--border);
  padding: 0.45rem 0.6rem;
  font-size: 0.9rem;
  background: var(--surface-alt);
  color: var(--text);
}
.btn {
  margin-top: 0.5rem;
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 0.75rem;
  background: var(--primary);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}
.btn:hover {
  opacity: 0.92;
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
  border-bottom: 1px solid var(--border);
}
.user-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}
.text-btn {
  border: none;
  background: transparent;
  color: var(--primary);
  cursor: pointer;
  font-weight: 600;
}
.text-btn.danger {
  color: #dc2626;
}
.user-meta {
  font-size: 0.8rem;
  color: var(--text-muted);
}
.list {
  margin-top: 0.5rem;
}
.list-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border);
}
.list-row:last-child {
  border-bottom: none;
}
.muted {
  color: var(--text-muted);
  font-size: 0.85rem;
}
.row-actions {
  display: flex;
  gap: 0.4rem;
}
.badge {
  display: inline-block;
  margin-left: 0.4rem;
  padding: 0.1rem 0.5rem;
  border-radius: 999px;
  background: var(--accent);
  color: var(--primary-strong);
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
  color: var(--text-muted);
  font-size: 0.9rem;
}
.divider {
  height: 1px;
  background: var(--border);
  margin: 1rem 0;
}
.edit-panel {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
  display: grid;
  gap: 0.6rem;
}
.edit-actions {
  display: flex;
  gap: 0.6rem;
}
.question-block {
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 0.75rem;
  background: var(--surface-alt);
  margin-bottom: 0.6rem;
}
.question-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}
@media (max-width: 800px) {
  .grid {
    grid-template-columns: 1fr;
  }
  .two-col {
    grid-template-columns: 1fr;
  }
}
</style>
