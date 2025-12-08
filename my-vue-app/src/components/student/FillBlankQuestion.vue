<template>
  <div class="fill-blank">
    <p class="q-text">
      <span
        v-for="(token, idx) in wordList"
        :key="idx + token"
        class="token"
      >
        <span v-if="blankSlotByPosition[idx] !== undefined" class="blank-drop">
          <span
            class="drop-zone"
            :class="{ filled: userAnswers[blankSlotByPosition[idx]] }"
            @dragover.prevent
            @drop.prevent="onDrop($event, blankSlotByPosition[idx])"
          >
            <span v-if="userAnswers[blankSlotByPosition[idx]]">
              {{ userAnswers[blankSlotByPosition[idx]] }}
            </span>
            <span v-else class="placeholder">____</span>
          </span>
        </span>
        <span v-else>{{ token }}</span>
      </span>
    </p>

    <div class="word-bank" v-if="wordBank.length">
      <p class="muted">Dra ord till luckorna (eller klicka för att fylla närmaste tomma).</p>
      <div class="bank-chips">
        <button
          v-for="(word, i) in wordBank"
          :key="word + i"
          class="chip"
          draggable="true"
          @dragstart="onDragStart($event, word)"
          @click="fillNextEmpty(word)"
          :disabled="disabled"
        >
          {{ word }}
        </button>
      </div>
    </div>
    <p v-else class="warning">Inga ord i ordlistan för denna fråga.</p>

    <div v-if="disabled" class="result">
      <span v-if="modelValue?.correct" class="correct">✔ Rätt</span>
      <span v-else class="wrong">✖ Fel</span>
    </div>
  </div>
</template>

<script>
export default {
  // Student drag/drop textluckor: renders blanks as drop targets with a word bank, checks order.
  props: ["data", "modelValue", "disabled"],

  data() {
    return {
      userAnswers: [],
    };
  },
  computed: {
    wordList() {
      const text = this.data?.text || "";
      const matches = text.match(/\S+/g);
      return matches ? matches : [];
    },
    normalizedBlanks() {
      const raw = Array.isArray(this.data?.blanks) ? this.data.blanks : [];
      if (raw.length) {
        return raw
          .map((b) => ({
            index: typeof b.index === "number" ? b.index : null,
            word: b.word || b.text || "",
          }))
          .filter((b) => b.word);
      }
      const answers = this.initialAnswers();
      return answers.map((w) => ({ index: null, word: w }));
    },
    positionedBlanks() {
      const words = [...this.wordList];
      const used = new Set();
      const blanks = [];
      this.normalizedBlanks.forEach((b) => {
        if (b.index !== null && b.index >= 0 && b.index < words.length) {
          blanks.push({ position: b.index, word: b.word });
          used.add(b.index);
        }
      });
      this.normalizedBlanks
        .filter((b) => b.index === null)
        .forEach((b) => {
          const idx = words.findIndex((w, i) => w === b.word && !used.has(i));
          if (idx !== -1) {
            blanks.push({ position: idx, word: b.word });
            used.add(idx);
          }
        });
      blanks.sort((a, b) => a.position - b.position);
      return blanks;
    },
    blankSlotByPosition() {
      const map = {};
      this.positionedBlanks.forEach((b, idx) => {
        map[b.position] = idx;
      });
      return map;
    },
    correctAnswers() {
      if (this.positionedBlanks.length) {
        return this.positionedBlanks.map((b) => b.word);
      }
      return this.initialAnswers();
    },
    wordBank() {
      const words =
        (Array.isArray(this.data?.words) && this.data.words.length
          ? this.data.words
          : this.optionPool()) || [];
      return [...new Set(words.filter(Boolean))];
    },
  },
  watch: {
    modelValue(val) {
      if (!val || !val.userAnswer) {
        this.resetAnswers();
      }
    },
    data: {
      deep: true,
      handler() {
        this.resetAnswers();
      },
    },
  },
  mounted() {
    this.resetAnswers();
  },
  methods: {
    optionPool() {
      const opts =
        (Array.isArray(this.data?.options) && this.data.options.length
          ? this.data.options
          : this.initialAnswers()) || [];
      return opts.map((o) => (typeof o === "string" ? o : o?.text || ""));
    },
    initialAnswers(raw) {
      const base = raw || this.data?.answers;
      if (Array.isArray(base) && base.length) return base;
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
      const count = (this.positionedBlanks && this.positionedBlanks.length) || answers.length || 1;
      return Array(count).fill("");
    },
    resetAnswers() {
      this.userAnswers = this.modelValue?.userAnswer
        ? [...this.modelValue.userAnswer]
        : this.defaultBlanks();
    },
    onDragStart(e, word) {
      e.dataTransfer.setData("text/plain", word);
    },
    onDrop(e, slotIdx) {
      const word = e.dataTransfer.getData("text/plain");
      if (!word) return;
      this.setAnswer(slotIdx, word);
    },
    fillNextEmpty(word) {
      if (this.disabled) return;
      const idx = this.userAnswers.findIndex((a) => !a);
      this.setAnswer(idx === -1 ? 0 : idx, word);
    },
    setAnswer(slotIdx, word) {
      if (this.disabled) return;
      if (slotIdx === undefined || slotIdx === null || slotIdx < 0) return;
      const answers = [...this.userAnswers];
      answers[slotIdx] = word;
      this.userAnswers = answers;
      this.update();
    },
    update() {
      const correct = this.correctAnswers;
      const user = this.userAnswers.slice(0, correct.length);
      if (!correct.length || !this.wordBank.length) return;
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
.fill-blank {
  display: grid;
  gap: 0.75rem;
}
.q-text {
  line-height: 1.6;
}
.token {
  margin-right: 0.4rem;
}
.drop-zone {
  display: inline-flex;
  min-width: 80px;
  padding: 0.2rem 0.4rem;
  border-bottom: 2px dashed var(--border);
  align-items: center;
  justify-content: center;
}
.drop-zone.filled {
  border-color: var(--primary);
  background: var(--accent);
  border-radius: 6px;
}
.placeholder {
  color: var(--text-muted);
}
.word-bank {
  background: var(--surface-alt);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 0.6rem 0.8rem;
}
.bank-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.chip {
  border: 1px solid var(--border);
  background: var(--surface);
  border-radius: 10px;
  padding: 0.35rem 0.65rem;
  cursor: grab;
}
.chip:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.result {
  font-weight: 700;
}
.correct {
  color: #16a34a;
}
.wrong {
  color: #dc2626;
}
</style>
