<template>
  <article class="question-card">
    <header class="question-card__header">
      <div>
        <p class="question-card__label">Fråga {{ index + 1 }}</p>
        <select v-model="localType" class="type-select">
          <option v-for="t in typeOptions" :key="t.value" :value="t.value">
            {{ t.label }}
          </option>
        </select>
      </div>
      <button type="button" class="remove-btn" @click="$emit('remove')">
        Ta bort
      </button>
    </header>

    <slot />
  </article>
</template>

<script>
export default {
  name: "QuestionCard",
  props: {
    question: {
      type: Object,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    typeOptions: {
      type: Array,
      default: () => [],
    },
  },
  emits: ["change-type", "remove"],
  data() {
    return {
      localType: this.question.type,
    };
  },
  watch: {
    question: {
      deep: true,
      handler(val) {
        if (val?.type !== this.localType) {
          this.localType = val?.type;
        }
      },
    },
    localType(val) {
      this.$emit("change-type", val);
    },
  },
};
</script>

<style scoped>
.question-card {
  background: var(--surface-alt);
  border-radius: 22px;
  padding: 1.5rem;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-soft);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.question-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}
.question-card__label {
  margin: 0 0 0.2rem;
  font-weight: 600;
  color: var(--text);
}
.type-select {
  border-radius: 10px;
  border: 1px solid var(--border);
  padding: 0.35rem 0.75rem;
  background: var(--surface);
  color: var(--text);
}
.remove-btn {
  border: none;
  background: var(--error-bg);
  color: var(--error-text);
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  cursor: pointer;
  font-weight: 600;
}
.remove-btn:hover {
  background: var(--error-bg);
  opacity: 0.85;
}
</style>
