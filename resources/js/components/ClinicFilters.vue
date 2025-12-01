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
const inputClasses = "w-full px-3 py-2 border border-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground";
const labelClasses = "block text-sm font-medium text-foreground mb-2";
</script>

<template>
    <div class="bg-card rounded-xl border border-border p-4 sticky top-4 max-h-[calc(100vh-2rem)] overflow-y-auto">
        <h3 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filters
        </h3>
        
        <div class="space-y-4 mb-4">
            <!-- Search Filter -->
            <div v-if="showSearch">
                <label class="block text-xs font-medium text-foreground mb-1.5">Search</label>
                <input 
                    v-model="localFilters.search"
                    @input="updateModelValue"
                    type="text" 
                    placeholder="Search clinics..."
                    class="w-full px-3 py-2 border border-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground text-sm"
                />
            </div>

            <!-- Category Filter -->
            <div v-if="showService">
                <label class="block text-xs font-medium text-foreground mb-1.5">Category</label>
                <div class="space-y-1.5 max-h-40 overflow-y-auto">
                    <label v-for="option in serviceOptions.filter(o => o.value !== '')" :key="option.value" 
                           class="flex items-center space-x-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            :value="option.value"
                            :checked="localFilters.service?.includes(option.value) || false"
                            @change="handleServiceChange(option.value, ($event.target as HTMLInputElement).checked)"
                            class="rounded border-border bg-background text-primary focus:ring-primary"
                        />
                        <span class="text-xs text-foreground">{{ option.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Rating Filter -->
            <div v-if="showRating">
                <label class="block text-xs font-medium text-foreground mb-1.5">Minimum rating</label>
                <div class="space-y-1.5">
                    <label v-for="option in ratingOptions.filter(o => o.value !== '')" :key="option.value" 
                           class="flex items-center space-x-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            :value="option.value"
                            :checked="localFilters.rating?.includes(option.value) || false"
                            @change="handleRatingChange(option.value, ($event.target as HTMLInputElement).checked)"
                            class="rounded border-border bg-background text-primary focus:ring-primary"
                        />
                        <span class="text-xs text-foreground">{{ option.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Distance Filter -->
            <div v-if="showDistance">
                <label class="block text-xs font-medium text-foreground mb-1.5">Distance</label>
                <select 
                    v-model="localFilters.distance" 
                    @change="updateModelValue" 
                    class="w-full px-3 py-2 border border-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground text-sm"
                >
                    <option v-for="option in distanceOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Status Filter -->
            <div v-if="showStatus">
                <label class="block text-xs font-medium text-foreground mb-1.5">Status</label>
                <div class="space-y-1.5">
                    <label v-for="option in statusOptions.filter(o => o.value !== '')" :key="option.value" 
                           class="flex items-center space-x-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            :value="option.value"
                            :checked="localFilters.status?.includes(option.value) || false"
                            @change="handleStatusChange(option.value, ($event.target as HTMLInputElement).checked)"
                            class="rounded border-border bg-background text-primary focus:ring-primary"
                        />
                        <span class="text-xs text-foreground">{{ option.label }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="space-y-2 mb-4">
            <button 
                v-if="hasActiveFilters"
                @click="clearFilters" 
                class="w-full px-4 py-2 text-foreground border border-border rounded-lg hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary text-sm font-medium transition-colors"
            >
                Clear Filters
            </button>
            
            <!-- View on Map Button -->
            <button 
                v-if="showViewOnMapButton"
                @click="handleViewOnMap" 
                class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium flex items-center justify-center gap-2 transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Map View
            </button>
        </div>

        <!-- Result Count -->
        <div v-if="resultCount !== undefined" class="text-xs text-muted-foreground text-center mb-3">
            {{ resultCount }} result{{ resultCount !== 1 ? 's' : '' }}
            <span v-if="hasActiveFilters" class="text-primary">(filtered)</span>
        </div>

        <!-- Location Status -->
        <div v-if="showLocationStatus" class="p-2.5 bg-muted/50 rounded-lg border border-border">
            <div v-if="hasUserLocation" class="flex items-center gap-2 text-xs text-green-400">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                <span>Location enabled</span>
            </div>
            <div v-else>
                <div class="flex items-center gap-2 text-xs text-muted-foreground mb-2">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Enable for distance info</span>
                </div>
                <button 
                    @click="handleRequestLocation" 
                    class="w-full px-3 py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-xs hover:from-blue-700 hover:to-purple-700 transition-all"
                >
                    Enable Location
                </button>
            </div>
        </div>
    </div>
</template>