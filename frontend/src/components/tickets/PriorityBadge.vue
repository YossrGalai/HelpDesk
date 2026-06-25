<template>
  <span :class="['badge', config.class]">{{ config.label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  priority: { type: String, required: true },
})

const config = computed(() => {
  const map = {
    LOW:      { label: 'Faible',    class: 'bg-gray-100 text-gray-600' },
    MEDIUM:   { label: 'Moyenne',   class: 'bg-blue-100 text-blue-700' },
    HIGH:     { label: 'Haute',     class: 'bg-orange-100 text-orange-700' },
    CRITICAL: { label: 'Critique',  class: 'bg-red-100 text-red-700' },
  }
  return map[props.priority] ?? { label: props.priority, class: 'bg-gray-100 text-gray-600' }
})
</script>
