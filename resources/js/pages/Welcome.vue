<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

// Reactive data for animations and interactions
const isLoaded = ref(false);
const activeFeature = ref(0);
const activePlan = ref(1); // Default to Premium plan

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

// Features data
const features = ref([
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
    },
    {
        icon: '💬',
        title: 'Direct Communication',
        description: 'Connect directly with veterinarians for quick consultations and follow-ups.'
    },
    {
        icon: '📊',
        title: 'Health Analytics',
        description: 'Monitor your pet\'s health trends and receive personalized care recommendations.'
    },
    {
        icon: '🚨',
        title: 'Emergency Services',
        description: 'Access 24/7 emergency veterinary services when your pet needs immediate care.'
    }
]);

// Pricing plans
const pricingPlans = ref([
    {
        name: 'Free',
        price: '₱0',
        period: 'forever',
        description: 'Perfect for pet owners getting started',
        features: [
            'Up to 2 pets',
            'Basic health tracking',
            'Clinic search & reviews',
            'Basic appointment booking',
            'Community support'
        ],
        popular: false,
        cta: 'Get Started Free'
    },
    {
        name: 'Premium',
        price: '₱199',
        period: 'per month',
        description: 'Best for dedicated pet parents',
        features: [
            'Unlimited pets',
            'Advanced health tracking',
            'Priority appointment booking',
            'Telemedicine consultations',
            'Health reports & analytics',
            'Export medical records',
            'Priority customer support'
        ],
        popular: true,
        cta: 'Start Premium Trial'
    },
    {
        name: 'Clinic Basic',
        price: '₱899',
        period: 'per month',
        description: 'For growing veterinary practices',
        features: [
            'Up to 100 appointments/month',
            'Basic clinic management',
            'Patient records system',
            'Online booking calendar',
            'Basic analytics',
            'Email support'
        ],
        popular: false,
        cta: 'Start Clinic Trial'
    }
]);

// Statistics
const stats = ref([
    { number: '10,000+', label: 'Happy Pet Owners' },
    { number: '500+', label: 'Partner Clinics' },
    { number: '50,000+', label: 'Appointments Booked' },
    { number: '99.9%', label: 'Uptime Reliability' }
]);

// Testimonials
const testimonials = ref([
    {
        name: 'Maria Santos',
        role: 'Dog Owner',
        avatar: '👩‍💼',
        content: 'PetConnect made finding a reliable vet so easy! The appointment booking system is fantastic and my dog Max gets the best care.',
        rating: 5
    },
    {
        name: 'Dr. Juan Dela Cruz',
        role: 'Veterinarian at Manila Pet Clinic',
        avatar: '👨‍⚕️',
        content: 'As a clinic owner, PetConnect has streamlined our operations and helped us reach more pet owners in our community.',
        rating: 5
    },
    {
        name: 'Sarah Johnson',
        role: 'Cat Owner',
        avatar: '👩',
        content: 'The health tracking features are amazing! I can keep all of Luna\'s medical records in one place and share them easily with vets.',
        rating: 5
    }
]);

onMounted(() => {
    isLoaded.value = true;
    
    // Auto-rotate features
    setInterval(() => {
        activeFeature.value = (activeFeature.value + 1) % features.value.length;
    }, 4000);
});

// Smooth scroll to section
const scrollToSection = (sectionId: string) => {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <Head title="PetConnect - Complete Pet Care Management Platform">
        <meta name="description" content="Connect with trusted veterinary clinics, manage your pet's health records, and book appointments easily. The complete pet care platform for Filipino pet owners.">
        <meta name="keywords" content="pet care, veterinary clinics, pet health, appointment booking, Philippines, dogs, cats, veterinarian">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Navigation Header -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 dark:bg-gray-900/95 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            🐾 PetConnect
                        </div>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-8">
                        <button @click="scrollToSection('features')" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">
                            Features
                        </button>
                        <button @click="scrollToSection('pricing')" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">
                            Pricing
                        </button>
                        <button @click="scrollToSection('about')" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">
                            About
                        </button>
                        <button @click="scrollToSection('contact')" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">
                            Contact
                        </button>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Get Started
                            </Link>
                        </template>

                        <!-- Theme Toggle (for both desktop and mobile) -->
                        <div class="relative">
                            <button 
                                @click="showThemeDropdown = !showThemeDropdown"
                                class="p-2 text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors"
                                title="Change theme"
                            >
                                <component :is="getCurrentThemeIcon()" class="h-5 w-5" />
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div 
                                v-if="showThemeDropdown"
                                class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                                @click.away="showThemeDropdown = false"
                            >
                                <button
                                    v-for="option in themeOptions"
                                    :key="option.value"
                                    @click="updateAppearance(option.value); showThemeDropdown = false"
                                    class="w-full px-3 py-2 text-left flex items-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    :class="appearance === option.value ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'"
                                >
                                    <component :is="option.Icon" class="h-4 w-4 mr-2" />
                                    {{ option.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-16 bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Hero Content -->
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
                                Complete Pet Care 
                                <span class="text-blue-600 dark:text-blue-400">Made Simple</span>
                            </h1>
                            <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                                Connect with trusted veterinary clinics, manage your pet's health records, and book appointments effortlessly. The ultimate platform for Filipino pet owners.
                            </p>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 transition-all transform hover:scale-105 text-center"
                            >
                                Start Free Today
                            </Link>
                            <button
                                @click="scrollToSection('features')"
                                class="border border-blue-600 text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-600 hover:text-white transition-all text-center dark:border-blue-400 dark:text-blue-400"
                            >
                                Learn More
                            </button>
                        </div>

                        <!-- Trust Indicators -->
                        <div class="flex items-center space-x-8 pt-8">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">10K+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Pet Owners</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">500+</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Clinics</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">99.9%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Uptime</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Image -->
                    <div class="relative">
                        <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl">
                            <img 
                                src="/images/landingpagebackground.jpg" 
                                alt="PetConnect Platform"
                                class="w-full h-96 object-cover"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        </div>
                        <!-- Floating elements for visual appeal -->
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-200 rounded-full opacity-20 animate-pulse"></div>
                        <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-purple-200 rounded-full opacity-20 animate-pulse delay-1000"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Everything You Need for Pet Care
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        From finding the right veterinarian to managing your pet's health records, PetConnect provides all the tools you need in one platform.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="bg-gray-50 dark:bg-gray-800 rounded-xl p-8 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                        :class="{ 'ring-2 ring-blue-500': activeFeature === index }"
                    >
                        <div class="text-4xl mb-4">{{ feature.icon }}</div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                            {{ feature.title }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Simple, Transparent Pricing
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                        Choose the perfect plan for your needs. Start free and upgrade as you grow.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div
                        v-for="(plan, index) in pricingPlans"
                        :key="index"
                        class="bg-white dark:bg-gray-900 rounded-xl p-8 shadow-lg relative"
                        :class="{ 'ring-2 ring-blue-500 scale-105': plan.popular }"
                    >
                        <div v-if="plan.popular" class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                            <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-sm font-semibold">
                                Most Popular
                            </span>
                        </div>

                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ plan.name }}
                            </h3>
                            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                                {{ plan.price }}
                            </div>
                            <div class="text-gray-600 dark:text-gray-300">
                                {{ plan.period }}
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mt-4">
                                {{ plan.description }}
                            </p>
                        </div>

                        <ul class="space-y-3 mb-8">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-center text-gray-700 dark:text-gray-300"
                            >
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ feature }}
                            </li>
                        </ul>

                        <Link
                            :href="register()"
                            class="w-full text-center py-3 px-6 rounded-lg font-semibold transition-colors block"
                            :class="plan.popular 
                                ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                : 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600'"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Trusted by Pet Owners & Veterinarians
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        See what our community has to say about PetConnect
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div
                        v-for="testimonial in testimonials"
                        :key="testimonial.name"
                        class="bg-gray-50 dark:bg-gray-800 rounded-xl p-8"
                    >
                        <div class="flex items-center mb-4">
                            <div class="text-3xl mr-4">{{ testimonial.avatar }}</div>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ testimonial.name }}
                                </div>
                                <div class="text-gray-600 dark:text-gray-300 text-sm">
                                    {{ testimonial.role }}
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            "{{ testimonial.content }}"
                        </p>
                        <div class="flex text-yellow-400">
                            <span v-for="i in testimonial.rating" :key="i">★</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 bg-blue-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">
                            About PetConnect
                        </h2>
                        <div class="space-y-4 text-gray-700 dark:text-gray-300">
                            <p>
                                PetConnect was born from a simple belief: every pet deserves access to quality healthcare, and every pet owner deserves peace of mind.
                            </p>
                            <p>
                                Founded in the Philippines, we understand the unique challenges faced by local pet owners in finding reliable veterinary care. Our platform bridges the gap between pet owners and trusted veterinary professionals.
                            </p>
                            <p>
                                Today, PetConnect serves thousands of pet owners and hundreds of veterinary clinics across the Philippines, making pet care more accessible, organized, and stress-free.
                            </p>
                        </div>
                        
                        <div class="mt-8 grid grid-cols-2 gap-6">
                            <div v-for="stat in stats" :key="stat.label" class="text-center">
                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ stat.number }}
                                </div>
                                <div class="text-gray-600 dark:text-gray-400">
                                    {{ stat.label }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="bg-white dark:bg-gray-700 rounded-2xl p-8 shadow-xl">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                                Our Mission
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300 mb-6">
                                To create a world where every pet receives the care they deserve through technology that connects, informs, and empowers.
                            </p>
                            
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-600 rounded-full mr-3"></div>
                                    <span class="text-gray-700 dark:text-gray-300">Accessible veterinary care for all</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-600 rounded-full mr-3"></div>
                                    <span class="text-gray-700 dark:text-gray-300">Empowering informed pet care decisions</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-600 rounded-full mr-3"></div>
                                    <span class="text-gray-700 dark:text-gray-300">Building stronger pet-owner relationships</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Get in Touch
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300">
                        Have questions? We're here to help you and your pets.
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Contact Information -->
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                                Contact Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Email</div>
                                        <div class="text-gray-600 dark:text-gray-300">support@petconnect.ph</div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Phone</div>
                                        <div class="text-gray-600 dark:text-gray-300">+63 2 8123 4567</div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">Address</div>
                                        <div class="text-gray-600 dark:text-gray-300">Makati City, Metro Manila, Philippines</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Business Hours
                            </h4>
                            <div class="space-y-2 text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span>Monday - Friday</span>
                                    <span>9:00 AM - 6:00 PM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Saturday</span>
                                    <span>9:00 AM - 3:00 PM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Sunday</span>
                                    <span>Closed</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Card -->
                    <div class="bg-blue-50 dark:bg-gray-800 rounded-2xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                            Ready to Get Started?
                        </h3>
                        <p class="text-gray-700 dark:text-gray-300 mb-8">
                            Join thousands of pet owners who trust PetConnect for their pet care needs.
                        </p>
                        
                        <div class="space-y-4">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="w-full bg-blue-600 text-white py-4 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-center block"
                            >
                                Create Free Account
                            </Link>
                            <Link
                                :href="login()"
                                class="w-full border border-blue-600 text-blue-600 py-4 px-6 rounded-lg font-semibold hover:bg-blue-600 hover:text-white transition-colors text-center block dark:border-blue-400 dark:text-blue-400"
                            >
                                Sign In
                            </Link>
                        </div>

                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                                Questions about our platform? 
                                <br>
                                <a href="mailto:support@petconnect.ph" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Contact our support team
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div>
                        <div class="text-2xl font-bold text-blue-400 mb-4">
                            🐾 PetConnect
                        </div>
                        <p class="text-gray-400 mb-4">
                            Complete pet care management platform connecting pet owners with trusted veterinary clinics across the Philippines.
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Facebook</span>
                                📘
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Twitter</span>
                                🐦
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Instagram</span>
                                📷
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><button @click="scrollToSection('features')" class="text-gray-400 hover:text-white transition-colors">Features</button></li>
                            <li><button @click="scrollToSection('pricing')" class="text-gray-400 hover:text-white transition-colors">Pricing</button></li>
                            <li><button @click="scrollToSection('about')" class="text-gray-400 hover:text-white transition-colors">About Us</button></li>
                            <li><button @click="scrollToSection('contact')" class="text-gray-400 hover:text-white transition-colors">Contact</button></li>
                        </ul>
                    </div>

                    <!-- For Pet Owners -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">For Pet Owners</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Find Clinics</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Book Appointments</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Health Records</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Emergency Care</a></li>
                        </ul>
                    </div>

                    <!-- For Clinics -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">For Veterinarians</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Join Network</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Clinic Management</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Patient Records</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Analytics</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                    <p class="text-gray-400">
                        © {{ new Date().getFullYear() }} PetConnect. All rights reserved. Made with ❤️ for pets in the Philippines.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom animations */
.starting\:opacity-0 {
    opacity: 0;
}

/* Smooth scrolling for the entire page */
html {
    scroll-behavior: smooth;
}

/* Custom gradient animation */
@keyframes gradient {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}
</style>