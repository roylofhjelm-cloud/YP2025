<template>
  <div>
    <p class="q-text">{{ data.text }}</p>

    <div
      v-for="(ans,i) in userAnswers"
      :key="i"
      class="blank-row"
    >
      <input
        v-model="userAnswers[i]"
        @input="update"
        :disabled="disabled"
      />
    </div>

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
      userAnswers: this.modelValue?.userAnswer
        ? [...this.modelValue.userAnswer]
        : this.data.answers.map(() => ""),
    };
  },

  methods: {
    update() {
      const correct = this.data.answers;
      const user = this.userAnswers;

      const isCorrect = JSON.stringify(user) === JSON.stringify(correct);

      this.$emit("update:modelValue", {
        correct: isCorrect,
        userAnswer: user,
        correctAnswer: correct,
      });
    },
  },
};
</script>

<style scoped>
.blank-row input {
  width: 100%;
  padding: 0.7rem 0.9rem;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.6);
  background: #f8fafc;
  transition: border-color 0.2s;
}
.blank-row input:focus {
  outline: none;
  border-color: #6366f1;
  background: #fff;
}
</style>
