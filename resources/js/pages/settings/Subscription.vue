<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Subscription',
        href: '/settings/subscription',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Subscription" />
        
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Subscription</h1>
            <p class="text-gray-600">Manage your subscription plan here.</p>
        </div>
    </AppLayout>
</template>
