<template>
  <section>
    <div class="title" v-if="title.length>0">{{ title }}</div>
    <div class="container">
      <slot></slot>
    </div>
  </section>
</template>
<script setup>

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
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