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
  background: #f8fafc;
  border-radius: 22px;
  padding: 1.5rem;
  border: 1px solid rgba(148, 163, 184, 0.4);
  box-shadow: 0 20px 35px rgba(15, 23, 42, 0.08);
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
  color: #0f172a;
}
.type-select {
  border-radius: 10px;
  border: 1px solid rgba(148, 163, 184, 0.8);
  padding: 0.35rem 0.75rem;
  background: #fff;
}
.remove-btn {
  border: none;
  background: rgba(239, 68, 68, 0.12);
  color: #b91c1c;
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  cursor: pointer;
  font-weight: 600;
}
.remove-btn:hover {
  background: rgba(239, 68, 68, 0.2);
}
</style>
