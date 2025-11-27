<script setup lang="ts">
import { inject, type Ref } from 'vue';
import { Button } from '@/components/ui/button';
import { ChevronRight } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

interface Props {
  variant?: 'outline' | 'default' | 'ghost' | 'destructive' | 'secondary' | 'link';
  size?: 'default' | 'sm' | 'lg' | 'icon';
}

withDefaults(defineProps<Props>(), {
  variant: 'outline',
  size: 'icon',
});

const orientation = inject<string>('orientation', 'horizontal');
const canScrollNext = inject<Ref<boolean>>('canScrollNext');
const scrollNext = inject<() => void>('scrollNext');
</script>

<template>
  <Button
    :variant="variant"
    :size="size"
    :class="cn(
      'absolute h-8 w-8 rounded-full z-10',
      orientation === 'horizontal'
        ? 'right-4 top-1/2 -translate-y-1/2'
        : 'bottom-4 left-1/2 -translate-x-1/2 rotate-90',
      $attrs.class
    )"
    :disabled="!canScrollNext"
    @click="scrollNext"
  >
    <ChevronRight class="h-4 w-4" />
    <span class="sr-only">Next slide</span>
  </Button>
</template>
