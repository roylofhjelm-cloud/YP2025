<template>
  <div>
    <p class="q-text">{{ data.text }}</p>

    <div
      v-for="(opt, i) in data.options"
      :key="i"
      class="option"
    >
      <label>
        <input
          type="radio"
          :value="i"
          v-model="selected"
          @change="update"
          :disabled="disabled"
        />
        {{ opt.text }}
      </label>
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
      const correctIndex = this.data.options.findIndex(o => o.isCorrect);
      const isCorrect = this.selected === correctIndex;

      this.$emit("update:modelValue", {
        correct: isCorrect,
        userAnswer: this.selected,
        correctAnswer: correctIndex,
      });
    },
  },
};
</script>

<style scoped>
.mcq {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
h4 {
  margin-bottom: 0.5rem;
  color: #0f172a;
}
.mcq-option {
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.4);
  border-radius: 14px;
  padding: 0.65rem 0.9rem;
  transition: border-color 0.2s, background 0.2s;
}
.mcq-option input {
  margin-right: 0.4rem;
}
.mcq-option:hover {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.08);
}
</style>
