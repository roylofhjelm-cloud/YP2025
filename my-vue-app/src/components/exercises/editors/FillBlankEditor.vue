<template>
  <div>
    <label>Text med luckor:</label>
    <textarea
      :value="data.text"
      @input="updateText($event.target.value)"
    ></textarea>

    <h4>Korrekt svar</h4>

    <div v-for="(ans,i) in data.answers" :key="i" class="row">
      <input
        :value="ans"
        @input="updateAnswer(i, $event.target.value)"
      />
      <button @click="removeAnswer(i)">🗑️</button>
    </div>

    <button @click="addAnswer">+ Lägg till svar</button>
  </div>
</template>

<script>
export default {
  props: ["modelValue"],
  emits: ["update:modelValue"],

  computed: {
    data: {
      get() {
        const d = this.modelValue || {};
        return {
          text: d.text ?? "",
          answers: Array.isArray(d.answers) ? d.answers : [""],
        };
      },
      set(v) {
        this.$emit("update:modelValue", v);
      },
    },
  },

  methods: {
    updateText(v) {
      this.data = { ...this.data, text: v };
    },
    updateAnswer(i, v) {
      const answers = [...this.data.answers];
      answers[i] = v;
      this.data = { ...this.data, answers };
    },
    addAnswer() {
      this.data = { ...this.data, answers: [...this.data.answers, ""] };
    },
    removeAnswer(i) {
      this.data = {
        ...this.data,
        answers: this.data.answers.filter((_, idx) => idx !== i),
      };
    },
  },
};
</script>
