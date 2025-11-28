<template>
  <div class="add-material">
    <h1>Lägg till läsmaterial</h1>

    <form @submit.prevent="save">
      <label>Titel</label>
      <input v-model="title" required />

      <label>Innehåll</label>
      <textarea v-model="content" rows="10" required></textarea>

      <button type="submit" class="save">💾 Spara</button>
      <p v-if="message" class="message">{{ message }}</p>
    </form>
  </div>
</template>

<script>
export default {
  name: "AddMaterial",
  data() {
    return {
      title: "",
      content: "",
      message: "",
    };
  },
  methods: {
    async save() {
      this.message = "";
      try {
        const payload = {
          Title: this.title,
          Content: this.content,
          Created_By: localStorage.getItem("admin_id") || null,
        };

        const res = await fetch("http://localhost/larportalen2025/api/materials.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        const out = await res.json();
        if (out.success) {
          this.message = "Material sparat!";
          this.title = "";
          this.content = "";
        } else {
          this.message = out.error || "Kunde inte spara.";
        }
      } catch (e) {
        this.message = "Kunde inte nå servern (kolla CORS/URL).";
        console.error(e);
      }
    },
  },
};
</script>

<style scoped>
.add-material {
  max-width: 700px;
  margin: 2rem auto;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

input,
textarea {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  background: #f8fafc;
}

textarea {
  resize: vertical;
  min-height: 180px;
}

.save {
  align-self: flex-start;
  padding: 0.85rem 1.3rem;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #4f46e5, #2563eb);
  color: white;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
}

.save:hover {
  opacity: 0.95;
}

.message {
  margin-top: 0.5rem;
  color: #0f5132;
  background: #d1e7dd;
  padding: 0.6rem 0.8rem;
  border-radius: 8px;
  border: 1px solid #badbcc;
}
</style>
