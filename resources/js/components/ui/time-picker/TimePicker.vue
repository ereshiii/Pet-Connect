<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    modelValue?: string;
    disabled?: boolean;
    placeholder?: string;
    availableSlots?: string[];
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select time',
    disabled: false,
    availableSlots: () => [],
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isOpen = ref(false);
const selectedTime = ref(props.modelValue || '');
const searchQuery = ref('');

watch(() => props.modelValue, (newValue) => {
    selectedTime.value = newValue || '';
});

// Generate time slots from available slots or default 30-minute intervals
const timeSlots = computed(() => {
    if (props.availableSlots && props.availableSlots.length > 0) {
        return props.availableSlots;
    }
    
    // Default: 9 AM to 5 PM in 30-minute intervals
    const slots: string[] = [];
    for (let hour = 9; hour <= 16; hour++) {
        for (let minute = 0; minute < 60; minute += 30) {
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            const displayMinute = minute.toString().padStart(2, '0');
            slots.push(`${displayHour}:${displayMinute} ${period}`);
        }
    }
    return slots;
});

const filteredTimeSlots = computed(() => {
    if (!searchQuery.value) return timeSlots.value;
    
    const query = searchQuery.value.toLowerCase();
    return timeSlots.value.filter(slot => 
        slot.toLowerCase().includes(query)
    );
});

const groupedTimeSlots = computed(() => {
    const groups: { [key: string]: string[] } = {
        'Morning': [],
        'Afternoon': [],
        'Evening': []
    };
    
    filteredTimeSlots.value.forEach(slot => {
        if (slot.includes('AM') || slot.startsWith('12:')) {
            groups['Morning'].push(slot);
        } else {
            const hour = parseInt(slot.split(':')[0]);
            if (hour < 5 || (hour === 12 && slot.includes('PM'))) {
                groups['Afternoon'].push(slot);
            } else {
                groups['Evening'].push(slot);
            }
        }
    });
    
    // Filter out empty groups
    return Object.entries(groups).filter(([_, slots]) => slots.length > 0);
});

const selectTime = (time: string) => {
    selectedTime.value = time;
    emit('update:modelValue', time);
    isOpen.value = false;
    searchQuery.value = '';
};

const togglePicker = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
        if (isOpen.value) {
            // Focus search input after opening
            setTimeout(() => {
                const searchInput = document.querySelector('.time-picker-search') as HTMLInputElement;
                searchInput?.focus();
            }, 100);
        }
    }
};

// Close picker when clicking outside
const pickerRef = ref<HTMLElement>();
const handleClickOutside = (event: MouseEvent) => {
    if (pickerRef.value && !pickerRef.value.contains(event.target as Node)) {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

watch(isOpen, (open) => {
    if (open) {
        document.addEventListener('click', handleClickOutside);
    } else {
        document.removeEventListener('click', handleClickOutside);
    }
});
</script>

<template>
    <div ref="pickerRef" class="relative">
        <!-- Time Input Button -->
        <button
            type="button"
            @click="togglePicker"
            :disabled="disabled"
            :class="cn(
                'w-full px-4 py-3 border rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:bg-gray-900 dark:text-gray-100',
                'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500',
                'flex items-center justify-between gap-2',
                disabled && 'opacity-50 cursor-not-allowed',
                props.class
            )"
        >
            <span :class="selectedTime ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'">
                {{ selectedTime || placeholder }}
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>

        <!-- Time Picker Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden"
            >
                <!-- Search Input -->
                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="time-picker-search w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Search time..."
                        />
                    </div>
                </div>

                <!-- Time Slots -->
                <div class="max-h-80 overflow-y-auto p-2">
                    <div v-if="filteredTimeSlots.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                        {{ availableSlots.length === 0 ? 'No available time slots' : 'No matching time slots' }}
                    </div>

                    <div v-else>
                        <div
                            v-for="[period, slots] in groupedTimeSlots"
                            :key="period"
                            class="mb-3 last:mb-0"
                        >
                            <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 px-2 py-1 mb-1">
                                {{ period }}
                            </div>
                            <div class="grid grid-cols-2 gap-1">
                                <button
                                    v-for="slot in slots"
                                    :key="slot"
                                    type="button"
                                    @click="selectTime(slot)"
                                    :class="cn(
                                        'px-3 py-2 text-sm rounded-lg transition-all',
                                        selectedTime === slot
                                            ? 'bg-blue-600 text-white font-semibold'
                                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                    )"
                                >
                                    {{ slot }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
