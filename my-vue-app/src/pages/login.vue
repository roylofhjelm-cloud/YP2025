<template>
  <div class="login-page">
    <h1>Admin login</h1>

    <form @submit.prevent="login">
      <input
        v-model="username"
        placeholder="Username"
        autocomplete="off"
      />

      <input
        v-model="password"
        type="password"
        placeholder="Password"
      />

      <button>Login</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script>
export default {
  name: "AdminLoginPage",
  data() {
    return {
      username: "",
      password: "",
      error: null,
    };
  },
  methods: {
    async login() {
      this.error = null;
      try {
        localStorage.clear();

        const res = await fetch("http://localhost/larportalen2025/api/admin.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "login",
            username: this.username,
            password: this.password
          })
        });

        const data = await res.json();

        if (data.success) {
          const adminId = data.user?.u_id ?? "admin";
          const adminName = data.user?.username || "Admin";
          localStorage.setItem("admin_id", adminId);
          localStorage.setItem("admin_name", adminName);
          this.$router.push("/admin");
        } else {
          this.error = "Wrong admin login";
        }
      } catch (err) {
        console.error(err);
        this.error = "Server error";
      }
    }
  }
};
</script>

<style scoped>
.login-page {
  max-width: 400px;
  margin: 3rem auto;
  background: var(--surface);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
}
input, button {
  display: block;
  width: 100%;
  margin-bottom: 1rem;
  padding: 0.6rem;
}
button {
  background: var(--primary);
  border: none;
  color: white;
  border-radius: 8px;
}
.error {
  color: var(--error-text);
}
</style>
