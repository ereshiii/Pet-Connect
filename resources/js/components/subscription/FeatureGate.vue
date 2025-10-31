<template>
  <div>
    <!-- Content when feature is available -->
    <slot v-if="hasFeature" name="default" />
    
    <!-- Upgrade prompt when feature is not available -->
    <div v-else-if="showUpgradePrompt">
      <slot name="upgrade-prompt">
        <div class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-6 text-center">
          <div class="mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
              <StarIcon class="h-6 w-6 text-white" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              {{ upgradeTitle }}
            </h3>
            <p class="text-gray-600 mb-4">
              {{ upgradeMessage }}
            </p>
          </div>
          
          <div class="space-y-3">
            <button 
              @click="$emit('upgrade')"
              class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-2 px-4 rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition-colors">
              {{ upgradeButtonText }}
            </button>
            <button 
              v-if="allowDismiss"
              @click="dismissed = true"
              class="text-sm text-gray-500 hover:text-gray-700">
              Maybe later
            </button>
          </div>
        </div>
      </slot>
    </div>
    
    <!-- Fallback content when feature is not available and no upgrade prompt -->
    <slot v-else name="fallback">
      <div class="text-gray-500 text-sm italic">
        This feature is not available on your current plan.
      </div>
    </slot>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { StarIcon } from '@heroicons/vue/24/solid'

interface Props {
  feature: string
  upgradeTitle?: string
  upgradeMessage?: string
  upgradeButtonText?: string
  showUpgradePrompt?: boolean
  allowDismiss?: boolean
  fallbackBehavior?: 'hide' | 'show-prompt' | 'show-fallback'
}

const props = withDefaults(defineProps<Props>(), {
  upgradeTitle: 'Upgrade to Premium',
  upgradeMessage: 'This feature is available in our premium plans.',
  upgradeButtonText: 'Upgrade Now',
  showUpgradePrompt: true,
  allowDismiss: true,
  fallbackBehavior: 'show-prompt'
})

const emit = defineEmits<{
  upgrade: []
}>()

const hasFeature = ref(false)
const dismissed = ref(false)
const loading = ref(true)

const showUpgradePrompt = computed(() => {
  return props.showUpgradePrompt && !dismissed.value && props.fallbackBehavior !== 'hide'
})

// Check if user has the feature
const checkFeature = async () => {
  try {
    const response = await fetch(`/api/features/${props.feature}/check`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const data = await response.json()
      hasFeature.value = data.hasFeature
    } else {
      console.error('Failed to check feature:', response.statusText)
      hasFeature.value = false
    }
  } catch (error) {
    console.error('Error checking feature:', error)
    hasFeature.value = false
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  checkFeature()
})

// Expose method to recheck feature (useful after subscription changes)
const recheckFeature = () => {
  loading.value = true
  checkFeature()
}

defineExpose({
  recheckFeature,
  hasFeature: computed(() => hasFeature.value)
})
</script>