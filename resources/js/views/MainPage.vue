<template>
  <v-app class="main-page">
    <div class="background-bar" aria-hidden="true"></div>
    <v-container class="content-container" fluid>
      <header class="main-header">
        <a class="main-header--logo" href="/">
          <img :src="logo">
        </a>
      </header>
      <div class="status-card-container">
        <v-card class="status-card">
          <v-card-text class="overall-status-row">
            <StatusIndicator state="green" size="huge" />
            <span class="label-huge">Alles in Ordnung.</span>
          </v-card-text>
        </v-card>
        <v-card class="status-card">
          <v-card-text>
            <StatusTracker :data="statusData" />
          </v-card-text>
        </v-card>
        <v-card class="status-card" style="padding-left: .5rem">
          <v-card-text>
            <div class="container-status-row">
              <StatusIndicator state="green" size="normal" />
              <span class="label-large">BÜRO aktiv.</span>
            </div>
            <div class="container-status-row">
              <StatusIndicator state="green" size="normal" />
              <span class="label-large">DOKUMENTE aktiv.</span>
            </div>
            <div class="container-status-row">
              <StatusIndicator state="green" size="normal" />
              <span class="label-large">MEDIEN aktiv.</span>
            </div>
          </v-card-text>
        </v-card>
      </div>
    </v-container>
  </v-app>
</template>

<script setup>
import { VApp, VCard, VCardText, VContainer } from 'vuetify/components'
import logo from '../../assets/LogoSteinmn.png'
import StatusIndicator from './components/StatusIndicator.vue'
import StatusTracker from './components/StatusTracker.vue'

const statusData = [
  { date: '2025-02-10', availability_p: 0.9991, avg_response_time_ms: 210 },
  { date: '2025-02-09', availability_p: 0.9985, avg_response_time_ms: 240 },
  { date: '2025-02-08', availability_p: 0.993, avg_response_time_ms: 320 },
  { date: '2025-02-07', availability_p: 0.987, avg_response_time_ms: 410 },
  { date: '2025-02-06', availability_p: null, avg_response_time_ms: null },
]
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
  height: 12rem;
  background: linear-gradient(180deg, #f9f9f9 0%, #bdbdbd 100%);
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

.status-card-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.status-card {
  border-radius: 0rem;
}

.overall-status-row {
  display: flex;
  align-items: center;
  gap: 2rem;
  padding: 2rem;
  line-height: 1.1;
}

.container-status-row {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: .7rem .75rem;
  line-height: 1.1;
}

.status-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  line-height: 1.1;
}

.label-huge {
  font-weight: 600;
  letter-spacing: 1px;
  font-size: 1.5rem;
}

.label-large {
  font-weight: 600;
  font-size: 1.2rem;
}

</style>
