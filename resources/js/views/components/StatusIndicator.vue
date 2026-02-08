<template>
  <span
    class="status-indicator"
    :class="`status-indicator--${state}`"
    role="status" :style="dotStyle"
    :aria-label="`${state} status`"
  ></span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  state: {
    type: String,
    default: 'green',
    validator: (value) => ['green', 'yellow', 'red'].includes(value),
  },
  size: {
    type: String,
    default: 'normal',
    validator: (value) => ['normal', 'small', 'huge'].includes(value),
  },
})

const sizeValues = {
  small: 0.9,
  normal: 1.8,
  huge: 3.0,
}

const dotSize = computed(() => sizeValues[props.size])
const dotStyle = computed(() => `width:${dotSize.value}rem;height:${dotSize.value}rem`)

</script>

<style scoped>
.status-indicator {
  position: relative;
  display: flex;
  width: var(--indicator-size);
  height: var(--indicator-size);
  border-radius: 999px;
  flex-shrink: 0;
  background-color: currentColor;
}

.status-indicator::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 999px;
  background-color: currentColor;
  opacity: 0;
  transform: scale(1);
}

.status-indicator--green {
  color: #2ecc71;
}

.status-indicator--yellow {
  color: #f5c542;
}

.status-indicator--red {
  color: #ff4d4f;
}

.status-indicator--green::after {
  animation: dot-pulse 2.0s ease-out infinite;
}

.status-indicator--yellow::after {
  animation: dot-pulse 4.0s ease-out infinite;
}

.status-indicator--red::after {
  animation: dot-breath 2.6s ease-in-out infinite alternate;
}

@keyframes dot-pulse {
  0% {
    opacity: 0.5;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(2);
  }
}

@keyframes dot-breath {
  0% {
    opacity: 0;
    transform: scale(1.0);
  }
  100% {
    opacity: 0.4;
    transform: scale(1.5);
  }
}
</style>
