<template>
  <div id="app">
    <AppHeader />
    <main class="p-6">
      <router-view />
    </main>
  </div>
</template>

<script>
import AppHeader from "./components/AppHeader.vue";

export default {
  name: "App",
  components: { AppHeader },
};
</script>

<style>
body {
  margin: 0;
  background-color: #f9fafb;
  font-family: 'Inter', sans-serif;
}
</style>
