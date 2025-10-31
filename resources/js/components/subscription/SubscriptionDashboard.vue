<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Subscription Plans</h1>
        <p class="mt-2 text-lg text-gray-600">
          Choose the perfect plan for your {{ userType === 'clinic' ? 'veterinary practice' : 'pet care needs' }}
        </p>
      </div>

      <!-- Current Subscription Status -->
      <div v-if="currentSubscription" class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Current Subscription</h2>
            <p class="text-gray-600">{{ currentSubscription.name }}</p>
            <p class="text-sm text-gray-500 mt-1">
              Next billing: {{ formatDate(currentSubscription.current_period_end) }}
            </p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-gray-900">
              ₱{{ formatPrice(currentSubscription.price) }}
            </div>
            <div class="text-sm text-gray-500">
              /{{ currentSubscription.interval }}
            </div>
          </div>
        </div>
        
        <!-- Usage Stats (for clinics) -->
        <div v-if="userType === 'clinic' && usageStats" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">This Month's Appointments</div>
            <div class="text-xl font-semibold text-gray-900">
              {{ usageStats.appointments_this_month }}
              <span v-if="currentPlan?.limits.max_appointments_per_month !== -1" class="text-sm text-gray-500">
                / {{ currentPlan?.limits.max_appointments_per_month }}
              </span>
            </div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Staff Accounts</div>
            <div class="text-xl font-semibold text-gray-900">
              {{ usageStats.staff_accounts }}
              <span v-if="currentPlan?.limits.max_staff_accounts !== -1" class="text-sm text-gray-500">
                / {{ currentPlan?.limits.max_staff_accounts }}
              </span>
            </div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Storage Used</div>
            <div class="text-xl font-semibold text-gray-900">
              {{ Math.round(usageStats.storage_used_mb) }} MB
              <span v-if="currentPlan?.limits.storage_mb !== -1" class="text-sm text-gray-500">
                / {{ currentPlan?.limits.storage_mb }} MB
              </span>
            </div>
          </div>
        </div>

        <!-- Pet Owner Usage -->
        <div v-if="userType === 'user' && usageStats" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">Your Pets</div>
            <div class="text-xl font-semibold text-gray-900">
              {{ usageStats.pets_count }}
              <span v-if="currentPlan?.limits.max_pets !== -1" class="text-sm text-gray-500">
                / {{ currentPlan?.limits.max_pets }}
              </span>
            </div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-600">This Month's Appointments</div>
            <div class="text-xl font-semibold text-gray-900">
              {{ usageStats.appointments_this_month }}
              <span v-if="currentPlan?.limits.max_appointments_per_month !== -1" class="text-sm text-gray-500">
                / {{ currentPlan?.limits.max_appointments_per_month }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Plan Type Toggle -->
      <div class="mb-8 flex justify-center">
        <div class="bg-white rounded-lg p-1 shadow-sm border border-gray-200">
          <button
            @click="planType = 'user'"
            :class="[
              'px-6 py-2 rounded-md text-sm font-medium transition-colors',
              planType === 'user' 
                ? 'bg-blue-600 text-white' 
                : 'text-gray-700 hover:text-gray-900'
            ]">
            Pet Owner Plans
          </button>
          <button
            @click="planType = 'clinic'"
            :class="[
              'px-6 py-2 rounded-md text-sm font-medium transition-colors',
              planType === 'clinic' 
                ? 'bg-blue-600 text-white' 
                : 'text-gray-700 hover:text-gray-900'
            ]">
            Clinic Plans
          </button>
        </div>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
        <SubscriptionPlanCard
          v-for="plan in filteredPlans"
          :key="plan.id"
          :plan="plan"
          :is-current-plan="currentSubscription?.stripe_price === plan.stripe_price_id"
          :user-type="userType"
          @select-plan="handlePlanSelection"
        />
      </div>

      <!-- Feature Comparison -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Feature Comparison</h2>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="text-left py-3 px-4 font-medium text-gray-900">Feature</th>
                <th v-for="plan in filteredPlans" :key="plan.id" 
                    class="text-center py-3 px-4 font-medium text-gray-900">
                  {{ plan.name }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="feature in allFeatures" :key="feature" 
                  class="border-b border-gray-100">
                <td class="py-3 px-4 text-sm text-gray-700">
                  {{ formatFeatureName(feature) }}
                </td>
                <td v-for="plan in filteredPlans" :key="plan.id" 
                    class="text-center py-3 px-4">
                  <CheckIcon v-if="plan.features.includes(feature)" 
                           class="h-5 w-5 text-green-500 mx-auto" />
                  <XMarkIcon v-else class="h-5 w-5 text-gray-300 mx-auto" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <PaymentModal
      v-if="showPaymentModal"
      :plan="selectedPlan"
      :is-annual="selectedIsAnnual"
      @close="showPaymentModal = false"
      @success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/solid'
import SubscriptionPlanCard from './SubscriptionPlanCard.vue'
import PaymentModal from './PaymentModal.vue'

interface SubscriptionPlan {
  id: number
  name: string
  slug: string
  type: 'user' | 'clinic'
  description: string
  price: number
  annual_price?: number
  stripe_price_id?: string
  features: string[]
  limits: Record<string, number>
  is_active: boolean
  trial_days: number
  transaction_fee_percentage?: number
  annual_savings_percentage?: number
}

interface CurrentSubscription {
  name: string
  price: number
  interval: string
  current_period_end: string
  stripe_price: string
}

interface UsageStats {
  pets_count?: number
  appointments_this_month: number
  staff_accounts?: number
  storage_used_mb: number
}

interface Props {
  plans: SubscriptionPlan[]
  currentSubscription?: CurrentSubscription
  userType: 'user' | 'clinic'
  usageStats?: UsageStats
}

const props = defineProps<Props>()

const planType = ref<'user' | 'clinic'>(props.userType)
const showPaymentModal = ref(false)
const selectedPlan = ref<SubscriptionPlan | null>(null)
const selectedIsAnnual = ref(false)

const filteredPlans = computed(() => {
  return props.plans
    .filter(plan => plan.type === planType.value && plan.is_active)
    .sort((a, b) => a.sort_order - b.sort_order)
})

const currentPlan = computed(() => {
  if (!props.currentSubscription) return null
  return props.plans.find(plan => 
    plan.stripe_price_id === props.currentSubscription?.stripe_price ||
    plan.stripe_annual_price_id === props.currentSubscription?.stripe_price
  )
})

const allFeatures = computed(() => {
  const features = new Set<string>()
  filteredPlans.value.forEach(plan => {
    plan.features.forEach(feature => features.add(feature))
  })
  return Array.from(features).sort()
})

const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 0 })
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatFeatureName = (feature: string): string => {
  const featureLabels: Record<string, string> = {
    basic_pet_profiles: 'Basic Pet Profiles',
    unlimited_pets: 'Unlimited Pets',
    standard_appointment_booking: 'Standard Appointment Booking',
    priority_booking: 'Priority Appointment Booking',
    advanced_health_tracking: 'Advanced Health Tracking',
    telemedicine: 'Telemedicine Consultations',
    health_reports: 'Health Reports & Analytics',
    export_records: 'Export Medical Records',
    unlimited_appointments: 'Unlimited Appointments',
    advanced_scheduling: 'Advanced Scheduling',
    staff_management: 'Staff Management',
    detailed_analytics: 'Detailed Analytics',
    custom_forms: 'Custom Forms',
    priority_listing: 'Priority Search Listing',
    multi_location: 'Multi-Location Management',
    api_access: 'API Access',
    white_label_app: 'White-Label App',
  }
  
  return featureLabels[feature] || feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const handlePlanSelection = (plan: SubscriptionPlan, isAnnual: boolean) => {
  selectedPlan.value = plan
  selectedIsAnnual.value = isAnnual
  
  if (plan.price === 0) {
    // Handle free plan activation
    handleFreePlanActivation(plan)
  } else {
    // Show payment modal for paid plans
    showPaymentModal.value = true
  }
}

const handleFreePlanActivation = async (plan: SubscriptionPlan) => {
  try {
    // Implementation for free plan activation
    console.log('Activating free plan:', plan.name)
  } catch (error) {
    console.error('Error activating free plan:', error)
  }
}

const handlePaymentSuccess = () => {
  showPaymentModal.value = false
  // Reload page or update current subscription
  window.location.reload()
}

onMounted(() => {
  // Set plan type based on user type
  planType.value = props.userType
})
</script>