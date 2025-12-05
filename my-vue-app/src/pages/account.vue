<template>
  <div class="account-page" v-if="profile">
    <h1>👤 {{ profile.user.username }}</h1>

    <div class="card">
      <p><strong>Role:</strong> Student</p>
      <p><strong>XP:</strong> {{ profile.user.xp }}</p>

      <p><strong>Level:</strong> {{ profile.level.level_name }}</p>

      <div class="xp-bar">
        <div class="fill" :style="{ width: xpPercent + '%' }"></div>
      </div>

      <small v-if="profile.next_level_xp">
        Next level at {{ profile.next_level_xp }} XP
      </small>
      <small v-else>🥳 Max level reached!</small>
    </div>

    <div class="card stats">
      <p><strong>Completed Exercises:</strong> {{ profile.stats.completed_exercises }}</p>
      <p><strong>Average Score:</strong> {{ profile.stats.average_score }}%</p>
    </div>

    <button @click="goHome" class="btn">⬅️ Back</button>
  </div>

  <div v-else class="loading">Loading...</div>
</template>

<script>
export default {
  data() {
    return { profile: null };
  },
  computed: {
    xpPercent() {
      if (!this.profile.next_level_xp) return 100;
      const xp = this.profile.user.xp;
      return Math.min(100, (xp / this.profile.next_level_xp) * 100);
    }
  },
  async mounted() {
    const uid = localStorage.getItem("student_id") || localStorage.getItem("admin_id");
    if (!uid) return;
    const res = await fetch(`http://localhost/larportalen2025/api/get_user_stats.php?user_id=${uid}`);
    this.profile = await res.json();
  },
  methods: {
    goHome() {
      this.$router.push("/home");
    }
  }
};
</script>

<style scoped>
.account-page {
  max-width: 600px;
  margin: 2rem auto;
  text-align: left;
}
.card {
  background: #fff;
  padding: 1.5rem 2rem;
  margin-bottom: 1rem;
  border-radius: 14px;
  border: 1px solid #ccc;
}
.xp-bar {
  width: 100%;
  height: 12px;
  border-radius: 8px;
  background: #eee;
  margin-top: 8px;
}
.fill {
  height: 100%;
  background: #34d399;
  border-radius: 8px;
}
.stats p {
  margin: 0.25rem 0;
}
.btn {
  padding: 0.85rem 1.5rem;
  background: #2563eb;
  color: white;
  border-radius: 12px;
  border: none;
  cursor: pointer;
}
.loading {
  text-align: center;
  color: #777;
}
</style>
