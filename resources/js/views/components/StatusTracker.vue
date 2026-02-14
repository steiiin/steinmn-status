<template>
  <div class="status-tracker" ref="containerRef">
    <div class="status-tracker__lines">
      <template v-for="item in displayData" :key="item.key">
        <VMenu
          v-if="isHoverable(item)"
          location="top"
          open-on-hover
        >
          <template #activator="{ props }">
            <div class="status-tracker__line-wrapper" v-bind="props">
              <div
                class="status-tracker__line"
                :class="lineClass(item)"
              ></div>
            </div>
          </template>
          <VCard class="status-tracker__popover" elevation="6">
            <VCardText>
              <div class="title">{{ formatDate(item.date) }}</div>
              <div class="info">
                <div>Online:</div><i>{{ formatAvailability(item.availability_p) }}</i>
                <div>Antwortzeit:</div><i>{{ formatResponseTime(item.avg_response_time_ms) }}</i>
              </div>
            </VCardText>
          </VCard>
        </VMenu>
        <div v-else class="status-tracker__line-wrapper is-disabled">
          <div class="status-tracker__line is-missing"></div>
        </div>
      </template>
    </div>
    <div class="status-tracker__labels" v-if="false">
      <div class="left">{{ lineCount }}d</div>
      <div class="right">Heute</div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { VCard, VCardText, VMenu } from 'vuetify/components'

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
})

const sortedData = computed(() =>
  [...props.data].sort((a, b) => new Date(b.date) - new Date(a.date)),
)

const containerRef = ref(null)
const containerWidth = ref(0)
let resizeObserver

const lineCount = computed(() => {
  const width = containerWidth.value
  const minWidth = 150
  const maxWidth = 700
  const minValue = 10
  const maxValue = 60

  const clampedWidth = Math.min(Math.max(width, minWidth), maxWidth)

  const count = Math.round(
    minValue +
      ((clampedWidth - minWidth) / (maxWidth - minWidth)) *
        (maxValue - minValue)
  )
  return Math.round(count / 5) * 5
})

const displayData = computed(() => {
  const count = lineCount.value
  const trimmed = sortedData.value.slice(0, count).map((item, index) => ({
    ...item,
    key: item.date ?? `missing-${index}`,
  }))
  const fillers = Array.from({ length: Math.max(0, count - trimmed.length) }, (_, i) => ({
    key: `missing-${trimmed.length + i}`,
    date: null,
    availability_p: null,
    avg_response_time_ms: null,
  }))
  return [...trimmed, ...fillers]
})

const hasData = (item) =>
  Number.isFinite(item.availability_p) && Number.isFinite(item.avg_response_time_ms)

const isHoverable = (item) => hasData(item) && Boolean(item.date)

const lineClass = (item) => {
  if (!hasData(item)) {
    return 'is-missing'
  }
  if (item.availability_p > 0.998) {
    return 'is-green'
  }
  if (item.availability_p > 0.99) {
    return 'is-barelygreen'
  }
  if (item.availability_p > 0.98) {
    return 'is-yellow'
  }
  return 'is-red'
}

const formatAvailability = (value) => `${(value * 100).toFixed(3)}%`
const formatResponseTime = (value) => `${Math.round(value)} ms`
const formatDate = (value) => value ?? 'Unknown'

onMounted(() => {
  if (!containerRef.value || typeof ResizeObserver === 'undefined') {
    return
  }

  resizeObserver = new ResizeObserver((entries) => {
    const entry = entries[0]
    if (entry) {
      containerWidth.value = entry.contentRect.width
    }
  })

  resizeObserver.observe(containerRef.value)
  containerWidth.value = containerRef.value.getBoundingClientRect().width
})

onBeforeUnmount(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
  }
})
</script>

<style scoped>
.status-tracker {
  width: 100%;
}

.status-tracker__lines {
  display: flex;
  flex-direction: row-reverse;
  align-items: flex-end;
  justify-content: space-between;
}

.status-tracker__line-wrapper {
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.status-tracker__line-wrapper.is-disabled {
  cursor: default;
}

.status-tracker__line {
  width: .5rem;
  height: 2rem;
  background-color: #4caf50;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.status-tracker__line-wrapper:not(.is-disabled):hover .status-tracker__line {
  transform: translateY(-4px);
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.45);
}

.status-tracker__line.is-green {
  background-color: #4caf50;
}

.status-tracker__line.is-barelygreen {
  background-color: #94d997;
}

.status-tracker__line.is-yellow {
  background-color: #f4c430;
}

.status-tracker__line.is-red {
  background-color: #ef5350;
}

.status-tracker__line.is-missing {
  background-color: #313131;
}

.status-tracker__popover {
  border-radius: 0.75rem;
  min-width: 180px;
}

.status-tracker__popover-date {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.status-tracker__popover-body {
  font-size: 0.85rem;
  line-height: 1.2;
}

.status-tracker__labels {
  display: flex;
  justify-content: space-between;
  color: #c7c7c7;
  font-size: .9em;
}

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
