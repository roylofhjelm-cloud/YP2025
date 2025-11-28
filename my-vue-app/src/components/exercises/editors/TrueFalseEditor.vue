<template>
  <div>
    <label>Fråga:</label>
    <input :value="data.text" @input="updateText($event.target.value)" />

    <h4>Svar:</h4>

    <label>
      <input
        type="radio"
        :checked="data.answer === true"
        @change="updateAnswer(true)"
      />
      Sant
    </label>

    <label>
      <input
        type="radio"
        :checked="data.answer === false"
        @change="updateAnswer(false)"
      />
      Falskt
    </label>
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
          answer: typeof d.answer === "boolean" ? d.answer : true,
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
    updateAnswer(v) {
      this.data = { ...this.data, answer: v };
    },
  },
};
</script>
