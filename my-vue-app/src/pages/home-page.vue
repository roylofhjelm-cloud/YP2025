<template>
  <div class="home">
    <div v-if="progress" class="profile-block">
      <div class="progress-top">
        <div>
          <p class="eyebrow">Din nivå</p>
          <h2>Level {{ levelNumber }}</h2>
          <p class="muted">XP: {{ userXp }}</p>
          <div class="xp-bar">
            <div class="fill" :style="{ width: xpPercent + '%' }"></div>
          </div>
          <small class="muted" v-if="progress.next_level_xp">Nästa nivå vid {{ progress.next_level_xp }} XP</small>
          <small class="muted" v-else>Max nivå</small>
        </div>
        <div class="stat-card">
          <p>Övningar klara</p>
          <strong>{{ progress.stats.total }}</strong>
        </div>
        <div class="stat-card">
          <p>Snittpoäng</p>
          <strong>{{ Math.round(progress.stats.average_score || 0) }}%</strong>
        </div>
      </div>

      <div class="recent">
        <h3>Senaste resultat</h3>
        <div v-if="progress.results && progress.results.length">
          <div
            v-for="r in progress.results"
            :key="r.Result_Id || r.Exercise_Id"
            class="recent-row"
          >
            <div>
              <p class="title">{{ r.Title }}</p>
              <small>{{ formatType(r.Type) || 'Övning' }}</small>
            </div>
            <div class="badge" :class="badgeClass(calculatePercent(r))">
              {{ calculatePercent(r) }}%
            </div>
          </div>
        </div>
        <p v-else class="muted">Inga resultat ännu.</p>
      </div>
    </div>

    <h1 class="page-title">Lärportalen</h1>
    <p class="subtitle">Välj en övning för att börja träna.</p>

    <div v-if="exercises.length === 0" class="no-data">
      Inga övningar tillgängliga just nu.
    </div>

    <div v-else class="exercise-grid">
      <router-link
        v-for="exercise in exercises"
        :key="exercise.Exercise_Id"
        :to="`/exercise/${exercise.Exercise_Id}`"
        class="exercise-card"
      >
        <div class="card-header">
          <div class="icon">📘</div>
          <h2>{{ exercise.Title }}</h2>
        </div>
        <p class="desc">{{ exercise.Description }}</p>

        <div
          v-if="exerciseStatus[exercise.Exercise_Id]"
          class="result-badge"
          :class="exerciseStatus[exercise.Exercise_Id].failed ? 'failed' : 'passed'"
        >
          <span class="label">
            {{
              exerciseStatus[exercise.Exercise_Id].failed
                ? "Failed"
                : statusLabel(exerciseStatus[exercise.Exercise_Id])
            }}
          </span>
          <span class="percent">
            {{ exerciseStatus[exercise.Exercise_Id].percent }}%
          </span>
        </div>
        <div class="type-tag">{{ formatType(exercise.Type) }}</div>
      </router-link>
    </div>
  </div>
</template>

<script>
import { API_BASE } from "@/apiConfig";

export default {
  name: "HomePage",
  data() {
    return {
      user: null,
      progress: null,
      exercises: [],
    };
  },
  computed: {
    levelNumber() {
      return this.progress?.level?.id || 1;
    },
    userXp() {
      return this.progress?.xp ?? 0;
    },
    xpPercent() {
      const next = this.progress?.next_level_xp;
      if (!next) return 100;
      return Math.min(100, Math.round((this.userXp / next) * 100));
    },
    exerciseStatus() {
      if (!this.progress || !Array.isArray(this.progress.results)) return {};

      const map = {};
      this.progress.results.forEach((result) => {
        const id = result.Exercise_Id;
        if (map[id]) return;

        const percent = this.calculatePercent(result);
        map[id] = this.buildStatus(percent);
      });

      return map;
    },
  },
  async mounted() {
    const student = localStorage.getItem("student_id");
    const admin = localStorage.getItem("admin_id");

    if (student) {
      this.user = { id: student, role: "student" };
    } else if (admin) {
      this.user = { id: admin, role: "admin" };
    } else {
      this.$router.push("/login");
      return;
    }

    try {
      const ex = await fetch(`${API_BASE}/exercises.php`);
      const data = await ex.json();
      this.exercises = Array.isArray(data) ? data : data.exercises || [];
    } catch (err) {
      console.error("Error fetching exercises:", err);
      this.exercises = [];
    }

    try {
      const res = await fetch(`${API_BASE}/user_progress.php?user_id=${this.user.id}`);
      this.progress = await res.json();
    } catch (err) {
      console.error("Error fetching progress:", err);
      this.progress = null;
    }
  },
  methods: {
    formatType(type) {
      const map = {
        true_false: "Sant eller falskt",
        mcq: "Flervalsfrågor",
        ordering: "Ordning",
        match: "Para ihop",
        fill_blank: "Textluckor",
      };
      return map[type] || type;
    },
    calculatePercent(result) {
      if (result && result.Percent !== undefined && result.Percent !== null) {
        return Math.round(Number(result.Percent));
      }

      const total = Number(
        result?.Total_Questions ?? result?.total_questions ?? 0
      );
      if (total > 0) {
        return Math.round((Number(result.Score || 0) / total) * 100);
      }
      return Math.round(Number(result?.Score || 0));
    },
    badgeClass(p) {
      if (p >= 70) return "badge pass";
      if (p >= 50) return "badge warn";
      return "badge fail";
    },
    buildStatus(percent) {
      if (percent >= 95) return { percent, stars: 3, failed: false };
      if (percent >= 80) return { percent, stars: 2, failed: false };
      if (percent >= 60) return { percent, stars: 1, failed: false };
      return { percent, stars: 0, failed: true };
    },
    statusLabel(status) {
      if (!status) return "";
      if (status.failed) return "Failed";
      const starWord = status.stars === 1 ? "star" : "stars";
      return `${"⭐".repeat(status.stars)} ${status.stars} ${starWord}`;
    },
  },
};
</script>

<style scoped>
.home {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 0 1rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 0.3rem;
}

.subtitle {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.profile-block {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  box-shadow: var(--shadow-soft);
  margin-bottom: 1.5rem;
  display: grid;
  gap: 1rem;
}
.progress-top {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.8rem;
  align-items: center;
}
.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.25em;
  font-size: 0.7rem;
  color: var(--text-muted);
  margin: 0 0 0.35rem;
}
.muted {
  color: var(--text-muted);
}
.xp-bar {
  width: 100%;
  height: 10px;
  border-radius: 999px;
  background: var(--border);
  margin: 0.4rem 0 0.3rem;
  overflow: hidden;
}
.xp-bar .fill {
  height: 100%;
  background: var(--primary-gradient);
  border-radius: 999px;
}
.stat-card {
  background: var(--surface-alt);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 0.8rem 1rem;
  text-align: center;
  box-shadow: var(--shadow-soft);
}
.stat-card p {
  margin: 0;
  color: var(--text-muted);
}
.stat-card strong {
  font-size: 1.4rem;
}
.recent {
  border-top: 1px solid var(--border);
  padding-top: 1rem;
}
.recent h3 {
  margin: 0 0 0.5rem;
}
.recent-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.4rem 0;
  border-bottom: 1px solid var(--border);
}
.recent-row:last-child {
  border-bottom: none;
}
.recent-row .title {
  margin: 0;
  font-weight: 600;
}
.badge {
  padding: 0.3rem 0.7rem;
  border-radius: 10px;
  font-weight: 700;
  color: white;
}
.badge.pass {
  background: linear-gradient(135deg, #16a34a, #22c55e);
}
.badge.warn {
  background: linear-gradient(135deg, #f59e0b, #f97316);
}
.badge.fail {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.loading,
.no-data {
  color: var(--text-muted);
  text-align: center;
  margin-top: 4rem;
  font-style: italic;
}

.exercise-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.exercise-card {
  background: var(--surface);
  padding: 1.25rem 1.5rem;
  border-radius: 14px;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
  text-decoration: none;
  color: var(--text);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.exercise-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.card-header .icon {
  font-size: 1.8rem;
}

.desc {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-bottom: 1rem;
  line-height: 1.4;
}

.type-tag {
  align-self: flex-start;
  background: var(--tag-bg);
  color: var(--tag-text);
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
}

.result-badge {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.65rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 0.6rem;
}

.result-badge .percent {
  font-size: 0.75rem;
  font-weight: 500;
}

.result-badge.passed {
  background: var(--success-bg);
  color: var(--success-text);
}

.result-badge.failed {
  background: var(--error-bg);
  color: var(--error-text);
}

.profile-block {
  background: var(--surface-alt);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  margin-bottom: 2rem;
}

.profile-block h2 {
  margin-top: 0;
  margin-bottom: 0.75rem;
}

.profile-block ul {
  margin: 0.75rem 0 0;
  padding-left: 1.1rem;
}
</style>
