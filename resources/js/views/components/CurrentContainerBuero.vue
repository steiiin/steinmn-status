<template>
  <div class="container-status-row">
    <div class="overall">
      <StatusIndicator :state="stateColor(state.ok)" size="normal" />
      <span class="label-large">BÜRO aktiv.</span>
    </div>
    <div class="parts">
      <div class="sub-con">
        <StatusIndicator :state="stateColor(state.nc_db)" size="small" />
        <span class="label-small">Datenbank</span>
      </div>
      <div class="sub-con">
        <StatusIndicator :state="stateColor(state.nc_redis)" size="small" />
        <span class="label-small">Redis</span>
      </div>
      <div class="sub-con">
        <StatusIndicator :state="stateColor(state.nc_base)" size="small" />
        <span class="label-small">Container</span>
      </div>
      <div class="sub-con">
        <StatusIndicator :state="stateColor(state.nextcloud)" size="small" />
        <span class="label-small">Service-Status</span>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue';
import StatusIndicator from './StatusIndicator.vue';

const props = defineProps({
  state: {
    type: Object,
  },
})

const stateColor = (status) => status ? 'green' : 'red'

</script>
<style scoped>

.container-status-row {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  line-height: 1.1;
}

.container-status-row .overall,
.container-status-row .sub-con {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: .7rem .75rem;
}

.container-status-row .overall {
  width: 18rem;
}

.container-status-row .sub-con {
  gap: .5rem;
  font-size: .9rem;
  font-weight: 600;
  letter-spacing: 0;
  padding: 2px .75rem;
}

.container-status-row .parts {
  gap: 0;
}

.label-large {
  font-weight: 600;
  font-size: 1.2rem;
}
</style>