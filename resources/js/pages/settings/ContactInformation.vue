<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Contact Information',
        href: '/settings/contact-information',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Contact Information" />
        
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">Contact Information</h1>
            <p class="text-gray-600">Manage your contact details here.</p>
        </div>
    </AppLayout>
</template>
