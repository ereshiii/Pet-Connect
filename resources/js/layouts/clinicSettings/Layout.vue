<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { User, Building2, Lock, Shield, Palette, CreditCard, Receipt, Wallet, Crown } from 'lucide-vue-next';

interface NavCategory {
    category: string;
    items: NavItem[];
}

const sidebarNavItems: NavCategory[] = [
    {
        category: 'Clinic Settings',
        items: [
            {
                title: 'Profile & Information',
                href: '/clinic/settings/clinic-profile',
                icon: Building2,
            },
        ],
    },
    {
        category: 'Account',
        items: [
            {
                title: 'Profile',
                href: '/clinic/settings/profile',
                icon: User,
            },
        ],
    },
    {
        category: 'Security',
        items: [
            {
                title: 'Password',
                href: '/clinic/settings/password',
                icon: Lock,
            },
            {
                title: 'Two-Factor Auth',
                href: '/clinic/settings/two-factor',
                icon: Shield,
            },
        ],
    },
    {
        category: 'Subscription',
        items: [
            {
                title: 'Current Plan',
                href: '/clinic/settings/current-plan',
                icon: Crown,
            },
            {
                title: 'Payment Methods',
                href: '/clinic/settings/payment-methods',
                icon: Wallet,
            },
            {
                title: 'Billing History',
                href: '/clinic/settings/billing-history',
                icon: Receipt,
            },
            {
                title: 'Manage Subscription',
                href: '/clinic/settings/subscription',
                icon: CreditCard,
            },
        ],
    },
    {
        category: 'Appearance',
        items: [
            {
                title: 'Theme',
                href: '/clinic/settings/appearance',
                icon: Palette,
            },
        ],
    },
];

const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Clinic Settings"
            description="Manage your clinic profile and settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <template v-for="(group, groupIndex) in sidebarNavItems" :key="group.category">
                        <div v-if="groupIndex > 0" class="py-2">
                            <Separator />
                        </div>
                        
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-muted-foreground px-3 py-2">
                                {{ group.category }}
                            </p>
                            
                            <Link :href="item.href" v-for="item in group.items" :key="toUrl(item.href)">
                                <Button
                                    variant="ghost"
                                    :class="[
                                        'w-full justify-start gap-2',
                                        { 'bg-muted': urlIsActive(item.href, currentPath) },
                                    ]"
                                >
                                    <component :is="item.icon" class="h-4 w-4 shrink-0" />
                                    <span>{{ item.title }}</span>
                                </Button>
                            </Link>
                        </div>
                    </template>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
