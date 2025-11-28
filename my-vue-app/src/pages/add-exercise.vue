<template>
  <div class="add-exercise">
    <h1>Skapa ny övning</h1>

    <form @submit.prevent="save">
      <div class="form-group">
        <label>Titel</label>
        <input v-model="title" />
      </div>

      <div class="form-group">
        <label>Beskrivning</label>
        <textarea v-model="description"></textarea>
      </div>

      <h2>Frågor</h2>

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

      <button type="button" @click="addQuestion">+ Lägg till fråga</button>

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

export default {
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
    };
  },

  methods: {
    addQuestion() {
      this.questions.push({
        type: "mcq",
        data: {},
      });
    },
    removeQuestion(i) {
      this.questions.splice(i, 1);
    },
    getEditor(type) {
      return {
        mcq: "MCQEditor",
        true_false: "TrueFalseEditor",
        ordering: "OrderingEditor",
        match: "MatchEditor",
        fill_blank: "FillBlankEditor",
      }[type];
    },

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
      const payload = {
        Title: this.title,
        Description: this.description,
        Type: "mixed",
        Created_By: 1,
        Data: {
          questions: this.questions.map((q) => ({
            type: this.normalizeType(q.type),
            data: q.data,
          })),
        },
      };

      const res = await fetch("http://localhost/larportalen2025/api/create_exercise.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const out = await res.json();
      console.log(out);
      alert("Saved!");
    },
  },
};
</script>
