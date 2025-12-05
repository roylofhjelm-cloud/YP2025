<template>
  <div class="materials">
    <h1>📖 Reading Materials</h1>

    <div v-for="m in materials" :key="m.Material_Id" class="card">
      <h2>{{ m.Title }}</h2>
      <p style="white-space:pre-wrap">{{ m.Content }}</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      materials: []
    };
  },

  async mounted() {
    const res = await fetch("http://localhost/larportalen2025/api/materials.php");
    this.materials = await res.json();
  }
};
</script>

<style scoped>
.materials {
  max-width: 900px;
  margin: auto;
  padding: 2rem;
}
.card {
  background: var(--surface);
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
}
</style>
