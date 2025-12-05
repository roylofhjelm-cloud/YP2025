<template>
  <div class="login-page">
    <h1>Student login</h1>

    <form @submit.prevent="login">
      <input v-model="username" placeholder="Username" />

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
  data(){
    return {
      username:"",
      password:"",
      error:null
    }
  },

  methods:{
    async login(){
      try{
        localStorage.clear();

        const res = await fetch("http://localhost/larportalen2025/api/student.php", {
          method:"POST",
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
          this.$router.push("/home")
        } else {
          this.error = "Wrong student login"
        }

      } catch(e){
        console.error(e)
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
</style>
