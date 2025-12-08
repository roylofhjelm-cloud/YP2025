<template>
  <div class="add-exercise">
    <h3>Skapa ny övning</h3>

    <form @submit.prevent="save">
      <div class="form-group">
        <label>Titel</label>
        <input v-model="title" />
      </div>

      <div class="form-group">
        <label>Beskrivning</label>
        <textarea v-model="description"></textarea>
      </div>

      <div class="form-group">
        <label>Övningstyp</label>
        <select v-model="exerciseType">
          <option value="mixed">Blandad</option>
          <option value="mcq">Flerval</option>
          <option value="true_false">Sant/Falskt</option>
          <option value="ordering">Ordning</option>
          <option value="match">Para ihop</option>
          <option value="fill_blank">Textluckor</option>
        </select>
      </div>

      <h4>Frågor</h4>

      <div v-for="(q, i) in questions" :key="i" class="question-block">
        <select v-model="q.type">
          <option disabled value="">-- välj typ --</option>
          <option value="mcq">Flerval</option>
          <option value="true_false">Sant/Falskt</option>
          <option value="ordering">Ordning</option>
          <option value="match">Para ihop</option>
          <option value="fill_blank">Textluckor</option>
        </select>

        <component
          :is="getEditor(q.type)"
          v-model="questions[i].data"
        />

        <button @click="removeQuestion(i)" type="button">🗑️ Ta bort</button>
      </div>

      <button type="button" class="btn-secondary" @click="addQuestion">+ Lägg till fråga</button>

      <button type="submit" class="save">💾 Spara övning</button>
    </form>
  </div>
</template>

<script>
import MCQEditor from "@/components/exercises/editors/MCQEditor.vue";
import TrueFalseEditor from "@/components/exercises/editors/TrueFalseEditor.vue";
import OrderingEditor from "@/components/exercises/editors/OrderingEditor.vue";
import MatchEditor from "@/components/exercises/editors/MatchEditor.vue";
import FillBlankEditor from "@/components/exercises/editors/FillBlankEditor.vue";
import { API_BASE } from "@/apiConfig";

export default {
  // Add-exercise page: admin builds a mixed exercise with multiple question types.
  components: {
    MCQEditor,
    TrueFalseEditor,
    OrderingEditor,
    MatchEditor,
    FillBlankEditor,
  },

  data() {
    return {
      title: "",
      description: "",
      questions: [],
      exerciseType: "mixed",
    };
  },

  methods: {
    // Add a new blank question using the default type
    addQuestion() {
      this.questions.push({
        type: "mcq",
        data: {},
      });
    },
    // Remove a question by index
    removeQuestion(i) {
      this.questions.splice(i, 1);
    },
    // Map editor components to the selected type
    getEditor(type) {
      return {
        mcq: "MCQEditor",
        true_false: "TrueFalseEditor",
        ordering: "OrderingEditor",
        match: "MatchEditor",
        fill_blank: "FillBlankEditor",
      }[type];
    },

    // Normalize variations of question types before saving
    normalizeType(t) {
      const map = {
        mcq: "mcq",
        true_false: "true_false",
        ordering: "ordering",
        match: "match",
        fill_blank: "fill_blank",
        "true-false": "true_false",
        trueFalse: "true_false",
        order: "ordering",
        "fill-blanks": "fill_blank",
        fillBlank: "fill_blank",
        "match-pairs": "match",
        pairs: "match",
      };

      return map[t] ?? "mcq";
    },

    async save() {
      if (!this.title.trim()) {
        alert("Titel krävs");
        return;
      }
      if (!this.questions.length) {
        alert("Lägg till minst en fråga");
        return;
      }

      const payload = {
        Title: this.title,
        Description: this.description,
        Type: this.exerciseType || "mixed",
        Created_By: 1,
        Data: {
          questions: this.questions.map((q) => ({
            type: this.normalizeType(q.type),
            data: q.data,
          })),
        },
      };

      const token = localStorage.getItem("csrf_token");
      const res = await fetch(`${API_BASE}/create_exercise.php`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": token || "",
        },
        body: JSON.stringify({ ...payload, csrf_token: token }),
      });

      const out = await res.json();
      console.log(out);
      if (out.success) {
        alert("Saved!");
        this.title = "";
        this.description = "";
        this.questions = [];
        this.addQuestion();
      } else {
        alert(out.message || "Kunde inte spara övning");
      }
    },
  },
};
</script>

<style scoped>
.add-exercise {
  margin: 0;
  background: transparent;
  padding: 0;
}

form {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

input,
textarea,
select {
  width: 100%;
  padding: 0.75rem 0.9rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  font-size: 1rem;
  background: var(--surface-alt);
  text-align: center;
  text-align-last: center;
}

textarea {
  resize: vertical;
  min-height: 140px;
}

.question-block {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 0.75rem;
  background: var(--surface);
  display: grid;
  gap: 0.5rem;
}

button {
  font-family: inherit;
}

.btn-secondary {
  align-self: flex-start;
  padding: 0.65rem 1rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: var(--surface-alt);
  color: var(--text);
  cursor: pointer;
}

.save {
  align-self: flex-start;
  padding: 0.85rem 1.3rem;
  border: none;
  border-radius: 10px;
  background: var(--primary-gradient);
  color: white;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
}

.save:hover {
  opacity: 0.95;
}
</style>
