<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import { useAppearance } from '@/composables/useAppearance';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, RefreshCw, Monitor, Moon, Sun } from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

const switchAccountType = () => {
    // This is for testing purposes - in production you'd have proper account switching
    const currentType = user.account_type;
    let newType: string;
    
    console.log('🔄 Account switch initiated:', {
        currentType,
        user: user,
        timestamp: new Date().toISOString()
    });
    
    // Cycle through account types: user -> clinic -> admin -> user
    if (currentType === 'user') {
        newType = 'clinic';
    } else if (currentType === 'clinic') {
        newType = 'admin';
    } else {
        newType = 'user';
    }
    
    console.log('🔄 Switching account type:', {
        from: currentType,
        to: newType,
        timestamp: new Date().toISOString()
    });
    
    router.post('/switch-account-type', {
        account_type: newType
    }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            console.log('✅ Account switch success callback', {
                timestamp: new Date().toISOString()
            });
            // Let Inertia handle the redirect from the server
            // The server already redirects to the appropriate dashboard
        },
        onError: (errors) => {
            console.error('❌ Account switch error:', errors);
        }
    });
};

const props = defineProps<Props>();
const { user } = props;
const { appearance, updateAppearance } = useAppearance();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    
    <!-- Theme Toggle -->
    <DropdownMenuGroup>
        <DropdownMenuLabel class="px-2 py-1.5 text-xs font-semibold">Theme</DropdownMenuLabel>
        <div class="flex items-center justify-center gap-1 px-2 py-1">
            <Button
                variant="ghost"
                size="sm"
                class="h-8 flex-1"
                :class="{ 'bg-accent': appearance === 'light' }"
                @click="updateAppearance('light')"
            >
                <Sun class="h-4 w-4" />
            </Button>
            <Button
                variant="ghost"
                size="sm"
                class="h-8 flex-1"
                :class="{ 'bg-accent': appearance === 'system' }"
                @click="updateAppearance('system')"
            >
                <Monitor class="h-4 w-4" />
            </Button>
            <Button
                variant="ghost"
                size="sm"
                class="h-8 flex-1"
                :class="{ 'bg-accent': appearance === 'dark' }"
                @click="updateAppearance('dark')"
            >
                <Moon class="h-4 w-4" />
            </Button>
        </div>
    </DropdownMenuGroup>
    
    <DropdownMenuSeparator />
    
    <DropdownMenuGroup>
        <!-- User Settings (for pet owners) -->
        <DropdownMenuItem v-if="user.account_type === 'user' || (!user.account_type && !user.is_clinic)" as-child>
            <Link class="flex w-full cursor-default items-center" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
        <!-- Clinic Settings -->
        <DropdownMenuItem v-if="user.account_type === 'clinic' || user.is_clinic" as-child>
            <Link class="flex w-full cursor-default items-center" href="/clinic/settings/profile" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
        <!-- Admin Settings -->
        <DropdownMenuItem v-if="user.account_type === 'admin' || user.is_admin" as-child>
            <Link class="flex w-full cursor-default items-center" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
        <!-- Account switching only for admin users -->
        <DropdownMenuItem v-if="user.is_admin || user.account_type === 'admin'" @click="switchAccountType" as="button" class="w-full">
            <RefreshCw class="mr-2 h-4 w-4" />
            Switch to {{ user.account_type === 'clinic' ? 'Admin' : user.account_type === 'admin' ? 'User' : 'Clinic' }} Account
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem as-child>
        <Link
            class="flex w-full cursor-default items-center"
            :href="logout()"
            @click="handleLogout"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
