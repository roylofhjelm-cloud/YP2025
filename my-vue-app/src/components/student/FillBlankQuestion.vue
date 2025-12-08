<template>
  <div>
    <p class="q-text">{{ displayText }}</p>

    <div v-if="optionPool.length">
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
    </div>
    <p v-else class="warning">Inga svarsalternativ tillgängliga för denna fråga.</p>

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
        : this.defaultBlanks(),
    };
  },
  computed: {
    optionPool() {
      const opts =
        (Array.isArray(this.data?.options) && this.data.options.length
          ? this.data.options
          : this.initialAnswers()) || [];
      const normalized = opts.map((o) => (typeof o === "string" ? o : o?.text || ""));
      return [...new Set(normalized.filter(v => v !== ""))];
    },
    displayText() {
      return this.data?.text || "";
    },
    blanksCount() {
      const text = this.data?.text || "";
      const placeholderCount = (text.match(/____/g) || []).length;
      const answerCount = this.initialAnswers().length;
      return Math.max(placeholderCount || answerCount || 1, 1);
    },
  },

  watch: {
    modelValue(val) {
      if (!val || !val.userAnswer) {
        this.userAnswers = this.defaultBlanks();
      }
    },
    "data.answers": {
      deep: true,
      handler(newAnswers) {
        this.userAnswers = this.defaultBlanks(newAnswers);
      },
    },
  },
  methods: {
    initialAnswers(raw) {
      const base = raw || this.data?.answers;
      if (Array.isArray(base) && base.length) return base;
      // fallback: derive from options with isCorrect
      if (Array.isArray(this.data?.options)) {
        const derived = this.data.options
          .filter((o) => typeof o === "object" && o?.isCorrect)
          .map((o) => o.text || "");
        if (derived.length) return derived;
      }
      return [""];
    },
    defaultBlanks(raw) {
      const answers = this.initialAnswers(raw);
      const count = this.blanksCount || answers.length || 1;
      return Array(count).fill("");
    },
    update() {
      const correct = this.initialAnswers();
      const user = this.userAnswers.slice(0, correct.length);

      if (!correct.length || !this.optionPool.length) return;

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
