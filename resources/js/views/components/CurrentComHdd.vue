<template>
  <v-chip prepend-icon="mdi-harddisk" label
    :color="stateColor" variant="elevated"><b>{{ label }}</b></v-chip>
</template>
<script setup>
import { computed } from 'vue';
import { VChip } from 'vuetify/components'

const props = defineProps({
  label: {
    type: String,
    required: true,
  },
  state: {
    type: Object,
  },
})

const stateColor = computed(() => {
  const ok = props.state.ok
  const health = props.state.health
  if (ok && health) { return 'success' }
  if (ok && !health) { return 'warning' }
  return 'error'
})

</script>