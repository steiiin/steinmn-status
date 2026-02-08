<template>
  <div class="container-status-row">
    <div class="overall">
      <StatusIndicator :state="stateColor(state.ok)" size="normal" />
      <span class="label-large">MEDIEN {{ state.ok ? 'aktiv' : 'gestört' }}.</span>
    </div>
    <div class="parts">
      <div class="sub-con">
        <StatusIndicator :state="stateColor(state.jf_base)" size="small" />
        <span class="label-small">Container</span>
      </div>
      <div class="sub-con">
        <StatusIndicator :state="stateColor(state.jellyfin)" size="small" />
        <span class="label-small">Anwendung</span>
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

@media only screen and (max-width: 530px) {
  .container-status-row .overall {
    width: 15rem;
  }
  .label-large {
    font-size: 1rem;
  }
}


@media only screen and (max-width: 385px) {
  .container-status-row .overall {
    width: 10rem;
    padding: .2rem;
  }
}
</style>