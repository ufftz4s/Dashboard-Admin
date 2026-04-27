<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainLayout from './components/layout/MainLayout.vue';

const route = useRoute();

// Logika untuk mengecek apakah halaman saat ini adalah Login
// Jika path-nya adalah '/', maka isLoginPage akan bernilai true
const isLoginPage = computed(() => route.path === '/');
</script>

<template>
  <MainLayout v-if="!isLoginPage" />
  
  <div v-else>
    <router-view />
  </div>
</template>

<style>
body {
  margin: 0;
  padding: 0;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}
</style>