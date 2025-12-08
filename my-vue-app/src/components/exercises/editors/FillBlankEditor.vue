<template>
  <div>
    <label>Text:</label>
    <textarea
      :value="localData.text"
      @input="updateText($event.target.value)"
      placeholder="Skriv texten och använd ____ där luckan ska vara"
    ></textarea>

    <h4>Alternativ (vad eleven får välja på)</h4>
    <p class="hint">Lägg till alla alternativ inklusive det korrekta. Kryssa i rätt svar.</p>
    <div v-for="(opt, i) in localData.options" :key="'opt-'+i" class="row option-row">
      <input
        :value="opt.text"
        @input="updateOptionText(i, $event.target.value)"
        placeholder="Alternativ"
      />
      <label class="check">
        <input
          type="checkbox"
          :checked="opt.isCorrect"
          @change="updateOptionCorrect(i, $event.target.checked)"
        />
        Rätt
      </label>
      <button type="button" @click="removeOption(i)">🗑️</button>
    </div>
    <button type="button" @click="addOption">+ Lägg till alternativ</button>
  </div>
</template>

<script>
export default {
  props: ["modelValue"],
  emits: ["update:modelValue"],

  data() {
    return {
      localData: {
        text: "",
        answers: [],
        options: [],
      },
      internalHash: "",
    };
  },

  computed: {
    // no computed fields needed
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
    normalizeOptions(raw) {
      if (!Array.isArray(raw)) return [];
      return raw.map((opt) => {
        if (typeof opt === "string") {
          return { text: opt, isCorrect: false };
        }
        return {
          text: opt?.text ?? "",
          isCorrect: Boolean(opt?.isCorrect),
        };
      });
    },
    hydrateFromModel() {
      const d = this.modelValue || {};
      const next = {
        text: d.text ?? "",
        answers: Array.isArray(d.answers) ? d.answers : [],
        options: this.normalizeOptions(d.options),
      };
      this.setData(next);
    },
    updateText(v) {
      this.setData({ ...this.localData, text: v });
    },
    updateOptionText(i, v) {
      const options = this.normalizeOptions(this.localData.options);
      if (!options[i]) return;
      options[i] = { ...options[i], text: v };
      this.persistOptions(options);
    },
    addOption() {
      const options = this.normalizeOptions(this.localData.options);
      options.push({ text: "", isCorrect: false });
      this.persistOptions(options);
    },
    removeOption(i) {
      const options = this.normalizeOptions(this.localData.options).filter((_, idx) => idx !== i);
      this.persistOptions(options);
    },
    updateOptionCorrect(i, checked) {
      let options = this.normalizeOptions(this.localData.options);
      options = options.map((opt, idx) => ({
        ...opt,
        isCorrect: idx === i ? checked : false,
      }));
      this.persistOptions(options);
    },
    ensureSingleCorrect(options, answers) {
      if (!options.length) return options;
      const withCorrect = options.some((o) => o.isCorrect);
      if (!withCorrect && answers?.length) {
        const ans = answers[0];
        return options.map((o) => ({ ...o, isCorrect: o.text === ans }));
      }
      return options;
    },
    persistOptions(options) {
      const answers = options.filter((o) => o.isCorrect).map((o) => o.text);
      this.setData({ ...this.localData, options, answers });
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
.option-row {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 0.5rem;
  align-items: center;
}
.check {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.9rem;
}
</style>
