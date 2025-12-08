<template>
  <div>
    <label>Text:</label>
    <textarea
      :value="localData.text"
      @input="updateText($event.target.value)"
      placeholder="Skriv texten. Klicka ord som ska bli luckor."
    ></textarea>

    <h4>Markera luckor</h4>
    <p class="hint">Klicka orden som ska döljas. Flera är ok.</p>
    <div class="chips">
      <button
        v-for="(word, idx) in wordList"
        :key="idx + word"
        type="button"
        class="chip"
        :class="{ active: selectedIndexes.includes(idx) }"
        @click="toggleBlank(idx)"
      >
        <input type="checkbox" :checked="selectedIndexes.includes(idx)" readonly />
        {{ word }}
      </button>
    </div>

    <h4>Ordlista (bank)</h4>
    <p class="hint">Lägg till alla ord som ska gå att dra till luckorna. Markerade ord läggs till automatiskt.</p>
    <div v-for="(opt, i) in localData.words" :key="'word-'+i" class="row option-row">
      <input
        :value="opt"
        @input="updateWord(i, $event.target.value)"
        placeholder="Ord"
      />
      <button type="button" @click="removeOption(i)">🗑️</button>
    </div>
    <button type="button" @click="addOption">+ Lägg till ord</button>
  </div>
</template>

<script>
export default {
  // Admin textluckor editor: select blank words and build a word bank for drag/drop.
  props: ["modelValue"],
  emits: ["update:modelValue"],

  data() {
    return {
      localData: {
        text: "",
        blanks: [],
        answers: [],
        words: [],
      },
      selectedIndexes: [],
      internalHash: "",
    };
  },

  computed: {
    wordList() {
      const text = this.localData.text || "";
      const matches = text.match(/\S+/g);
      return matches ? matches : [];
    },
  },

  watch: {
    modelValue: {
      deep: true,
      handler() {
        this.hydrateFromModel();
      },
    },
  },

  mounted() {
    this.hydrateFromModel();
  },

  methods: {
    hydrateFromModel() {
      const d = this.modelValue || {};
      const next = {
        text: d.text ?? "",
        blanks: this.normalizeBlanks(d.blanks),
        answers: Array.isArray(d.answers) ? d.answers : [],
        words: Array.isArray(d.words) ? d.words : [],
      };
      if (!next.answers.length && next.blanks.length) {
        next.answers = next.blanks.map((b) => b.word).filter(Boolean);
      }
      if (!next.words.length) {
        next.words = this.normalizeOptionsToWords(d.options);
      }
      next.words = this.ensureWords(next.answers, next.words);
      this.selectedIndexes = next.blanks
        .map((b) => (typeof b.index === "number" ? b.index : null))
        .filter((i) => i !== null);
      this.setData(next);
    },
    updateText(v) {
      this.setData({ ...this.localData, text: v });
    },
    toggleBlank(idx) {
      const exists = this.selectedIndexes.includes(idx);
      let nextIdx = exists
        ? this.selectedIndexes.filter((i) => i !== idx)
        : [...this.selectedIndexes, idx];
      nextIdx = nextIdx.sort((a, b) => a - b);
      const blanks = nextIdx.map((i) => ({ index: i, word: this.wordList[i] || "" }));
      const answers = blanks.map((b) => b.word);
      const words = this.ensureWords(answers, this.localData.words);
      this.selectedIndexes = nextIdx;
      this.setData({ ...this.localData, blanks, answers, words });
    },
    addOption() {
      const words = [...this.localData.words, ""];
      this.setData({ ...this.localData, words });
    },
    removeOption(i) {
      const words = this.localData.words.filter((_, idx) => idx !== i);
      this.setData({ ...this.localData, words });
    },
    updateWord(i, v) {
      const words = [...this.localData.words];
      words[i] = v;
      this.setData({ ...this.localData, words });
    },
    ensureWords(answers, words) {
      const list = Array.isArray(words) ? [...words] : [];
      (answers || []).forEach((a) => {
        if (a && !list.includes(a)) list.push(a);
      });
      return list;
    },
    normalizeBlanks(raw) {
      if (!Array.isArray(raw)) return [];
      return raw
        .map((b) => {
          if (typeof b === "object" && b !== null) {
            return {
              index: typeof b.index === "number" ? b.index : null,
              word: b.word || b.text || "",
            };
          }
          return { index: null, word: b || "" };
        })
        .filter((b) => b.word);
    },
    normalizeOptionsToWords(opts) {
      if (!Array.isArray(opts)) return [];
      return opts.map((o) => (typeof o === "string" ? o : o?.text || "")).filter(Boolean);
    },
    setData(next) {
      const currentHash = this.internalHash;
      const nextHash = JSON.stringify(next);
      if (currentHash === nextHash) return;
      this.internalHash = nextHash;
      this.localData = next;
      this.$emit("update:modelValue", next);
    },
  },
};
</script>

<style scoped>
.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 1px solid var(--border);
  background: var(--surface-alt);
  padding: 0.35rem 0.65rem;
  border-radius: 12px;
  cursor: pointer;
}
.chip.active {
  background: var(--accent);
  border-color: var(--primary);
}
.chip input {
  pointer-events: none;
}
.option-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 0.5rem;
  align-items: center;
}
</style>
