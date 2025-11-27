<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits, withDefaults } from 'vue';

// Debounce utility function
function debounce(func: Function, wait: number) {
    let timeout: number;
    return function executedFunction(...args: any[]) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait) as unknown as number;
    };
}

// Filter interface
interface FilterOptions {
    search?: string;
    service?: string[];  // Changed to array for multiple selections
    rating?: string[];   // Changed to array for multiple selections
    distance?: string;   // Keep as single selection (radius)
    status?: string[];   // Changed to array for multiple selections
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

// Handle checkbox changes for multi-select filters
const handleServiceChange = (value: string, checked: boolean) => {
    if (!localFilters.value.service) localFilters.value.service = [];
    if (checked) {
        if (!localFilters.value.service.includes(value)) {
            localFilters.value.service.push(value);
        }
    } else {
        localFilters.value.service = localFilters.value.service.filter(v => v !== value);
    }
    updateModelValue();
};

const handleRatingChange = (value: string, checked: boolean) => {
    if (!localFilters.value.rating) localFilters.value.rating = [];
    if (checked) {
        if (!localFilters.value.rating.includes(value)) {
            localFilters.value.rating.push(value);
        }
    } else {
        localFilters.value.rating = localFilters.value.rating.filter(v => v !== value);
    }
    updateModelValue();
};

const handleStatusChange = (value: string, checked: boolean) => {
    if (!localFilters.value.status) localFilters.value.status = [];
    if (checked) {
        if (!localFilters.value.status.includes(value)) {
            localFilters.value.status.push(value);
        }
    } else {
        localFilters.value.status = localFilters.value.status.filter(v => v !== value);
    }
    updateModelValue();
};

// Service options
const serviceOptions = [
    { value: '', label: 'All categories' },
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
    { value: 'emergency', label: 'Emergency clinics' },
];

// Apply filters
// Clear all filters
const clearFilters = () => {
    localFilters.value = {
        search: '',
        service: [],
        rating: [],
        distance: '',
        status: [],
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
    return localFilters.value.search !== '' ||
           (localFilters.value.service && localFilters.value.service.length > 0) ||
           (localFilters.value.rating && localFilters.value.rating.length > 0) ||
           localFilters.value.distance !== '' ||
           (localFilters.value.status && localFilters.value.status.length > 0);
});

// Form styles
const inputClasses = "w-full px-3 py-2 border border-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-800 text-gray-100";
const labelClasses = "block text-sm font-medium text-gray-300 mb-2";
</script>

<template>
    <div class="bg-gray-900 dark:bg-gray-900 rounded-xl border border-gray-800 dark:border-gray-700 p-5">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filters
        </h3>
        
        <div class="space-y-6 mb-6">
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

            <!-- Category Filter -->
            <div v-if="showService">
                <label :class="labelClasses">Category</label>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    <label v-for="option in serviceOptions.filter(o => o.value !== '')" :key="option.value" 
                           class="flex items-center space-x-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            :value="option.value"
                            :checked="localFilters.service?.includes(option.value) || false"
                            @change="handleServiceChange(option.value, ($event.target as HTMLInputElement).checked)"
                            class="rounded border-gray-600 bg-gray-800 text-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-300">{{ option.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Rating Filter -->
            <div v-if="showRating">
                <label :class="labelClasses">Minimum rating</label>
                <div class="space-y-2">
                    <label v-for="option in ratingOptions.filter(o => o.value !== '')" :key="option.value" 
                           class="flex items-center space-x-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            :value="option.value"
                            :checked="localFilters.rating?.includes(option.value) || false"
                            @change="handleRatingChange(option.value, ($event.target as HTMLInputElement).checked)"
                            class="rounded border-gray-600 bg-gray-800 text-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-300">{{ option.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Distance Filter (Keep as dropdown for single selection) -->
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
                <div class="space-y-2">
                    <label v-for="option in statusOptions.filter(o => o.value !== '')" :key="option.value" 
                           class="flex items-center space-x-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            :value="option.value"
                            :checked="localFilters.status?.includes(option.value) || false"
                            @change="handleStatusChange(option.value, ($event.target as HTMLInputElement).checked)"
                            class="rounded border-gray-600 bg-gray-800 text-blue-500 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-300">{{ option.label }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="flex gap-2 flex-1">
                <button 
                    @click="clearFilters" 
                    class="flex-1 px-4 py-2 text-gray-300 border border-gray-700 rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-sm font-medium transition-colors"
                >
                    Clear
                </button>
            </div>
            
            <!-- View on Map Button -->
            <button 
                v-if="showViewOnMapButton"
                @click="handleViewOnMap" 
                class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm font-medium flex items-center justify-center gap-2 whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="hidden sm:inline">View on Map</span>
                <span class="sm:hidden">Map</span>
            </button>
        </div>

        <!-- Result Count -->
        <div v-if="resultCount !== undefined" class="mt-4 text-sm text-gray-400">
            {{ resultCount }} clinic{{ resultCount !== 1 ? 's' : '' }} found
            <span v-if="hasActiveFilters" class="text-blue-400">
                (filtered)
            </span>
        </div>

        <!-- Location Status -->
        <div v-if="showLocationStatus" class="mt-4 p-3 bg-gray-800 rounded-lg border border-gray-700">
            <div v-if="hasUserLocation" class="flex items-center gap-2 text-sm text-green-400">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                <span>Location enabled - showing distances and nearby clinics</span>
            </div>
            <div v-else class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Enable location for distance calculations</span>
                </div>
                <button 
                    @click="handleRequestLocation" 
                    class="px-3 py-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-sm hover:from-blue-700 hover:to-purple-700 transition-all"
                >
                    Enable Location
                </button>
            </div>
        </div>

        <!-- Active Filters Summary -->
        <div v-if="hasActiveFilters" class="mt-4 p-3 bg-blue-900/20 rounded-lg border border-blue-800">
            <h4 class="text-sm font-medium text-blue-300 mb-2">Active Filters:</h4>
            <div class="flex flex-wrap gap-2">
                <span v-if="localFilters.search" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-800 text-blue-100">
                    Search: {{ localFilters.search }}
                </span>
                <span v-if="localFilters.service && localFilters.service.length > 0" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-800 text-blue-100">
                    Service: {{ localFilters.service.map(s => serviceOptions.find(opt => opt.value === s)?.label).filter(Boolean).join(', ') }}
                </span>
                <span v-if="localFilters.rating && localFilters.rating.length > 0" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-800 text-blue-100">
                    Rating: {{ localFilters.rating.map(r => ratingOptions.find(opt => opt.value === r)?.label).filter(Boolean).join(', ') }}
                </span>
                <span v-if="localFilters.distance" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-800 text-blue-100">
                    Distance: {{ distanceOptions.find(d => d.value === localFilters.distance)?.label }}
                </span>
                <span v-if="localFilters.status && localFilters.status.length > 0" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-800 text-blue-100">
                    Status: {{ localFilters.status.map(s => statusOptions.find(opt => opt.value === s)?.label).filter(Boolean).join(', ') }}
                </span>
            </div>
        </div>
    </div>
</template>