<template>
  <v-chip :prepend-icon="'mdi-'+icon" label
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
  icon: {
    type: String,
    required: true,
  },
  state: {
    type: Object,
  },
})

const stateColor = computed(() => {
  return props.state.ok ? 'success' : 'error'
})

</script>