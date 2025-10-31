<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative">
    <!-- Popular Badge -->
    <div v-if="plan.slug === 'premium-pet-owner' || plan.slug === 'professional-clinic'" 
         class="absolute -top-3 left-1/2 transform -translate-x-1/2">
      <span class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-1 rounded-full text-sm font-medium">
        Most Popular
      </span>
    </div>

    <!-- Plan Header -->
    <div class="text-center mb-6">
      <h3 class="text-xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
      <p class="text-gray-600 text-sm">{{ plan.description }}</p>
    </div>

    <!-- Pricing -->
    <div class="text-center mb-6">
      <div class="flex items-baseline justify-center mb-2">
        <span class="text-4xl font-bold text-gray-900">₱{{ formatPrice(currentPrice) }}</span>
        <span class="text-gray-500 ml-2">/{{ billingCycle }}</span>
      </div>
      
      <!-- Annual Savings -->
      <div v-if="plan.annual_price && plan.annual_savings_percentage" class="mb-4">
        <button 
          @click="toggleBillingCycle"
          class="text-sm text-blue-600 hover:text-blue-800 font-medium">
          Save {{ plan.annual_savings_percentage }}% with annual billing
        </button>
      </div>

      <!-- Billing Toggle -->
      <div v-if="plan.annual_price" class="flex items-center justify-center space-x-3 mb-4">
        <span :class="{ 'text-gray-500': isAnnual, 'text-gray-900 font-medium': !isAnnual }">
          Monthly
        </span>
        <button
          @click="toggleBillingCycle"
          :class="[
            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
            isAnnual ? 'bg-blue-600' : 'bg-gray-200'
          ]">
          <span
            :class="[
              'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
              isAnnual ? 'translate-x-6' : 'translate-x-1'
            ]" />
        </button>
        <span :class="{ 'text-gray-900 font-medium': isAnnual, 'text-gray-500': !isAnnual }">
          Annual
        </span>
      </div>

      <!-- Trial Period -->
      <div v-if="plan.trial_days > 0" class="text-sm text-green-600 font-medium">
        {{ plan.trial_days }}-day free trial
      </div>
    </div>

    <!-- Features List -->
    <div class="mb-6">
      <ul class="space-y-3">
        <li v-for="feature in displayFeatures" :key="feature.key" class="flex items-start">
          <CheckIcon class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" />
          <span class="text-gray-700 text-sm">{{ feature.label }}</span>
        </li>
      </ul>

      <!-- Limits -->
      <div v-if="displayLimits.length > 0" class="mt-4 pt-4 border-t border-gray-100">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Plan Limits</h4>
        <ul class="space-y-1">
          <li v-for="limit in displayLimits" :key="limit.key" class="text-xs text-gray-600">
            {{ limit.label }}: {{ limit.value }}
          </li>
        </ul>
      </div>
    </div>

    <!-- CTA Button -->
    <div class="space-y-3">
      <button
        @click="selectPlan"
        :disabled="isCurrentPlan || loading"
        :class="[
          'w-full py-3 px-4 rounded-lg font-medium transition-colors',
          isCurrentPlan 
            ? 'bg-gray-100 text-gray-500 cursor-not-allowed'
            : plan.slug === 'premium-pet-owner' || plan.slug === 'professional-clinic'
              ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700'
              : 'bg-blue-600 text-white hover:bg-blue-700',
          loading && 'opacity-50 cursor-not-allowed'
        ]">
        <span v-if="loading" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
        <span v-else-if="isCurrentPlan">Current Plan</span>
        <span v-else-if="plan.price === 0">Get Started Free</span>
        <span v-else>
          Start {{ plan.trial_days > 0 ? 'Free Trial' : 'Subscription' }}
        </span>
      </button>

      <!-- Transaction Fee Notice (for clinics) -->
      <div v-if="plan.type === 'clinic' && plan.transaction_fee_percentage" 
           class="text-xs text-gray-500 text-center">
        + {{ plan.transaction_fee_percentage }}% transaction fee on paid appointments
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { CheckIcon } from '@heroicons/vue/24/solid'

interface SubscriptionPlan {
  id: number
  name: string
  slug: string
  type: 'user' | 'clinic'
  description: string
  price: number
  annual_price?: number
  features: string[]
  limits: Record<string, number>
  is_active: boolean
  trial_days: number
  transaction_fee_percentage?: number
  annual_savings_percentage?: number
}

interface Props {
  plan: SubscriptionPlan
  isCurrentPlan?: boolean
  userType: 'user' | 'clinic'
}

const props = withDefaults(defineProps<Props>(), {
  isCurrentPlan: false
})

const emit = defineEmits<{
  selectPlan: [plan: SubscriptionPlan, isAnnual: boolean]
}>()

const isAnnual = ref(false)
const loading = ref(false)

const currentPrice = computed(() => {
  if (isAnnual.value && props.plan.annual_price) {
    return props.plan.annual_price
  }
  return props.plan.price
})

const billingCycle = computed(() => {
  return isAnnual.value ? 'year' : 'month'
})

const displayFeatures = computed(() => {
  const featureLabels: Record<string, string> = {
    // Pet Owner Features
    basic_pet_profiles: 'Up to 2 pet profiles',
    unlimited_pets: 'Unlimited pet profiles',
    standard_appointment_booking: 'Standard appointment booking',
    priority_booking: 'Priority appointment booking',
    basic_health_records: 'Basic health records',
    advanced_health_tracking: 'Advanced health tracking & alerts',
    community_access: 'Community access',
    telemedicine: 'Telemedicine consultations',
    health_reports: 'Detailed health reports',
    export_records: 'Export medical records',
    vaccination_reminders: 'Automated vaccination reminders',
    medical_history_timeline: 'Medical history timeline',
    emergency_contact_alerts: 'Emergency contact alerts',
    
    // Clinic Features
    profile_listing: 'Clinic profile listing',
    basic_calendar: 'Basic calendar management',
    unlimited_appointments: 'Unlimited appointments',
    advanced_scheduling: 'Advanced scheduling features',
    standard_reviews: 'Customer reviews',
    basic_patient_management: 'Basic patient management',
    staff_management: 'Staff management (5 users)',
    detailed_analytics: 'Detailed analytics dashboard',
    custom_forms: 'Custom appointment forms',
    priority_listing: 'Priority search listing',
    inventory_management: 'Inventory management',
    automated_reminders: 'Automated patient reminders',
    financial_reporting: 'Financial reporting',
    multi_location: 'Multi-location management',
    unlimited_staff: 'Unlimited staff accounts',
    api_access: 'API access for integrations',
    white_label_app: 'White-label mobile app',
    advanced_reporting: 'Advanced reporting suite',
    dedicated_support: 'Dedicated support',
    custom_integrations: 'Custom integrations',
    advanced_analytics: 'Advanced analytics',
    compliance_tools: 'Compliance tools',
    multi_currency: 'Multi-currency support',
  }

  return props.plan.features.map(feature => ({
    key: feature,
    label: featureLabels[feature] || feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
  }))
})

const displayLimits = computed(() => {
  const limitLabels: Record<string, string> = {
    max_pets: 'Maximum pets',
    max_appointments_per_month: 'Monthly appointments',
    max_staff_accounts: 'Staff accounts',
    max_locations: 'Locations',
    storage_mb: 'Storage (MB)',
  }

  return Object.entries(props.plan.limits)
    .filter(([_, value]) => value !== -1) // Filter out unlimited (-1) values
    .map(([key, value]) => ({
      key,
      label: limitLabels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
      value: value === -1 ? 'Unlimited' : value.toLocaleString()
    }))
})

const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 0 })
}

const toggleBillingCycle = () => {
  if (props.plan.annual_price) {
    isAnnual.value = !isAnnual.value
  }
}

const selectPlan = async () => {
  if (props.isCurrentPlan || loading.value) return
  
  loading.value = true
  try {
    emit('selectPlan', props.plan, isAnnual.value)
  } finally {
    loading.value = false
  }
}
</script>