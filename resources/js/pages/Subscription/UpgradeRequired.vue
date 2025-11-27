<script setup lang="ts">
import UpgradePrompt from '@/components/subscription/UpgradePrompt.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Props {
    feature: string;
    requiredPlan: 'professional' | 'pro-plus';
}

const props = defineProps<Props>();

const breadcrumbs = [
    {
        title: 'Clinic Dashboard',
        href: '/clinic/dashboard',
    },
    {
        title: 'Upgrade Required',
        href: '#',
    },
];
</script>

<template>
    <Head title="Upgrade Required" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col items-center justify-center p-6">
            <UpgradePrompt 
                :feature="feature"
                :required-plan="requiredPlan"
            />
        </div>
    </AppLayout>
</template> 
