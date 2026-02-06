<template>
  <v-chip prepend-icon="mdi-lock" label
    :color="stateColor" variant="elevated">Verschlüsselung</v-chip>
</template>
<script setup>
import { computed } from 'vue';
import { VChip } from 'vuetify/components'

const props = defineProps({
  ok: {
    type: Boolean,
  },
})

const stateColor = computed(() => {
  return props.ok ? 'success' : 'error'
})

</script>