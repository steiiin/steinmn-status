<template>
  <span
    class="status-indicator"
    :class="`status-indicator--${state}`"
    role="status"
    :aria-label="`${state} status`"
  ></span>
</template>

<script setup>
const props = defineProps({
  state: {
    type: String,
    default: 'green',
    validator: (value) => ['green', 'yellow', 'red'].includes(value),
  },
})
</script>

<style scoped>
.status-indicator {
  --indicator-size: 0.9rem;
  position: relative;
  display: inline-flex;
  width: var(--indicator-size);
  height: var(--indicator-size);
  border-radius: 999px;
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
  animation: green-pulse 1.1s ease-out infinite;
}

.status-indicator--yellow::after {
  animation: yellow-pulse 1.8s ease-out infinite;
}

.status-indicator--red::after {
  opacity: 0.55;
  animation: red-breath 2.6s ease-in-out infinite alternate;
}

@keyframes green-pulse {
  0% {
    opacity: 0.6;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(2.1);
  }
}

@keyframes yellow-pulse {
  0% {
    opacity: 0.5;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(2.4);
  }
}

@keyframes red-breath {
  0% {
    opacity: 0.65;
    transform: scale(0.95);
  }
  100% {
    opacity: 0.35;
    transform: scale(1.45);
  }
}
</style>
