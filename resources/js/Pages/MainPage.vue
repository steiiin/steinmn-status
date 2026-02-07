<template>
  <v-app class="main-page">
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
            <div style="display:flex;flex-direction:column;">
              <span class="label-huge">Alles in Ordnung.</span>
              <span class="label-subtitle">Letztes Update: <b>Vor 30min</b></span>
            </div>
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
        <v-card class="status-card">
          <v-card-text>
            <StatusTracker :data="statusData" />
          </v-card-text>
        </v-card>
        <v-card class="status-card">
          <v-card-text style="display:flex;flex-direction:column;gap:.5rem;">

            <current-container title="Server">
              <current-com-temp :state="{
                thermal_range: 'LOW', thermal_temperature: 40
              }"></current-com-temp>
              <current-com-encryption :ok="true">
              </current-com-encryption>
            </current-container>

            <current-container title="Festplatten">
              <current-com-hdd label="HDDa" :state="{
                ok: true, health: true, free_p: 0.19
              }"></current-com-hdd>
              <current-com-hdd label="HDDb" :state="{
                ok: true, health: false, free_p: 0.19
              }"></current-com-hdd>
            </current-container>

            <current-container title="Services">
              <current-com-service
                label="Docker" icon="docker"
                :ok="true">
              </current-com-service>
              <current-com-service
                label="Proxy" icon="arrow-decision-outline"
                :ok="true">
              </current-com-service>
            </current-container>

          </v-card-text>
        </v-card>
      </div>
    </v-container>
  </v-app>
</template>

<script setup>
import { VApp, VCard, VCardText, VContainer, VChip } from 'vuetify/components'
import logo from '../assets/LogoSteinmn.png'
import StatusIndicator from '../views/components/StatusIndicator.vue'
import StatusTracker from '../views/components/StatusTracker.vue'
import CurrentContainer from '../views/components/CurrentContainer.vue'
import CurrentComTemp from '../views/components/CurrentComTemp.vue'
import CurrentComHdd from '../views/components/CurrentComHdd.vue'
import CurrentComEncryption from '../views/components/CurrentComEncryption.vue'
import CurrentComService from '../views/components/CurrentComService.vue'

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

.main-header--logo {
  border: 1px solid #fff;
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
