<template>
  <v-chip prepend-icon="mdi-lock" label
    :color="stateColor">Verschlüsselung</v-chip>
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
  return props.state.ok ? 'success' : 'error'
})

</script>