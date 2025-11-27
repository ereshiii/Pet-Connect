<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Crown, Users, Briefcase } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    type: 'staff' | 'services';
}

const props = defineProps<Props>();
const page = usePage();

const usageStats = computed(() => {
    return page.props.subscription?.usageStats || null;
});

const isAtLimit = computed(() => {
    if (!usageStats.value) return false;
    
    if (props.type === 'staff') {
        return !usageStats.value.staff.can_add;
    } else {
        return !usageStats.value.services.can_add;
    }
});

const limitInfo = computed(() => {
    if (!usageStats.value) return null;
    
    const data = props.type === 'staff' ? usageStats.value.staff : usageStats.value.services;
    
    return {
        current: data.current,
        max: data.max,
        unlimited: data.unlimited,
        percentage: data.unlimited ? 0 : Math.round((data.current / data.max) * 100)
    };
});

const upgradeMessage = computed(() => {
    if (props.type === 'staff') {
        return {
            title: 'Staff Limit Reached',
            description: 'Upgrade to Professional (3 staff) or Pro Plus (unlimited staff) to add more team members.',
            icon: Users
        };
    } else {
        return {
            title: 'Services Limit Reached',
            description: 'Upgrade to Professional (10 services) or Pro Plus (unlimited services) to add more services.',
            icon: Briefcase
        };
    }
});

const handleUpgrade = () => {
    router.visit('/subscription');
};
</script>

<template>
    <Alert v-if="isAtLimit" variant="destructive" class="mb-6">
        <AlertTriangle class="h-4 w-4" />
        <AlertTitle class="flex items-center gap-2">
            {{ upgradeMessage.title }}
            <Badge variant="secondary" class="gap-1">
                <component :is="upgradeMessage.icon" class="h-3 w-3" />
                {{ limitInfo?.current }} / {{ limitInfo?.unlimited ? '∞' : limitInfo?.max }}
            </Badge>
        </AlertTitle>
        <AlertDescription class="mt-2 space-y-3">
            <p>{{ upgradeMessage.description }}</p>
            
            <Button 
                variant="outline" 
                size="sm" 
                @click="handleUpgrade"
                class="gap-2"
            >
                <Crown class="h-4 w-4" />
                Upgrade Plan
            </Button>
        </AlertDescription>
    </Alert>
</template>
