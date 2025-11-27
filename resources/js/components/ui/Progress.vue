<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface ProgressProps {
  modelValue?: number;
  class?: string;
}

const props = withDefaults(defineProps<ProgressProps>(), {
  modelValue: 0,
  class: '',
});

const progressValue = computed(() => {
  const value = props.modelValue || 0;
  return Math.min(Math.max(value, 0), 100);
});
</script>

<template>
  <div
    :class="cn('relative h-4 w-full overflow-hidden rounded-full bg-secondary', props.class)"
    role="progressbar"
    :aria-valuenow="progressValue"
    aria-valuemin="0"
    aria-valuemax="100"
  >
    <div
      class="h-full w-full flex-1 bg-primary transition-all"
      :style="{ transform: `translateX(-${100 - progressValue}%)` }"
    />
  </div>
</template>
