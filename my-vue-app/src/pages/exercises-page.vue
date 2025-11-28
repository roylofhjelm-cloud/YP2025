<template>
  <div class="exercise-page p-6">
    <div v-if="loading" class="text-gray-500">Laddar övning...</div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-2">{{ exercise.Title }}</h1>
      <p class="text-gray-700 mb-6">{{ exercise.Description }}</p>

      <div v-if="questions.length">
        <div
          v-for="q in questions"
          :key="q.Question_Id"
          class="border rounded-xl p-4 mb-3 bg-white"
        >
          <p class="font-medium">{{ q.Statement }}</p>
        </div>
      </div>
      <div v-else class="text-gray-400">Inga frågor hittades.</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ExercisePage",
  data() {
    return {
      exercise: {},
      questions: [],
      loading: true,
    };
  },
  async mounted() {
    const id = this.$route.params.id; // get /exercise/:id from router
    try {
      const res = await fetch(`http://localhost/larportalen2025/api/exercise.php?id=${id}`);
      const data = await res.json();
      this.exercise = data.exercise;
      this.questions = data.questions;
    } catch (err) {
      console.error("Error loading exercise:", err);
    } finally {
      this.loading = false;
    }
  },
};
</script>
