<template>
  <div>
    <label>Text med luckor:</label>
    <textarea
      :value="data.text"
      @input="updateText($event.target.value)"
    ></textarea>

    <h4>Korrekt svar</h4>

    <div v-for="(ans,i) in data.answers" :key="i" class="row">
      <input
        :value="ans"
        @input="updateAnswer(i, $event.target.value)"
      />
      <button type="button" @click="removeAnswer(i)">🗑️</button>
    </div>

    <button type="button" @click="addAnswer">+ Lägg till svar</button>

    <h4>Svarsalternativ (visas för elever)</h4>
    <p class="hint">Lägg till de alternativ som ska gå att välja. Om du lämnar listan tom används de korrekta svaren.</p>
    <div v-for="(opt,i) in data.options" :key="'opt-'+i" class="row">
      <input
        :value="opt"
        @input="updateOption(i, $event.target.value)"
        placeholder="Alternativ"
      />
      <button type="button" @click="removeOption(i)">🗑️</button>
    </div>
    <button type="button" @click="addOption">+ Lägg till alternativ</button>
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
          answers: Array.isArray(d.answers) ? d.answers : [""],
          options: Array.isArray(d.options)
            ? d.options
            : Array.isArray(d.answers)
              ? [...d.answers]
              : [""],
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
    updateAnswer(i, v) {
      const answers = [...this.data.answers];
      answers[i] = v;
      this.data = { ...this.data, answers };
    },
    addAnswer() {
      this.data = { ...this.data, answers: [...this.data.answers, ""] };
    },
    removeAnswer(i) {
      this.data = {
        ...this.data,
        answers: this.data.answers.filter((_, idx) => idx !== i),
      };
    },
    updateOption(i, v) {
      const options = [...this.data.options];
      options[i] = v;
      this.data = { ...this.data, options };
    },
    addOption() {
      this.data = { ...this.data, options: [...this.data.options, ""] };
    },
    removeOption(i) {
      this.data = {
        ...this.data,
        options: this.data.options.filter((_, idx) => idx !== i),
      };
    },
  },
};
</script>
