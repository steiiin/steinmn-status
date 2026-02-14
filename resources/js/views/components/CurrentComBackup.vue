<template>
  <VMenu location="top" open-on-hover>
    <template #activator="{ props }">
      <v-chip prepend-icon="mdi-backup-restore" label v-bind="props"
        :color="stateColor" variant="elevated">{{ label }}
      </v-chip>
    </template>
    <VCard elevation="6">
      <VCardText>
        <div class="title">Backup • {{ label }}</div>
        <div class="info">
          <div>Status:</div><i>{{ stateDescription }}</i>
          <div>Vom:</div><i>{{ state.birth }}</i>
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

// ok: true|false
// error: null|'frozen'|'old'|'not_available'
// birth: string e.g. "Do 5. Feb 00:30:03 CET 2026"

const stateColor = computed(() => {
  if (!props.state.error) { return 'success' }
  if (props.state.error == 'old') { return 'warning' }
  return 'error'
})

const stateDescription = computed(() => {
  if (!props.state.error) { return 'OK' }
  if (props.state.error == 'old') { return 'warning' }
  return 'error'
})

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