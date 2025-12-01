<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

defineProps<{
    title?: string;
    description?: string;
}>();

// Theme toggle functionality
const { appearance, updateAppearance } = useAppearance();
const showThemeDropdown = ref(false);

const themeOptions = [
    { value: 'light' as const, Icon: Sun, label: 'Light' },
    { value: 'dark' as const, Icon: Moon, label: 'Dark' },
    { value: 'system' as const, Icon: Monitor, label: 'System' },
];

const getCurrentThemeIcon = () => {
    const option = themeOptions.find(opt => opt.value === appearance.value);
    return option?.Icon || Monitor;
};

// Features data for background
const features = [
    {
        icon: '🏥',
        title: 'Find Veterinary Clinics',
        description: 'Discover trusted veterinary clinics near you with real-time availability and verified reviews.'
    },
    {
        icon: '📅',
        title: 'Easy Appointment Booking',
        description: 'Schedule appointments with your preferred veterinarians in just a few clicks.'
    },
    {
        icon: '🐕',
        title: 'Pet Health Records',
        description: 'Keep track of your pet\'s medical history, vaccinations, and health milestones.'
    }
];
</script>

<template>
    <!-- Background: Home Page Content -->
    <div class="relative min-h-screen overflow-hidden">
        <!-- Welcome Page Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-blue-900/20 dark:to-purple-900/20">
            <!-- Animated gradient orbs -->
            <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-300 dark:bg-purple-500 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-300 dark:bg-yellow-500 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 dark:bg-pink-500 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Blurred background features -->
        <div class="absolute inset-0 overflow-hidden opacity-20 dark:opacity-10">
            <div class="container mx-auto px-4 py-20">
                <div class="grid md:grid-cols-3 gap-8 mt-20">
                    <div v-for="(feature, index) in features" :key="index" 
                         class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg transform hover:scale-105 transition-all duration-300">
                        <div class="text-5xl mb-4">{{ feature.icon }}</div>
                        <h3 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">{{ feature.title }}</h3>
                        <p class="text-gray-600 dark:text-gray-300">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay backdrop with blur -->
        <div class="absolute inset-0 bg-white/40 dark:bg-gray-900/40 backdrop-blur-md"></div>

        <!-- Theme Toggle Button -->
        <div class="absolute top-6 right-6 z-50 hidden sm:block">
            <div class="relative">
                <button
                    @click="showThemeDropdown = !showThemeDropdown"
                    class="flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    <component :is="getCurrentThemeIcon()" class="h-4 w-4" />
                    <span class="hidden sm:inline">Theme</span>
                </button>

                <!-- Theme Dropdown -->
                <div
                    v-if="showThemeDropdown"
                    @click.stop
                    class="absolute right-0 mt-2 w-40 rounded-lg border border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm shadow-lg py-1"
                >
                    <button
                        v-for="option in themeOptions"
                        :key="option.value"
                        @click="updateAppearance(option.value); showThemeDropdown = false"
                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': appearance === option.value }"
                    >
                        <component :is="option.Icon" class="h-4 w-4" />
                        <span>{{ option.label }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Theme Toggle Floating Button (mobile only) -->
        <div class="fixed bottom-4 right-4 z-50 sm:hidden">
            <button 
                @click="showThemeDropdown = !showThemeDropdown"
                class="bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg rounded-full p-2.5 flex items-center justify-center transition-all hover:from-blue-600 hover:to-purple-700 hover:scale-110"
                title="Change theme"
            >
                <component :is="getCurrentThemeIcon()" class="h-5 w-5 text-white" />
            </button>
            <!-- Dropdown Menu (icons only) -->
            <div 
                v-if="showThemeDropdown"
                @click.stop
                class="absolute bottom-16 right-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-2 w-14"
            >
                <button
                    v-for="option in themeOptions"
                    :key="option.value"
                    @click="updateAppearance(option.value); showThemeDropdown = false"
                    class="flex items-center justify-center px-2 py-3 w-full hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 dark:hover:from-blue-900/50 dark:hover:to-purple-900/50 transition-all"
                    :title="option.label"
                >
                    <component :is="option.Icon" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </button>
            </div>
        </div>

        <!-- Auth Form Modal -->
        <div class="relative flex min-h-screen flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="w-full max-w-md">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-8">
                    <div class="flex flex-col gap-6">
                        <div class="flex flex-col items-center gap-4">
                            <Link
                                :href="home()"
                                class="flex flex-col items-center gap-2 font-medium group"
                            >
                                <div class="mb-1 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 shadow-lg group-hover:shadow-xl transition-shadow">
                                    <AppLogoIcon
                                        class="size-8 fill-current text-white"
                                    />
                                </div>
                                <span class="sr-only">{{ title }}</span>
                            </Link>
                            <div class="space-y-2 text-center">
                                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400 bg-clip-text text-transparent">
                                    {{ title }}
                                </h1>
                                <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                                    {{ description }}
                                </p>
                            </div>
                        </div>
                        <slot />
                    </div>
                </div>

                <!-- Footer Link -->
                <div class="mt-6 text-center">
                    <Link
                        :href="home()"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors inline-flex items-center gap-2 group"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:-translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Back to home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
