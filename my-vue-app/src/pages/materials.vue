<template>
  <div class="materials">
    <header class="materials-header">
      <div>
        <p class="eyebrow">📖 Reading Materials</p>
        <h1>Läsrum</h1>
      </div>
    </header>

    <div class="grid">
      <article
        v-for="m in materials"
        :key="m.Material_Id"
        class="card"
        @click="openMaterial(m)"
      >
        <div class="card-top">
          <div>
            <p class="eyebrow">Artikel</p>
            <h2>{{ m.Title || 'Namnlös' }}</h2>
          </div>
          <span class="badge">ID: {{ m.Material_Id }}</span>
        </div>
        <p class="content" style="white-space:pre-wrap">
          {{ preview(m.Content) }}
        </p>
        <button class="read-btn" type="button">Läs mer</button>
      </article>
    </div>

    <div v-if="selected" class="modal" @click.self="selected = null">
      <div class="modal-card">
        <header class="modal-header">
          <div>
            <p class="eyebrow">Artikel</p>
            <h2>{{ selected.Title || 'Namnlös' }}</h2>
          </div>
          <button class="close-btn" type="button" @click="selected = null">✕</button>
        </header>
        <section class="modal-content" style="white-space:pre-wrap">
          {{ selected.Content }}
        </section>
      </div>
    </div>
  </div>
</template>

<script>
import { API_BASE } from "@/apiConfig";

export default {
  name: "ReadingMaterialsPage",
  data() {
    return {
      materials: [],
      selected: null,
    };
  },

  async mounted() {
    const res = await fetch(`${API_BASE}/materials.php`);
    this.materials = await res.json();
  },
  methods: {
    preview(text) {
      const clean = text || "";
      return clean.length > 180 ? clean.slice(0, 180) + "…" : clean;
    },
    openMaterial(m) {
      this.selected = m;
    },
  }
};
</script>

<style scoped>
.materials {
  max-width: 1100px;
  margin: 2rem auto;
  padding: 0 1.5rem 2rem;
}
.materials-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}
.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.22em;
  font-size: 0.7rem;
  color: var(--text-muted);
  margin: 0 0 0.25rem;
}
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.1rem;
}
.card {
  background: var(--surface);
  padding: 1.1rem 1.2rem;
  border-radius: 14px;
  box-shadow: var(--shadow-soft);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}
.badge {
  background: var(--accent);
  color: var(--primary-strong);
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.8rem;
}
.content {
  margin: 0;
  color: var(--text);
  line-height: 1.5;
}
.read-btn {
  align-self: flex-start;
  border: 1px solid var(--border);
  background: var(--surface-alt);
  color: var(--text);
  border-radius: 10px;
  padding: 0.55rem 0.9rem;
  cursor: pointer;
  font-weight: 600;
}
.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 40;
}
.modal-card {
  background: var(--surface);
  max-width: 720px;
  width: 100%;
  max-height: 80vh;
  border-radius: 16px;
  box-shadow: var(--shadow-strong);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem 0.25rem;
}
.modal-content {
  padding: 0 1.25rem 1.25rem;
  overflow: auto;
  line-height: 1.6;
}
.close-btn {
  border: none;
  background: transparent;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text);
}
@media (max-width: 640px) {
  .materials {
    padding: 0 1rem 1.5rem;
  }
}
</style>
