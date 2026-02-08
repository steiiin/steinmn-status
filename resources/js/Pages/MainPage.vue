<template>
  <v-app class="main-page">
    <v-container class="content-container" fluid>
      <header class="main-header">
        <a class="main-header--logo" href="/">
          <img :src="logo">
        </a>
      </header>
      <div class="status-empty" v-if="stats_unavailable">
        Kein Status verfügbar.
      </div>
      <div class="status-card-container" v-else>
        <v-card class="status-card">
          <v-card-text class="overall-status-row">
            <StatusIndicator :state="overallStatus.color" size="huge" />
            <div style="display:flex;flex-direction:column;">
              <span class="label-huge">{{ overallStatus.label }}</span>
              <span class="label-subtitle"><b>{{ probedDateText }}</b></span>
            </div>
          </v-card-text>
        </v-card>
        <v-card class="status-card" style="padding-left: .5rem">
          <v-card-text>
            <current-container-buero :state="internal_check['container-buero']"></current-container-buero>
            <hr>
            <current-container-doku :state="internal_check['container-dokumente']"></current-container-doku>
            <hr>
            <current-container-medien :state="internal_check['container-medien']"></current-container-medien>
          </v-card-text>
        </v-card>
        <v-card class="status-card">
          <v-card-text>
            <StatusTracker :data="performance" />
          </v-card-text>
        </v-card>
        <v-card class="status-card">
          <v-card-text style="display:flex;flex-direction:column;gap:.5rem;">

            <current-container title="Server">
              <current-com-temp :state="internal_check.thermal"></current-com-temp>
              <current-com-encryption :state="internal_check.encryption"></current-com-encryption>
            </current-container>

            <current-container title="Festplatten">
              <current-com-hdd label="HDDa" :state="internal_check['hdd-a']"></current-com-hdd>
              <current-com-hdd label="HDDb" :state="internal_check['hdd-b']"></current-com-hdd>
            </current-container>

            <current-container title="Services">
              <current-com-service label="Docker" icon="docker" :state="internal_check['service-docker']"></current-com-service>
              <current-com-service label="Proxy" icon="arrow-decision-outline" :state="internal_check['service-nginx']"></current-com-service>
            </current-container>

            <current-container title="Backup">
              <current-com-backup label="System" icon="docker" :state="internal_check['backup-system']"></current-com-backup>
              <current-com-backup label="Extern" icon="arrow-decision-outline" :state="internal_check['backup-external']"></current-com-backup>
              <current-com-backup label="Lokal" icon="arrow-decision-outline" :state="internal_check['backup-local']"></current-com-backup>
            </current-container>

          </v-card-text>
        </v-card>
      </div>
    </v-container>
  </v-app>
</template>

<script setup>

import { VApp, VCard, VCardText, VContainer, VChip } from 'vuetify/components'
import logo from '../../assets/LogoSteinmn.png'
import StatusIndicator from '../views/components/StatusIndicator.vue'
import StatusTracker from '../views/components/StatusTracker.vue'
import CurrentContainerBuero from '../views/components/CurrentContainerBuero.vue'
import CurrentContainerDoku from '../views/components/CurrentContainerDoku.vue'
import CurrentContainerMedien from '../views/components/CurrentContainerMedien.vue'
import CurrentContainer from '../views/components/CurrentContainer.vue'
import CurrentComTemp from '../views/components/CurrentComTemp.vue'
import CurrentComHdd from '../views/components/CurrentComHdd.vue'
import CurrentComEncryption from '../views/components/CurrentComEncryption.vue'
import CurrentComService from '../views/components/CurrentComService.vue'
import CurrentComBackup from '../views/components/CurrentComBackup.vue'

import { router } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted } from 'vue'
import { absoluteTime } from '../../utils/date'

const props = defineProps({
  performance: {
    type: Array,
    default: () => [],
  },
  internal_check: {
    type: Object,
  },
  internal_ok: {
    type: Boolean,
  },
  external_check: {
    type: Object,
  },
})

const stats_unavailable = computed(() => {
  return props.performance.length==0
    || !props.internal_check
    || !props.external_check
    || props.internal_ok === null
})

const overallStatus = computed(() => {
  const externalOk = props.external_check.is_available
  const internalOk = props.internal_ok

  const latestResponseTime = props.external_check.response_time_ms
  const hddOk = props.internal_check['hdd-a'].health && props.internal_check['hdd-b'].health

  if (!internalOk) { return { label: 'Server meldet Probleme.', color: 'red' } }
  if (!externalOk) { return { label: 'Server nicht erreichbar.', color: 'red' } }
  if (!hddOk || (!!latestResponseTime && latestResponseTime > 1000)) { return { label: 'Server läuft mit Auffälligkeiten.', color: 'yellow' }  }
  return { label: 'Alles in Ordnung.', color: 'green' }
})
const probedDateText = computed(() => {

  const latestExternal = absoluteTime(new Date(props.external_check.probed_at))
  const dates = Object.values(props.internal_check).map(b => new Date(b.ts)).sort((a, b) => b - a)
  const latestInternal = dates.length>0 ? absoluteTime(dates[0]) : 'Nie'
  return `Extern: ${latestExternal} | Intern: ${latestInternal}`

})

const refreshIntervalMs = 2 * 60 * 1000
let refreshIntervalId

onMounted(() => {
  refreshIntervalId = setInterval(() => {
    router.reload({ preserveScroll: true, preserveState: true })
  }, refreshIntervalMs)
})

onBeforeUnmount(() => {
  if (refreshIntervalId) {
    clearInterval(refreshIntervalId)
  }
})

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
  filter: invert();
}

.content-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 56rem;
  margin: 0 auto;
  padding: 2rem 1.5rem 2rem;
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

hr {
  border: none;
  background: #000;
  height: .1rem;
  margin: 1rem -1.5rem;
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

@media only screen and (max-width: 580px) {
  .label-huge {
    font-size: 1.2rem;
  }
  .content-container {
    padding: 1rem .75rem 1rem;
  }
  .main-header {
    margin-bottom: 1rem;
  }
  .status-card-container {
    gap: .75rem;
  }
}

@media only screen and (max-width: 530px) {
  .content-container {
    padding: 1rem 0 1rem;
  }
  .status-card-container {
    gap: .5rem;
  }
}

.status-empty {
  color: #fff;

}
</style>
