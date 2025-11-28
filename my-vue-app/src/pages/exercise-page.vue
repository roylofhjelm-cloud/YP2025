<template>
  <div class="exercise-page" v-if="exercise">
    <h1>{{ exercise.Title }}</h1>
    <p>{{ exercise.Description }}</p>

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

    <div class="actions">
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
    </div>

    <div v-if="showResults" class="result">
      <div class="score-card">
        <p>Resultat</p>
        <strong>{{ score }}/{{ questions.length }}</strong>
        <small>rätta svar</small>
      </div>
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

export default {
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
      exercise: null,
      questions: [],
      answers: [],
      score: 0,
      percentage: 0,
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
    const id = this.$route.params.id;
    try {
      const res = await fetch(
        `http://localhost/larportalen2025/api/exercise.php?id=${id}`
      );
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

    } catch (err) {
      console.error("Failed loading exercise", err);
    }
  },
  methods: {
    getComponent(type) {
      return {
        mcq: "MultipleChoiceQuestion",
        true_false: "TrueFalseQuestion",
        ordering: "OrderingQuestion",
        match: "MatchQuestion",
        fill_blank: "FillBlankQuestion",
      }[type] || "div";
    },

    updateAnswer(index, value) {
      this.$set
        ? this.$set(this.answers, index, value)
        : (this.answers[index] = value);
    },

    async checkAnswers() {
      let correct = 0;

      this.questions.forEach((q, i) => {
        const answer = this.answers[i];
        if (answer && answer.correct === true) {
          correct++;
        }
      });

      const total = this.questions.length;
      const percent = Math.round((correct / total) * 100);

      this.score = correct;
      this.percentage = percent;
      this.showResults = true;

      this.saveResult(percent);
    },

    async saveResult(percent) {
      const userId = localStorage.getItem("student_id");
      const exerciseId = this.exercise?.Exercise_Id;

      if (!userId || !exerciseId) return;

      try {
        const res = await fetch("http://localhost/larportalen2025/api/save_result.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            user_id: userId,
            exercise_id: exerciseId,
            score: percent,
            total: 100,
          }),
        });

        const data = await res.json();
        console.log("✅ Result saved:", data);

        if (data.passed) {
          alert(`✅ Passed! (${data.percent}%) +50 XP`);
        } else {
          alert(`❌ Failed (${data.percent}%). Try again`);
        }
      } catch (err) {
        console.error("❌ Could not save result", err);
      }
    },

    resetQuiz() {
      this.answers = Array(this.questions.length).fill(null);
      this.showResults = false;
      this.score = 0;
      this.percentage = 0;
    },
  },
};
</script>

<style scoped>
.exercise-page {
  max-width: 900px;
  margin: 2rem auto;
  background: #f8fafc;
  padding: 2rem;
  border-radius: 30px;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.1);
  border: 1px solid rgba(148, 163, 184, 0.35);
}
.question-block {
  background: #fff;
  border-radius: 18px;
  padding: 1.4rem;
  margin-bottom: 1.5rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
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
  color: #4338ca;
  background: rgba(67, 56, 202, 0.12);
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
}
.question-type {
  text-transform: capitalize;
  color: #64748b;
  font-size: 0.9rem;
}
.actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1rem;
}
.btn {
  background: #2563eb;
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
.loading {
  text-align: center;
  color: #64748b;
  margin-top: 2rem;
}
.result {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
}
.score-card {
  background: #fff;
  padding: 1.5rem 2rem;
  border-radius: 16px;
  box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.2);
  text-align: center;
}
.score-card strong {
  font-size: 2rem;
  color: #16a34a;
}
.score-card small {
  display: block;
  color: #94a3b8;
}
.unknown-question {
  padding: 1rem;
  border-radius: 12px;
  background: rgba(248, 113, 113, 0.15);
  color: #b91c1c;
}
</style>
