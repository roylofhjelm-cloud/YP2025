<template>
  <div class="ordering-question">
    <p class="q-text">{{ data.text }}</p>

    <div
      v-for="(item, i) in localOrder"
      :key="i"
      class="order-row"
    >
      <span class="order-index">{{ i + 1 }}.</span>
      <span class="order-text">{{ item }}</span>

      <div class="order-controls">
        <button
          type="button"
          @click="moveUp(i)"
          :disabled="disabled || i === 0"
        >
          ▲
        </button>
        <button
          type="button"
          @click="moveDown(i)"
          :disabled="disabled || i === localOrder.length - 1"
        >
          ▼
        </button>
      </div>
    </div>

    <div v-if="disabled" class="result">
      <span v-if="modelValue?.correct" class="correct">✔ Rätt</span>
      <span v-else class="wrong">✖ Fel</span>
    </div>
  </div>
</template>

<script>
export default {
  // Student ordering: reorder items with up/down controls, emits correct flag + order.
  name: "OrderingQuestion",
  props: ["data", "modelValue", "disabled"],
  emits: ["update:modelValue"],

  data() {
    return {
      // make a local copy so we can reorder without mutating props
      localOrder: this.shuffleItems(this.data?.items),
    };
  },

  watch: {
    // when parent resets the answer, reshuffle to avoid showing the solved order
    modelValue(val) {
      if (!val || !val.userAnswer || val.userAnswer.length === 0) {
        this.localOrder = this.shuffleItems(this.data?.items);
      }
    },
    // keep in sync if question data itself changes
    "data.items": {
      deep: true,
      handler(newItems) {
        this.localOrder = this.shuffleItems(newItems);
      },
    },
    // whenever local order changes, recompute correctness
    localOrder: {
      deep: true,
      handler() {
        this.emitAnswer();
      },
    },
  },

  mounted() {
    // initialize answer once
    this.emitAnswer();
  },

  methods: {
    shuffleItems(items) {
      const arr = Array.isArray(items) ? [...items] : [];
      for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
      }
      return arr;
    },
    moveUp(i) {
      if (this.disabled || i === 0) return;
      const arr = [...this.localOrder];
      const temp = arr[i - 1];
      arr[i - 1] = arr[i];
      arr[i] = temp;
      this.localOrder = arr;
    },

    moveDown(i) {
      if (this.disabled || i === this.localOrder.length - 1) return;
      const arr = [...this.localOrder];
      const temp = arr[i + 1];
      arr[i + 1] = arr[i];
      arr[i] = temp;
      this.localOrder = arr;
    },

    emitAnswer() {
      const correctOrder = this.data?.items || [];
      const isCorrect =
        JSON.stringify(this.localOrder) === JSON.stringify(correctOrder);

      this.$emit("update:modelValue", {
        correct: isCorrect,
        userAnswer: this.localOrder,
      });
    },
  },
};
</script>

<style scoped>
.ordering-question {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.order-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.35rem 0.6rem;
  border-radius: 8px;
  background: var(--surface-alt);
}
.order-index {
  font-weight: 600;
  color: var(--text-muted);
}
.order-text {
  flex: 1;
}
.order-controls button {
  margin-left: 0.15rem;
}
.result {
  margin-top: 0.4rem;
  font-weight: 700;
}
.correct {
  color: #16a34a;
}
.wrong {
  color: #dc2626;
}
</style>
