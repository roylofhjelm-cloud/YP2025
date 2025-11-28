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
          v-for="(p, idx) in data.pairs"
          :key="idx"
          :value="p.right"
        >
          {{ p.right }}
        </option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  name: "MatchQuestion",
  props: ["data", "modelValue", "disabled"],
  emits: ["update:modelValue"],

  data() {
    return {
      localMatches: Array.isArray(this.data?.pairs)
        ? this.data.pairs.map(() => "")
        : [],
    };
  },

  methods: {
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
</style>