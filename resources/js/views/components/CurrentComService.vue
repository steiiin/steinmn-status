<template>
  <VMenu location="top" open-on-hover>
    <template #activator="{ props }">
      <v-chip :prepend-icon="'mdi-'+icon" label v-bind="props"
        :color="stateColor" variant="elevated">{{ label }}
      </v-chip>
    </template>
    <VCard elevation="6">
      <VCardText>
        <div class="title">Service • {{ label }}</div>
        <div class="info">
          <div>Status:</div><i>{{ stateDescription }}</i>
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
  icon: {
    type: String,
    required: true,
  },
  state: {
    type: Object,
  },
})

// ok: true/false
// error: null|'deactivated'|'crashed'

const stateColor = computed(() => props.state.ok ? 'success' : 'error' )
const stateDescription = computed(() => {
  if (!props.state.error) { return 'OK' }
  if (props.state.error == 'deactivated' ) { return 'Deaktiviert' }
  return 'Abgestürzt'
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
  color: #555;
}

.info {
  display: grid;
  grid-template-columns: auto 1fr;
  column-gap: 0.5rem;
}
</style>