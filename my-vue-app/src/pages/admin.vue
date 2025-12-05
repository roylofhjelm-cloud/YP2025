<template>
  <div class="admin-page">
    <h1>Admin panel</h1>

    <button @click="logout">Logout</button>

    <hr />

    <section class="actions">
      <router-link to="/add" class="btn-link">✨ Create exercise</router-link>
      <router-link to="/add-material" class="btn">
        ➕ Add reading material
      </router-link>
    </section>

    <h2>Create user</h2>
    <form @submit.prevent="createUser">
      <input v-model="newUser.username" placeholder="Username" />
      <input v-model="newUser.password" type="password" placeholder="Password" />

      <select v-model="newUser.role">
        <option value="1">Student</option>
        <option value="2">Teacher</option>
        <option value="3">Admin</option>
      </select>

      <button>Create user</button>
    </form>

    <p v-if="message">{{ message }}</p>
  </div>
</template>

<script>
export default {
  name: "AdminPage",
  data() {
    return {
      newUser: {
        username: "",
        password: "",
        role: 1
      },
      message: null
    };
  },

  mounted() {
    if (!localStorage.getItem("admin_id") && !localStorage.getItem("admin")) {
      this.$router.push("/login");
    }
  },

  methods: {
    logout(){
      localStorage.removeItem("admin_id");
      localStorage.removeItem("admin_name");
      localStorage.removeItem("admin");
      this.$router.push("/login");
    },

    async createUser(){
      const res = await fetch("http://localhost/larportalen2025/api/admin.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "create_user",
          ...this.newUser
        })
      });

      const data = await res.json();
      this.message = data.message;

      this.newUser = { username:"", password:"", role:1 };
    }
  }
};
</script>

<style scoped>
.admin-page{
  max-width:600px;
  margin:2rem auto;
  background:var(--surface);
  padding:2rem;
  border-radius:12px;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
}
input,select,button{ 
  display:block; 
  width:100%; 
  margin-bottom:1rem;
}
.actions {
  margin: 1rem 0 2rem;
}
.btn-link {
  display: inline-block;
  padding: 0.7rem 1.2rem;
  border-radius: 10px;
  background: var(--primary-gradient);
  color: white;
  text-decoration: none;
  font-weight: 600;
  box-shadow: 0 12px 24px rgba(79,70,229,0.25);
}
.btn-link:hover {
  opacity: 0.9;
}
.btn {
  display: inline-block;
  padding: 0.7rem 1.2rem;
  border-radius: 10px;
  background: linear-gradient(135deg,#10b981,#059669);
  color: white;
  text-decoration: none;
  font-weight: 600;
  box-shadow: 0 12px 24px rgba(16,185,129,0.25);
  margin-left: 0.75rem;
}
.btn:hover {
  opacity: 0.9;
}
</style>
