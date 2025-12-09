<template>
  <div class="exercise-page" v-if="exercise">
    <h1>{{ exercise.Title }}</h1>
    <button class="back-btn" type="button" @click="goBack">← Tillbaka</button>
    <div v-if="!hasStarted" class="intro">
      <p class="description">{{ exercise.Description }}</p>
      <button class="btn primary" type="button" @click="startExercise">🚀 Börja övningen</button>
    </div>
    <div v-if="hasStarted" ref="questionTop"></div>

    <section v-if="hasStarted">
      <section
        v-for="(question, i) in questions"
        :key="question.Question_Id"
        class="question-block"
      >
        <div class="question-header">
          <span class="chip">Fråga {{ i + 1 }}</span>
          <p class="question-type">{{ typeLabels[question.Question_Type] || question.Question_Type }}</p>
        </div>

        <component
          :is="getComponent(question.Question_Type)"
          :data="question.Data"
          :model-value="answers[i]"
          @update:model-value="updateAnswer(i, $event)"
          :disabled="showResults"
        />
      </section>
    </section>

    <div v-if="hasStarted" class="actions">
      <button
        v-if="!showResults"
        @click="checkAnswers"
        class="btn"
      >
        ✅ Kontrollera svar
      </button>
      <button
        v-else
        @click="resetQuiz"
        class="btn"
      >
        🔁 Försök igen
      </button>
      <button class="btn secondary" type="button" @click="goBack">← Tillbaka</button>
    </div>

    <div v-if="showResults" class="result">
      <div class="score-card" :class="score >= 70 ? 'pass' : 'fail'">
        <p class="score-label">{{ score >= 70 ? 'Bra jobbat!' : 'Försök igen!' }}</p>

        <div class="score-value">
          <span>{{ animatedScore }}%</span>
        </div>

        <transition name="slide-fade">
          <div v-if="xpGained > 0" class="xp-banner">
            +{{ xpGained }} XP 🎉
          </div>
        </transition>
      </div>

      <canvas v-if="score >= 70" ref="confettiCanvas" class="confetti"></canvas>
    </div>
  </div>

  <div v-else class="loading">
    Laddar övning...
  </div>
</template>

<script>
import MultipleChoiceQuestion from "@/components/student/MultipleChoiceQuestion.vue";
import TrueFalseQuestion from "@/components/student/TrueFalseQuestion.vue";
import OrderingQuestion from "@/components/student/OrderingQuestion.vue";
import MatchQuestion from "@/components/student/MatchQuestion.vue";
import FillBlankQuestion from "@/components/student/FillBlankQuestion.vue";
import { API_BASE } from "@/apiConfig";

export default {
  // Exercise runner: loads a single exercise, renders questions, and evaluates score/XP.
  name: "ExercisePage",
  components: {
    MultipleChoiceQuestion,
    TrueFalseQuestion,
    OrderingQuestion,
    MatchQuestion,
    FillBlankQuestion,
  },
  data() {
    return {
      // Loaded exercise payload and prepared questions
      exercise: null,
      questions: [],
      answers: [],
      // UI state + score animation
      hasStarted: false,
      score: 0,
      animatedScore: 0,
      xpGained: 0,
      showResults: false,
      typeLabels: {
        mcq: "Flerval",
        true_false: "Sant/Falskt",
        ordering: "Ordning",
        match: "Para ihop",
        fill_blank: "Textluckor",
      },
    };
  },
  async mounted() {
    // Fetch exercise + questions by id from the route
    const id = this.$route.params.id;
    try {
      const res = await fetch(`${API_BASE}/exercise.php?id=${id}`, {
        credentials: "include",
      });
      const data = await res.json();

      this.exercise = data.exercise;
      this.questions = data.questions.map((q) => {
        let raw = typeof q.Data === "string" ? JSON.parse(q.Data) : q.Data;

        return {
          ...q,
          Question_Type: raw.type || q.Question_Type,
          Data: raw.data || raw,
        };
      });

      this.answers = Array(this.questions.length).fill(null);
      this.hasStarted = !(this.exercise?.Description && this.exercise.Description.length > 0);

    } catch (err) {
      console.error("Failed loading exercise", err);
    }
  },
  methods: {
    // Pick the correct question component for a given type
    getComponent(type) {
      return {
        mcq: "MultipleChoiceQuestion",
        true_false: "TrueFalseQuestion",
        ordering: "OrderingQuestion",
        match: "MatchQuestion",
        fill_blank: "FillBlankQuestion",
      }[type] || "div";
    },

    // Keep answers reactive per index
    updateAnswer(index, value) {
      this.$set
        ? this.$set(this.answers, index, value)
        : (this.answers[index] = value);
    },

    // Animate the score counting up
    startScoreAnimation() {
      const target = this.score;
      let start = 0;
      const step = () => {
        if (start < target) {
          start++;
          this.animatedScore = start;
          requestAnimationFrame(step);
        }
      };
      requestAnimationFrame(step);
    },

    // Simple canvas confetti for pass cases
    triggerConfetti() {
      const canvas = this.$refs.confettiCanvas;
      if (!canvas) return;
      const ctx = canvas.getContext("2d");
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;

      let pieces = new Array(80).fill().map(() => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height - canvas.height,
        size: 8 + Math.random() * 8,
        dy: 2 + Math.random() * 3,
        dx: -2 + Math.random() * 4,
        color: `hsl(${Math.random() * 360}, 90%, 60%)`,
      }));

      function update() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        pieces.forEach((p) => {
          p.x += p.dx;
          p.y += p.dy;
          if (p.y > canvas.height) p.y = -10;
          ctx.fillStyle = p.color;
          ctx.fillRect(p.x, p.y, p.size, p.size);
        });
        requestAnimationFrame(update);
      }
      update();
    },

    async checkAnswers() {
      // Calculate score from child components
      let correct = 0;
      this.questions.forEach((q, i) => {
        const ans = this.answers[i];
        if (ans && ans.correct === true) correct++;
      });

      this.score = Math.round((correct / this.questions.length) * 100);
      this.showResults = true;
      this.$nextTick(() => this.startScoreAnimation());

      const user_id = localStorage.getItem("student_id");
      const exercise_id = this.$route.params.id;

      // Save score + XP server-side
      if (user_id) {
        const token = localStorage.getItem("csrf_token");
        await fetch(`${API_BASE}/save_result.php`, {
          method: "POST",
          credentials: "include",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": token || "",
          },
          body: JSON.stringify({
            user_id,
            exercise_id,
            score: this.score,
            total: 100,
            csrf_token: token,
          }),
        })
          .then(res => res.json())
          .then(data => {
            console.log("💾 Saved:", data);
            this.xpGained = data?.xp_gained ?? 0;
            if (data.xp_gained > 0) {
              this.fireConfetti(); // 🎉 Celebrate only if gained XP
            }
          })
          .catch(err => console.error("❌ Could not save result", err));
      }
    },

    fireConfetti() {
      this.triggerConfetti();
    },

    goBack() {
      if (window.history.length > 1) {
        this.$router.back();
      } else {
        this.$router.push("/home");
      }
    },

    startExercise() {
      this.hasStarted = true;
      this.$nextTick(() => {
        if (this.$refs.questionTop && this.$refs.questionTop.scrollIntoView) {
          this.$refs.questionTop.scrollIntoView({ behavior: "smooth" });
        }
      });
    },

    resetQuiz() {
      this.answers = Array(this.questions.length).fill(null);
      this.showResults = false;
      this.score = 0;
      this.animatedScore = 0;
      this.xpGained = 0;
    },
  },
};
</script>

<style scoped>
.exercise-page {
  max-width: 900px;
  margin: 2rem auto;
  background: var(--surface-alt);
  padding: 2rem;
  border-radius: 30px;
  box-shadow: var(--shadow-strong);
  border: 1px solid var(--border);
}
.question-block {
  background: var(--surface);
  border-radius: 18px;
  padding: 1.4rem;
  margin-bottom: 1.5rem;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-soft);
}
.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.chip {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--primary-strong);
  background: var(--accent);
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
}
.question-type {
  text-transform: capitalize;
  color: var(--text-muted);
  font-size: 0.9rem;
}
.actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1rem;
}
.intro {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-soft);
}
.intro .description {
  margin: 0 0 0.75rem;
  color: var(--text);
}
.btn.primary {
  margin-top: 0.5rem;
}
.btn {
  background: var(--primary);
  color: white;
  padding: 0.85rem 1.5rem;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  box-shadow: 0 15px 25px rgba(37, 99, 235, 0.25);
  transition: transform 0.2s;
}
.btn:hover {
  transform: translateY(-1px);
}
.btn.secondary {
  background: var(--surface-alt);
  color: var(--text);
  border: 1px solid var(--border);
  box-shadow: none;
}
.back-btn {
  border: 1px solid var(--border);
  background: var(--surface-alt);
  color: var(--text);
  border-radius: 10px;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  margin-bottom: 0.75rem;
}
.loading {
  text-align: center;
  color: var(--text-muted);
  margin-top: 2rem;
}
.result {
  margin-top: 2rem;
  display: grid;
  justify-content: center;
  position: relative;
}
.score-card {
  background: var(--surface);
  padding: 2rem;
  border-radius: 16px;
  text-align: center;
  font-size: 1.4rem;
  width: 260px;
  margin: auto;
  box-shadow: 0 0 0 3px transparent;
  transition: all 0.6s ease;
}
.score-card.pass {
  box-shadow: 0 0 12px 3px rgba(34, 197, 94, 0.35);
}
.score-card.fail {
  animation: shake 0.5s;
  box-shadow: 0 0 12px 3px rgba(239, 68, 68, 0.4);
}
@keyframes shake {
  0% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  100% { transform: translateX(0); }
}

.score-value span {
  font-size: 3.5rem;
  font-weight: 700;
  display: inline-block;
  animation: pop 0.6s ease;
}
@keyframes pop {
  0% { transform: scale(0.3); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.xp-banner {
  margin-top: 1rem;
  padding: 0.6rem 1rem;
  border-radius: 12px;
  font-weight: 600;
  color: #fff;
  background: #22c55e;
  box-shadow: 0 0 12px rgba(34, 197, 94, 0.5);
}

.slide-fade-enter-active {
  transition: all .5s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}
.confetti {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
  width: 100%;
  height: 100%;
}
.unknown-question {
  padding: 1rem;
  border-radius: 12px;
  background: var(--error-bg);
  color: var(--error-text);
}
</style>
