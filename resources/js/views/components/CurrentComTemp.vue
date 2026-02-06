<template>
  <v-chip prepend-icon="mdi-thermometer" label
    :color="stateColor" variant="elevated">{{ state.thermal_temperature }}°C</v-chip>
</template>
<script setup>
import { computed } from 'vue';
import { VChip } from 'vuetify/components'

const props = defineProps({
  state: {
    type: Object,
  },
})

const stateColor = computed(() => {
  const range = props.state.thermal_range
  if (range == 'HIGH') { return 'error' }
  if (range == 'MEDIUM') { return 'warning' }
  return 'success'
})

</script>
<style scoped>
section .title {
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}
section .container {
  display: flex;
  gap: .5rem;
}
</style>