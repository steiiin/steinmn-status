<template>
  <VMenu location="top" open-on-hover>
    <template #activator="{ props }">
      <v-chip prepend-icon="mdi-harddisk" label v-bind="props"
        :color="stateColor" variant="elevated">{{ label }}
      </v-chip>
    </template>
    <VCard elevation="6">
      <VCardText>
        <div class="title">Festplatte • {{ label }}</div>
        <div class="info">
          <div>Status:</div><i>{{ stateDescription }}</i>
          <div>Frei:</div><i>{{ freeSpace }}%</i>
        </div>
      </VCardText>
    </VCard>
  </VMenu>
</template>
<script setup>
import { computed } from 'vue';
import { VChip, VMenu, VCard, VCardText } from 'vuetify/components'

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  state: {
    type: Object,
  },
})

// ok: true/false
// health: true/false
// error: null|'not_mount'|'smartctl_error'|'smart_failed'|'attr_pending'
//            |'attr_offline_uncorrectable'|'attr_reallocated'|'selftest_errors'
// free_p: float

const stateColor = computed(() => {
  const ok = props.state.ok
  const health = props.state.health
  if (ok && health) { return 'success' }
  if (ok && !health) { return 'warning' }
  return 'error'
})

const stateDescription = computed(() => {
  if (!props.state.error) { return 'Eingebunden & Gesund' }
  if (props.state.error == 'not_mount') { return 'Nicht eingebunden' }
  if (props.state.error == 'smart_failed') { return 'SMART-Fehler' }
  if (props.state.error == 'attr_pending') { return 'Fehlerhafte Sektoren' }
  if (props.state.error == 'attr_offline_uncorrectable') { return 'Festplattenfehler' }
  if (props.state.error == 'attr_reallocated') { return 'Defekte Sektoren' }
  if (props.state.error == 'selftest_errors') { return 'Selbsttest nicht bestanden' }
  return 'Unbekannt'
})

const freeSpace = computed(() => (props.state.free_p * 100).toFixed(1) )

</script>
<style scoped>
.title {
  font-size: 1rem;
  font-weight: 600;
}
.info > div {
  text-transform: uppercase;
}

.info i {
  font-style: normal;
  color: #8c8c8c;
}

.info {
  display: grid;
  grid-template-columns: auto 1fr;
  column-gap: 0.5rem;
}
</style>