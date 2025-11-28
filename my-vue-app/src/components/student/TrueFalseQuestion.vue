<template>
  <div>
    <p class="q-text">{{ data.text }}</p>

    <label>
      <input
        type="radio"
        :value="true"
        v-model="selected"
        @change="update"
        :disabled="disabled"
      /> Sant
    </label>

    <label>
      <input
        type="radio"
        :value="false"
        v-model="selected"
        @change="update"
        :disabled="disabled"
      /> Falskt
    </label>

    <div v-if="disabled" class="result">
      <span v-if="modelValue?.correct" class="correct">✔ Rätt</span>
      <span v-else class="wrong">✖ Fel</span>
    </div>
  </div>
</template>

<script>
export default {
  props: ["data", "modelValue", "disabled"],
  data() {
    return {
      selected: this.modelValue?.userAnswer ?? null,
    };
  },
  watch: {
    modelValue: {
      deep: true,
      handler(val) {
        this.selected = val?.userAnswer ?? null;
      },
    },
  },
  methods: {
    update() {
      const correct = this.data.answer;
      const isCorrect = this.selected === correct;

      this.$emit("update:modelValue", {
        correct: isCorrect,
        userAnswer: this.selected,
        correctAnswer: correct,
      });
    },
  },
};
</script>

<style scoped>
.tf {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
h4 {
  margin-bottom: 0.4rem;
  color: #0f172a;
}
label {
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.4);
  padding: 0.55rem 0.8rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}
</style>
