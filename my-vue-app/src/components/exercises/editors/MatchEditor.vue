<template>
  <div>
    <label>Instruktion:</label>
    <input :value="data.text" @input="updateText($event.target.value)" />

    <h4>Par</h4>

    <div v-for="(pair,i) in data.pairs" :key="i" class="row">
      <input
        :value="pair.left"
        @input="updatePair(i, 'left', $event.target.value)"
        placeholder="Vänster"
      />
      <input
        :value="pair.right"
        @input="updatePair(i, 'right', $event.target.value)"
        placeholder="Höger"
      />
      <button @click="removePair(i)">🗑️</button>
    </div>

    <button @click="addPair">+ Lägg till par</button>
  </div>
</template>

<script>
export default {
  props: ["modelValue"],
  emits: ["update:modelValue"],

  computed: {
    data: {
      get() {
        const d = this.modelValue || {};
        return {
          text: d.text ?? "",
          pairs: Array.isArray(d.pairs)
            ? d.pairs
            : [{ left: "", right: "" }],
        };
      },
      set(v) {
        this.$emit("update:modelValue", v);
      },
    },
  },

  methods: {
    updateText(v) {
      this.data = { ...this.data, text: v };
    },
    updatePair(i, key, v) {
      const pairs = [...this.data.pairs];
      pairs[i] = { ...pairs[i], [key]: v };
      this.data = { ...this.data, pairs };
    },
    addPair() {
      this.data = {
        ...this.data,
        pairs: [...this.data.pairs, { left: "", right: "" }],
      };
    },
    removePair(i) {
      this.data = {
        ...this.data,
        pairs: this.data.pairs.filter((_, idx) => idx !== i),
      };
    },
  },
};
</script>
