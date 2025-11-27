<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { edit as editAddress } from '@/routes/address';
import { edit as editContactInformation } from '@/routes/contact-information';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { User, Lock, Shield, Palette, MapPin, Phone } from 'lucide-vue-next';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
        icon: User,
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: Lock,
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: Shield,
    },
    {
        title: 'Address',
        href: editAddress(),
        icon: MapPin,
    },
    {
        title: 'Contact Information',
        href: editContactInformation(),
        icon: Phone,
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: Palette,
    },
];

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Link :href="item.href" v-for="item in sidebarNavItems" :key="toUrl(item.href)">
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
