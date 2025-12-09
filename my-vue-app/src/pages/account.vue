<template>
  <div class="account-page" v-if="profile">
    <header class="page-header">
      <div>
        <p class="eyebrow">Din profil</p>
        <h1>👤 {{ profile.user.username }}</h1>
        <p class="muted">Student</p>
      </div>
      <button @click="goHome" class="btn ghost">⬅️ Tillbaka</button>
    </header>

    <div class="grid">
      <div class="card highlight">
        <div class="level-row">
          <div>
            <p class="label">Level</p>
            <h2>{{ levelNumber }}</h2>
          </div>
          <div class="pill">XP: {{ profile.user.xp }}</div>
        </div>

        <div class="xp-bar">
          <div class="fill" :style="{ width: xpPercent + '%' }"></div>
        </div>
        <small v-if="profile.next_level_xp" class="muted">
          Nästa nivå vid {{ profile.next_level_xp }} XP
        </small>
        <small v-else class="muted">🥳 Max nivå uppnådd</small>
      </div>

      <div class="card stats">
        <h3>Dina siffror</h3>
        <div class="stat-row">
          <span>Klarmarkerade övningar</span>
          <strong>{{ profile.stats.completed_exercises }}</strong>
        </div>
        <div class="stat-row">
          <span>Snittpoäng</span>
          <strong>{{ profile.stats.average_score }}%</strong>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="loading">Loading...</div>
</template>

<script>
import { API_BASE } from "@/apiConfig";

export default {
  // Account page: shows profile XP/level and basic stats for the logged-in user.
  name: "AccountPage",
  data() {
    return { profile: null };
  },
  computed: {
    // Current level number derived from profile payload
    levelNumber() {
      return this.profile?.level?.level_id ?? 1;
    },
    // Progress bar fill based on current XP vs next level
    xpPercent() {
      if (!this.profile.next_level_xp) return 100;
      const xp = this.profile.user.xp;
      return Math.min(100, (xp / this.profile.next_level_xp) * 100);
    }
  },
  async mounted() {
    // Fetch profile + stats for whichever role is logged in
    const uid = localStorage.getItem("student_id") || localStorage.getItem("admin_id");
    if (!uid) return;
    const res = await fetch(`${API_BASE}/get_user_stats.php?user_id=${uid}`, {
      credentials: "include",
    });
    this.profile = await res.json();
  },
  methods: {
    // Send user back to the home/dashboard
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
}
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}
.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.7rem;
  color: var(--text-muted);
  margin: 0 0 0.25rem;
}
.muted {
  color: var(--text-muted);
}
.grid {
  display: grid;
  gap: 1rem;
}
.card {
  background: var(--surface);
  padding: 1.5rem 2rem;
  margin-bottom: 1rem;
  border-radius: 14px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-soft);
}
.card.highlight {
  background: var(--surface-alt);
  border-color: var(--border);
}
.xp-bar {
  width: 100%;
  height: 12px;
  border-radius: 8px;
  background: var(--border);
  margin-top: 8px;
}
.fill {
  height: 100%;
  background: var(--primary-gradient);
  border-radius: 8px;
}
.level-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.6rem;
}
.level-row .label {
  margin: 0;
  color: var(--text-muted);
  font-weight: 600;
}
.pill {
  background: var(--accent);
  color: var(--primary-strong);
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  font-weight: 700;
}
.stats h3 {
  margin-top: 0;
  margin-bottom: 0.6rem;
}
.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.45rem 0;
}
.stat-row span {
  color: var(--text-muted);
}
.stat-row strong {
  font-size: 1.1rem;
}
.btn {
  padding: 0.75rem 1.35rem;
  background: var(--primary);
  color: #fff;
  border-radius: 12px;
  border: none;
  cursor: pointer;
}
.btn.ghost {
  background: var(--surface-alt);
  color: var(--text);
  border: 1px solid var(--border);
}
.loading {
  text-align: center;
  color: var(--text-muted);
}
</style>
