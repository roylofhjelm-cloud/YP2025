<template>
  <div class="match-question">
    <p class="q-text">{{ data.text }}</p>

    <div
      v-for="(pair, i) in data.pairs"
      :key="i"
      class="match-row"
    >
      <span class="left">{{ pair.left }}</span>

      <select
        v-model="localMatches[i]"
        @change="emitAnswer"
        :disabled="disabled"
      >
        <option disabled value="">-- välj --</option>
        <option
          v-for="(p, idx) in rightOptions"
          :key="idx"
          :value="p"
        >
          {{ p }}
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
  // Student match pairs: choose right-side option per left term, track correctness, allow reset.
  name: "MatchQuestion",
  props: ["data", "modelValue", "disabled"],
  emits: ["update:modelValue"],

  data() {
    return {
      localMatches: Array.isArray(this.data?.pairs)
        ? this.data.pairs.map(() => "")
        : [],
      rightOptions: this.shuffleRights(this.data?.pairs),
    };
  },

  watch: {
    modelValue: {
      deep: true,
      handler(val) {
        if (!val || !val.userAnswer) {
          this.localMatches = Array.isArray(this.data?.pairs)
            ? this.data.pairs.map(() => "")
            : [];
        }
      },
    },
    "data.pairs": {
      deep: true,
      handler(pairs) {
        this.rightOptions = this.shuffleRights(pairs);
        this.localMatches = Array.isArray(pairs) ? pairs.map(() => "") : [];
      },
    },
  },

  methods: {
    shuffleRights(pairs) {
      const arr = Array.isArray(pairs) ? pairs.map(p => p.right) : [];
      for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
      }
      return arr;
    },
    emitAnswer() {
      const correctRights = (this.data?.pairs || []).map(p => p.right);
      const user = this.localMatches;

      const isCorrect =
        user.length === correctRights.length &&
        user.every((val, i) => val === correctRights[i]);

      this.$emit("update:modelValue", {
        correct: isCorrect,
        userAnswer: user,
      });
    },
  },
};
</script>

<style scoped>
.match-question {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.match-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.left {
  min-width: 120px;
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
