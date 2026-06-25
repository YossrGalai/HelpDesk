<template>
  <span :class="['badge', config.class]">{{ config.label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, required: true },
})

const config = computed(() => {
  const map = {
    OPEN:        { label: 'Ouvert',    class: 'bg-amber-100 text-amber-800' },
    IN_PROGRESS: { label: 'En cours',  class: 'bg-blue-100 text-blue-800' },
    CLOSED:      { label: 'Fermé',     class: 'bg-green-100 text-green-800' },
  }
  return map[props.status] ?? { label: props.status, class: 'bg-gray-100 text-gray-700' }
})
</script>
