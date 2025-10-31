<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits, withDefaults } from 'vue';

// Debounce utility function
function debounce(func: Function, wait: number) {
    let timeout: NodeJS.Timeout;
    return function executedFunction(...args: any[]) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Filter interface
interface FilterOptions {
    search?: string;
    service?: string;
    rating?: string;
    region?: string;
    distance?: string;
    status?: string;
}

interface Props {
    // Current filter values
    modelValue: FilterOptions;
    // Show/hide specific filters
    showSearch?: boolean;
    showService?: boolean;
    showRating?: boolean;
    showRegion?: boolean;
    showDistance?: boolean;
    showStatus?: boolean;
    // Additional configuration
    showViewOnMapButton?: boolean;
    showLocationStatus?: boolean;
    hasUserLocation?: boolean;
    resultCount?: number;
    // Custom button handlers
    onViewOnMap?: () => void;
    onRequestLocation?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    showSearch: true,
    showService: true,
    showRating: true,
    showRegion: true,
    showDistance: true,
    showStatus: true,
    showViewOnMapButton: false,
    showLocationStatus: false,
    hasUserLocation: false,
    resultCount: 0,
});

const emit = defineEmits<{
    'update:modelValue': [value: FilterOptions];
    'clear': [];
    'viewOnMap': [];
    'requestLocation': [];
}>();

// Local reactive copies of filter values
const localFilters = ref<FilterOptions>({ ...props.modelValue });

// Watch for external changes to modelValue (only update if different)
watch(() => props.modelValue, (newValue) => {
    // Only update if values are actually different to prevent loops
    if (JSON.stringify(localFilters.value) !== JSON.stringify(newValue)) {
        localFilters.value = { ...newValue };
    }
}, { deep: true });

// Function to update model value (called explicitly instead of watching)
const updateModelValue = debounce(() => {
    emit('update:modelValue', { ...localFilters.value });
}, 150); // 150ms debounce for better performance

// Service options
const serviceOptions = [
    { value: '', label: 'All services' },
    { value: 'consultation', label: 'Consultation' },
    { value: 'vaccination', label: 'Vaccination' },
    { value: 'surgery', label: 'Surgery' },
    { value: 'emergency', label: 'Emergency' },
    { value: 'grooming', label: 'Grooming' },
    { value: 'boarding', label: 'Boarding' },
    { value: 'wellness', label: 'Wellness' },
    { value: 'laboratory', label: 'Laboratory' },
    { value: 'marine', label: 'Marine Care' },
    { value: 'livestock', label: 'Livestock' },
    { value: 'aquatic', label: 'Aquatic' },
    { value: 'traditional care', label: 'Traditional Care' },
    { value: 'disaster response', label: 'Disaster Response' },
];

// Rating options
const ratingOptions = [
    { value: '', label: 'Any rating' },
    { value: '5', label: '5 stars' },
    { value: '4', label: '4+ stars' },
    { value: '3', label: '3+ stars' },
    { value: '2', label: '2+ stars' },
];

// Region options
const regionOptions = [
    { value: '', label: 'All regions' },
    { value: 'Metro Manila', label: 'Metro Manila' },
    { value: 'Central Visayas', label: 'Central Visayas' },
    { value: 'Davao Region', label: 'Davao Region' },
    { value: 'Cordillera', label: 'Cordillera' },
    { value: 'Western Visayas', label: 'Western Visayas' },
    { value: 'Cagayan Valley', label: 'Cagayan Valley' },
    { value: 'MIMAROPA', label: 'MIMAROPA' },
    { value: 'CALABARZON', label: 'CALABARZON' },
    { value: 'Bicol Region', label: 'Bicol Region' },
    { value: 'Central Luzon', label: 'Central Luzon' },
    { value: 'Eastern Visayas', label: 'Eastern Visayas' },
    { value: 'Caraga', label: 'Caraga' },
    { value: 'Northern Mindanao', label: 'Northern Mindanao' },
    { value: 'BARMM', label: 'BARMM' },
    { value: 'SOCCSKSARGEN', label: 'SOCCSKSARGEN' },
    { value: 'Ilocos Region', label: 'Ilocos Region' },
];

// Distance options
const distanceOptions = [
    { value: '', label: 'Any distance' },
    { value: '2', label: 'Within 2 km' },
    { value: '5', label: 'Within 5 km' },
    { value: '10', label: 'Within 10 km' },
    { value: '20', label: 'Within 20 km' },
    { value: '50', label: 'Within 50 km' },
];

// Status options
const statusOptions = [
    { value: '', label: 'All clinics' },
    { value: 'open', label: 'Open now' },
    { value: 'closed', label: 'Closed' },
    { value: '24_7', label: 'Open 24/7' },
];

// Apply filters
// Clear all filters
const clearFilters = () => {
    localFilters.value = {
        search: '',
        service: '',
        rating: '',
        region: '',
        distance: '',
        status: '',
    };
    updateModelValue();
    emit('clear');
};

// View on map handler
const handleViewOnMap = () => {
    if (props.onViewOnMap) {
        props.onViewOnMap();
    } else {
        emit('viewOnMap');
    }
};

// Request location handler
const handleRequestLocation = () => {
    if (props.onRequestLocation) {
        props.onRequestLocation();
    } else {
        emit('requestLocation');
    }
};

// Check if any filter is active
const hasActiveFilters = computed(() => {
    return Object.values(localFilters.value).some(value => value && value !== '');
});

// Form styles
const inputClasses = "w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100";
const labelClasses = "block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2";
</script>

<template>
    <div class="bg-white rounded-xl border border-sidebar-border/70 dark:border-sidebar-border dark:bg-gray-800 p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Filters</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mb-6">
            <!-- Search Filter -->
            <div v-if="showSearch">
                <label :class="labelClasses">Search</label>
                <input 
                    v-model="localFilters.search"
                    @input="updateModelValue"
                    type="text" 
                    placeholder="Search clinics..."
                    :class="inputClasses"
                />
            </div>

            <!-- Service Filter -->
            <div v-if="showService">
                <label :class="labelClasses">Service</label>
                <select v-model="localFilters.service" @change="updateModelValue" :class="inputClasses">
                    <option v-for="option in serviceOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Rating Filter -->
            <div v-if="showRating">
                <label :class="labelClasses">Minimum rating</label>
                <select v-model="localFilters.rating" @change="updateModelValue" :class="inputClasses">
                    <option v-for="option in ratingOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Region Filter -->
            <div v-if="showRegion">
                <label :class="labelClasses">Region</label>
                <select v-model="localFilters.region" @change="updateModelValue" :class="inputClasses">
                    <option v-for="option in regionOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Distance Filter -->
            <div v-if="showDistance">
                <label :class="labelClasses">Distance</label>
                <select v-model="localFilters.distance" @change="updateModelValue" :class="inputClasses">
                    <option v-for="option in distanceOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Status Filter -->
            <div v-if="showStatus">
                <label :class="labelClasses">Status</label>
                <select v-model="localFilters.status" @change="updateModelValue" :class="inputClasses">
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="flex gap-2 flex-1">
                <button 
                    @click="clearFilters" 
                    class="flex-1 px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 text-sm font-medium"
                >
                    Clear
                </button>
            </div>
            
            <!-- View on Map Button -->
            <button 
                v-if="showViewOnMapButton"
                @click="handleViewOnMap" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium flex items-center justify-center gap-2 whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="hidden sm:inline">View on Map</span>
                <span class="sm:hidden">Map</span>
            </button>
        </div>

        <!-- Result Count -->
        <div v-if="resultCount !== undefined" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            {{ resultCount }} clinic{{ resultCount !== 1 ? 's' : '' }} found
            <span v-if="hasActiveFilters" class="text-blue-600 dark:text-blue-400">
                (filtered)
            </span>
        </div>

        <!-- Location Status -->
        <div v-if="showLocationStatus" class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div v-if="hasUserLocation" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                <span>Location enabled - showing distances and nearby clinics</span>
            </div>
            <div v-else class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Enable location for distance calculations</span>
                </div>
                <button 
                    @click="handleRequestLocation" 
                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors"
                >
                    Enable Location
                </button>
            </div>
        </div>

        <!-- Active Filters Summary -->
        <div v-if="hasActiveFilters" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Active Filters:</h4>
            <div class="flex flex-wrap gap-2">
                <span v-if="localFilters.search" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    Search: {{ localFilters.search }}
                </span>
                <span v-if="localFilters.service" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    Service: {{ serviceOptions.find(s => s.value === localFilters.service)?.label }}
                </span>
                <span v-if="localFilters.rating" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    Rating: {{ ratingOptions.find(r => r.value === localFilters.rating)?.label }}
                </span>
                <span v-if="localFilters.region" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    Region: {{ localFilters.region }}
                </span>
                <span v-if="localFilters.distance" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    Distance: {{ distanceOptions.find(d => d.value === localFilters.distance)?.label }}
                </span>
                <span v-if="localFilters.status" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                    Status: {{ statusOptions.find(s => s.value === localFilters.status)?.label }}
                </span>
            </div>
        </div>
    </div>
</template>