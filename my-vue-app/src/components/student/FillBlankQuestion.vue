<template>
  <div>
    <p class="q-text">{{ data.text }}</p>

    <div
      v-for="(ans,i) in userAnswers"
      :key="i"
      class="blank-row"
    >
      <select
        v-model="userAnswers[i]"
        @change="update"
        :disabled="disabled"
      >
        <option disabled value="">-- välj ett alternativ --</option>
        <option
          v-for="opt in optionPool"
          :key="opt + i"
          :value="opt"
        >
          {{ opt }}
        </option>
      </select>
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
        : (Array.isArray(this.data?.answers) ? this.data.answers : []).map(() => ""),
    };
  },
  computed: {
    optionPool() {
      const opts = Array.isArray(this.data?.options) && this.data.options.length
        ? this.data.options
        : this.data?.answers || [];
      return [...new Set(opts)];
    },
  },

  watch: {
    modelValue(val) {
      if (!val || !val.userAnswer) {
        const answers = Array.isArray(this.data?.answers) ? this.data.answers : [];
        this.userAnswers = answers.map(() => "");
      }
    },
    "data.answers": {
      deep: true,
      handler(newAnswers) {
        this.userAnswers = Array.isArray(newAnswers)
          ? newAnswers.map(() => "")
          : [];
      },
    },
  },
  methods: {
    update() {
      const correct = Array.isArray(this.data?.answers) ? this.data.answers : [];
      const user = this.userAnswers.slice(0, correct.length);

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
  border: 1px solid var(--border);
  background: var(--surface-alt);
  transition: border-color 0.2s;
}
.blank-row input:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--surface);
}
.blank-row select {
  width: 100%;
  padding: 0.75rem 0.9rem;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--surface-alt);
}
.blank-row select:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--surface);
}
</style>
