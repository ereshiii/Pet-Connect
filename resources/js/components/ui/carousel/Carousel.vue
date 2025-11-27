<script setup lang="ts">
import { provide, ref, onMounted, watch } from 'vue';
import emblaCarouselVue from 'embla-carousel-vue';
import type { EmblaCarouselType } from 'embla-carousel';
import { cn } from '@/lib/utils';

interface Props {
  opts?: any;
  plugins?: any[];
  orientation?: 'horizontal' | 'vertical';
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'horizontal',
  opts: () => ({}),
  plugins: () => [],
});

const [emblaRef, emblaApi] = emblaCarouselVue(
  {
    ...props.opts,
    axis: props.orientation === 'horizontal' ? 'x' : 'y',
  },
  props.plugins
);

const canScrollPrev = ref(false);
const canScrollNext = ref(false);

const onSelect = (api: EmblaCarouselType) => {
  if (!api) return;
  canScrollPrev.value = api.canScrollPrev();
  canScrollNext.value = api.canScrollNext();
};

const scrollPrev = () => {
  emblaApi.value?.scrollPrev();
};

const scrollNext = () => {
  emblaApi.value?.scrollNext();
};

onMounted(() => {
  if (!emblaApi.value) return;
  
  onSelect(emblaApi.value);
  emblaApi.value.on('reInit', onSelect);
  emblaApi.value.on('select', onSelect);
});

provide('emblaRef', emblaRef);
provide('emblaApi', emblaApi);
provide('canScrollPrev', canScrollPrev);
provide('canScrollNext', canScrollNext);
provide('scrollPrev', scrollPrev);
provide('scrollNext', scrollNext);
provide('orientation', props.orientation);
</script>

<template>
  <div class="relative" role="region" aria-roledescription="carousel">
    <slot />
  </div>
</template>
