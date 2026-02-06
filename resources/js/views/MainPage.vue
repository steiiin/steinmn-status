<template>
  <v-app class="main-page">
    <div class="background-bar" aria-hidden="true"></div>
    <v-container class="content-container" fluid>
      <header class="main-header">
        <div class="logo-placeholder"></div>
      </header>
      <v-card class="placeholder-card" elevation="6">
        <v-card-text>
          Placeholder content
        </v-card-text>
      </v-card>
    </v-container>
  </v-app>
</template>

<script setup>
import 'vuetify/styles'
import { VApp, VCard, VCardText, VContainer } from 'vuetify/components'
</script>

<style scoped>
.main-page {
  min-height: 100vh;
  background-color: #000;
  position: relative;
  overflow: hidden;
  color: #fff;
}

.background-bar {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 25vh;
  background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
  z-index: 0;
}

.content-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 56rem;
  margin: 0 auto;
  padding: 2rem 1.5rem 4rem;
  background-color: transparent;
}

.main-header {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  height: 4rem;
  background: transparent;
  margin-bottom: 2rem;
}

.logo-placeholder {
  width: 140px;
  height: 32px;
  border: 1px dashed rgba(255, 255, 255, 0.5);
  border-radius: 6px;
}

.placeholder-card {
  background-color: rgba(15, 23, 42, 0.85);
  color: #fff;
}
</style>
