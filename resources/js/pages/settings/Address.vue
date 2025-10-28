<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Address Settings',
        href: '/settings/address',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Address Settings" />
        
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Address Settings</h1>
            <p class="text-gray-600">Manage your address information here.</p>
        </div>
    </AppLayout>
</template>
