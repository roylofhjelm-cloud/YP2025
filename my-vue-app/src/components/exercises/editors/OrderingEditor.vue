<template>
  <div>
    <label>Instruktion:</label>
    <input :value="data.text" @input="updateText($event.target.value)" />

    <h4>Ord i rätt ordning</h4>

    <div v-for="(item,i) in data.items" :key="i" class="row">
      <input
        :value="item"
        @input="updateItem(i, $event.target.value)"
      />
      <button type="button" @click="removeItem(i)">🗑️</button>
    </div>

    <button type="button" @click="addItem">+ Lägg till</button>
  </div>
</template>

<script>
export default {
  // Admin ordering editor: set instruction and list of items in the correct order.
  props: ["modelValue"],
  emits: ["update:modelValue"],

  computed: {
    data: {
      get() {
        const d = this.modelValue || {};
        return {
          text: d.text ?? "",
          items: Array.isArray(d.items) ? d.items : [""],
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
    updateItem(i, v) {
      const items = [...this.data.items];
      items[i] = v;
      this.data = { ...this.data, items };
    },
    addItem() {
      this.data = { ...this.data, items: [...this.data.items, ""] };
    },
    removeItem(i) {
      this.data = {
        ...this.data,
        items: this.data.items.filter((_, idx) => idx !== i),
      };
    },
  },
};
</script>
