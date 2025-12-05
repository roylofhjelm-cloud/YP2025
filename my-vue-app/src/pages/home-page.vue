<template>
  <div class="home">
    <div v-if="progress" class="profile-block">
      <h2>📊 Your Progress</h2>

      <p><strong>Level:</strong> {{ progress.level }}</p>
      <p><strong>Exercises completed:</strong> {{ progress.stats.total }}</p>
      <p>
        <strong>Average score:</strong>
        {{ Math.round(progress.stats.average_score || 0) }}%
      </p>

      <h3>Recent Results</h3>
      <ul>
        <li v-for="r in progress.results" :key="r.Exercise_Id">
          <strong>{{ r.Title }}</strong> – {{ calculatePercent(r) }}%
        </li>
      </ul>
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
      const ex = await fetch("http://localhost/larportalen2025/api/exercises.php");
      this.exercises = await ex.json();
    } catch (err) {
      console.error("Error fetching exercises:", err);
      this.exercises = [];
    }

    try {
      const res = await fetch(
        `http://localhost/larportalen2025/api/user_progress.php?user_id=${this.user.id}`
      );
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
