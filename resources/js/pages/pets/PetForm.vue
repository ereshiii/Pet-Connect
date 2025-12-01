<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useMobileKeyboard } from '@/composables/useMobileKeyboard';

interface PetFormData {
    name: string;
    type_id: number | null;
    breed_id: number | null;
    gender: string;
    birth_date: string | null;
    weight: number | null;
    size: string | null;
    color: string | null;
    markings: string | null;
    is_neutered: boolean;
    special_needs: string | null;
    notes: string | null;
}

interface PetType {
    id: number;
    name: string;
    icon?: string;
}

interface Breed {
    id: number;
    name: string;
    type_id: number;
}

interface Props {
    pet?: PetFormData;
    petTypes: PetType[];
    breeds: Breed[];
    mode: 'create' | 'edit';
    submitUrl: string;
    submitMethod?: 'post' | 'put';
}

const props = withDefaults(defineProps<Props>(), {
    submitMethod: 'post',
});

const emit = defineEmits<{
    (e: 'cancel'): void;
    (e: 'success'): void;
}>();

const { handleInputFocus } = useMobileKeyboard();

// Form setup
const form = useForm<PetFormData>({
    name: props.pet?.name || '',
    type_id: props.pet?.type_id || null,
    breed_id: props.pet?.breed_id || null,
    gender: props.pet?.gender || 'male',
    birth_date: props.pet?.birth_date || null,
    weight: props.pet?.weight || null,
    size: props.pet?.size || null,
    color: props.pet?.color || null,
    markings: props.pet?.markings || null,
    is_neutered: props.pet?.is_neutered || false,
    special_needs: props.pet?.special_needs || null,
    notes: props.pet?.notes || null,
});

// Options
const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'unknown', label: 'Unknown' },
];

const sizeOptions = [
    { value: 'small', label: 'Small' },
    { value: 'medium', label: 'Medium' },
    { value: 'large', label: 'Large' },
    { value: 'extra_large', label: 'Extra Large' },
];

// Computed
const filteredBreeds = computed(() => {
    if (!form.type_id) return [];
    return props.breeds.filter(breed => breed.type_id === form.type_id);
});

const maxBirthDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const selectedPetType = computed(() => {
    return props.petTypes.find(type => type.id === form.type_id);
});

// Watchers
watch(() => form.type_id, () => {
    // Reset breed when pet type changes
    if (form.breed_id) {
        const breedExists = filteredBreeds.value.some(breed => breed.id === form.breed_id);
        if (!breedExists) {
            form.breed_id = null;
        }
    }
});

// Methods
const submitForm = () => {
    const method = props.submitMethod;
    
    if (method === 'post') {
        form.post(props.submitUrl, {
            onSuccess: () => emit('success'),
        });
    } else {
        form.put(props.submitUrl, {
            onSuccess: () => emit('success'),
        });
    }
};

const cancel = () => {
    emit('cancel');
};
</script>

<template>
    <form @submit.prevent="submitForm" @focusin="handleInputFocus" class="divide-y divide-border">
        
        <!-- Basic Information -->
        <div class="p-6 md:p-8">
            <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-6">
                <div class="p-1.5 sm:p-2 bg-primary/10 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold">Basic Information</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                
                <!-- Pet Name -->
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-semibold mb-2">
                        Pet Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        v-model="form.name"
                        type="text" 
                        required
                        :class="['w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all',
                                form.errors.name ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'hover:border-muted-foreground']"
                        placeholder="Enter your pet's name"
                    />
                    <p v-if="form.errors.name" class="text-red-600 dark:text-red-400 text-xs sm:text-sm mt-2 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Pet Type -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Pet Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        v-model="form.type_id"
                        required
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                    >
                        <option :value="null">Select pet type</option>
                        <option v-for="type in petTypes" :key="type.id" :value="type.id">
                            {{ type.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.type_id" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.type_id }}</p>
                </div>

                <!-- Breed -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Breed
                    </label>
                    <select 
                        v-model="form.breed_id"
                        :disabled="!form.type_id || filteredBreeds.length === 0"
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <option :value="null">{{ form.type_id ? 'Select breed (optional)' : 'Select pet type first' }}</option>
                        <option v-for="breed in filteredBreeds" :key="breed.id" :value="breed.id">
                            {{ breed.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.breed_id" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.breed_id }}</p>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Gender <span class="text-red-500">*</span>
                    </label>
                    <select 
                        v-model="form.gender"
                        required
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                    >
                        <option v-for="gender in genderOptions" :key="gender.value" :value="gender.value">
                            {{ gender.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.gender" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.gender }}</p>
                </div>

                <!-- Birth Date -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Birth Date
                    </label>
                    <input 
                        v-model="form.birth_date"
                        type="date" 
                        :max="maxBirthDate"
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                    />
                    <p v-if="form.errors.birth_date" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.birth_date }}</p>
                </div>

                <!-- Weight -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Weight (kg)
                    </label>
                    <input 
                        v-model="form.weight"
                        type="number" 
                        min="0"
                        step="0.1"
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                        placeholder="Enter weight in kg"
                    />
                    <p v-if="form.errors.weight" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.weight }}</p>
                </div>

                <!-- Size -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Size
                    </label>
                    <select 
                        v-model="form.size"
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                    >
                        <option :value="null">Select size (optional)</option>
                        <option v-for="size in sizeOptions" :key="size.value" :value="size.value">
                            {{ size.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.size" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.size }}</p>
                </div>

                <!-- Color -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Color
                    </label>
                    <input 
                        v-model="form.color"
                        type="text" 
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                        placeholder="e.g., Brown, White"
                    />
                    <p v-if="form.errors.color" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.color }}</p>
                </div>

                <!-- Markings -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Markings
                    </label>
                    <input 
                        v-model="form.markings"
                        type="text" 
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary transition-all hover:border-muted-foreground"
                        placeholder="e.g., White spots, Black stripe"
                    />
                    <p v-if="form.errors.markings" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.markings }}</p>
                </div>

                <!-- Neutered Status -->
                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input 
                                v-model="form.is_neutered"
                                type="checkbox" 
                                class="sr-only"
                            />
                            <div :class="[
                                'w-11 h-6 rounded-full transition-colors',
                                form.is_neutered ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
                            ]"></div>
                            <div :class="[
                                'absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform',
                                form.is_neutered ? 'translate-x-5' : ''
                            ]"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium group-hover:text-primary transition-colors">
                            Pet is neutered/spayed
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="p-6 md:p-8 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-6">
                <div class="p-1.5 sm:p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold">Additional Information</h3>
            </div>
            <div class="space-y-6">
                <!-- Special Needs -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Special Needs
                    </label>
                    <textarea 
                        v-model="form.special_needs"
                        rows="3"
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all hover:border-muted-foreground resize-none"
                        placeholder="Any special care requirements, medical conditions, or behavioral notes..."
                    ></textarea>
                    <p v-if="form.errors.special_needs" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.special_needs }}</p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Notes
                    </label>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        class="w-full px-4 py-3 border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all hover:border-muted-foreground resize-none"
                        placeholder="Any additional information about your pet's behavior, preferences, or other details..."
                    ></textarea>
                    <p v-if="form.errors.notes" class="text-red-600 dark:text-red-400 text-sm mt-2">{{ form.errors.notes }}</p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="p-4 sm:p-6 md:p-8 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button 
                    type="submit"
                    :disabled="form.processing"
                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base rounded-xl hover:from-blue-700 hover:to-purple-700 font-semibold transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                >
                    <svg v-if="form.processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <template v-else>
                        <svg v-if="mode === 'create'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                        </svg>
                    </template>
                    <span v-if="form.processing">{{ mode === 'create' ? 'Registering...' : 'Saving...' }}</span>
                    <span v-else>{{ mode === 'create' ? 'Register Pet' : 'Save Changes' }}</span>
                </button>
                <button 
                    type="button"
                    @click="cancel"
                    :disabled="form.processing"
                    class="flex-1 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Cancel
                </button>
            </div>
        </div>
    </form>
</template>
