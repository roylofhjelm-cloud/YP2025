<template>
  <div class="login-page">
    <h1 v-if="mode === 'login'">Student login</h1>
    <h1 v-else>Registrera student</h1>

    <form @submit.prevent="submit">
      <input v-model="username" placeholder="Användarnamn" />

      <input
        v-if="mode === 'register'"
        v-model="email"
        type="email"
        placeholder="E-post (valfritt)"
      />

      <input
        v-model="password"
        type="password"
        placeholder="Lösenord"
      />

      <input
        v-if="mode === 'register'"
        v-model="confirm"
        type="password"
        placeholder="Bekräfta lösenord"
      />

      <button>{{ mode === 'login' ? 'Login' : 'Registrera' }}</button>
    </form>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="success" class="success">{{ success }}</p>

    <p class="toggle">
      <button type="button" @click="toggleMode">
        {{ mode === 'login' ? 'Har inget konto? Registrera dig' : 'Har konto? Logga in' }}
      </button>
    </p>
  </div>
</template>

<script>
import { API_BASE } from "@/apiConfig";

export default {
  data(){
    return {
      username:"",
      email:"",
      password:"",
      confirm:"",
      error:null,
      success:null,
      mode: "login"
    }
  },

  methods:{
    toggleMode() {
      this.mode = this.mode === "login" ? "register" : "login";
      this.error = null;
    },
    async submit() {
      if (this.mode === "register") {
        return this.register();
      }
      return this.login();
    },
    async login(){
      try{
        localStorage.clear();

        const res = await fetch(`${API_BASE}/student.php`, {
          method:"POST",
          credentials: "include",
          headers:{ "Content-Type":"application/json" },
          body: JSON.stringify({
            action:"login",
            username: this.username,
            password: this.password
          })
        })

        const data = await res.json()

        if(data.success){
          localStorage.setItem("student_id", data.user.u_id)
          localStorage.setItem("student_name", data.user.username || "Student")
          if (data.csrf_token) localStorage.setItem("csrf_token", data.csrf_token);
          this.$router.push("/home")
        } else {
          this.error = "Wrong student login"
        }

      } catch(e){
        console.error(e)
        this.error = "Serverfel";
      }
    },
    async register() {
      this.error = null;
      this.success = null;
      if (!this.username || !this.password) {
        this.error = "Användarnamn och lösenord krävs";
        return;
      }
      if (this.password.length < 3) {
        this.error = "Lösenord måste vara minst 3 tecken";
        return;
      }
      if (this.password !== this.confirm) {
        this.error = "Lösenorden matchar inte";
        return;
      }

      try {
        const res = await fetch(`${API_BASE}/register.php`, {
          method: "POST",
          credentials: "include",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            username: this.username,
            email: this.email,
            password: this.password,
          }),
        });
        const data = await res.json();
        if (data.success) {
          localStorage.clear();
          this.mode = "login";
          this.success = "Konto skapat! Logga in med dina uppgifter.";
          this.password = "";
          this.confirm = "";
        } else {
          this.error = data.error || "Kunde inte registrera.";
        }
      } catch (e) {
        console.error(e);
        this.error = "Serverfel vid registrering";
      }
    }
  }
}
</script>

<style scoped>
.login-page{
  max-width:400px;
  margin:3rem auto;
  background:var(--surface);
  padding:2rem;
  border-radius:12px;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
}

input,button{
  display:block;
  width:100%;
  margin-bottom:1rem;
  padding:0.6rem;
}

button{
  background:var(--primary-gradient);
  color:white;
  border:0;
  border-radius:8px;
}
.error{color:var(--error-text);}
.success{
  color: var(--success-text);
  background: var(--success-bg);
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: 1px solid var(--success-text);
}
.toggle button{
  background: transparent;
  border: none;
  color: var(--primary);
  cursor: pointer;
  font-weight: 600;
}
</style>
