<script setup lang="ts">
import { inject, type Ref } from 'vue';
import { cn } from '@/lib/utils';

const emblaRef = inject<Ref>('emblaRef');
const orientation = inject<string>('orientation', 'horizontal');
</script>

<template>
  <div :ref="emblaRef" class="overflow-hidden">
    <div :class="cn('flex', orientation === 'horizontal' ? '-ml-4' : '-mt-4 flex-col')">
      <slot />
    </div>
  </div>
</template>
