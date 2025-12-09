<template>
  <div>
    <label>Fråga:</label>
    <input :value="data.text" @input="updateText($event.target.value)" />

    <h4>Alternativ</h4>

    <div v-for="(opt,i) in data.options" :key="i" class="row">
      <input
        :value="opt.text"
        @input="updateOption(i,'text',$event.target.value)"
      />
      <input
        type="checkbox"
        :checked="opt.isCorrect"
        @change="updateOption(i,'isCorrect',$event.target.checked)"
      />
      <button type="button" @click="removeOption(i)">🗑️</button>
    </div>

    <button type="button" @click="addOption">+ Lägg till alternativ</button>
  </div>
</template>

<script>
export default {
  // Admin MCQ editor: edit question text, options list, and mark correct option.
  props: ["modelValue"],
  emits: ["update:modelValue"],

  computed: {
    data: {
      // Ensure text + at least two options exist
      get() {
        const d = this.modelValue || {};

        return {
          text: d.text ?? "",
          options: Array.isArray(d.options)
            ? d.options
            : [
                { text: "", isCorrect: false },
                { text: "", isCorrect: false },
              ],
        };
      },
      set(v) {
        this.$emit("update:modelValue", v);
      },
    },
  },

  methods: {
    // Update question text
    updateText(val) {
      this.data = { ...this.data, text: val };
    },
    // Update a specific option field
    updateOption(i, key, val) {
      const opts = [...this.data.options];
      opts[i] = { ...opts[i], [key]: val };
      this.data = { ...this.data, options: opts };
    },
    // Append a blank option
    addOption() {
      this.data = {
        ...this.data,
        options: [...this.data.options, { text: "", isCorrect: false }],
      };
    },
    // Remove option by index
    removeOption(i) {
      this.data = {
        ...this.data,
        options: this.data.options.filter((_, idx) => idx !== i),
      };
    },
  },
};
</script>
