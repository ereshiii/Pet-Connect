<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, RefreshCw } from 'lucide-vue-next';

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
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="edit()" prefetch as="button">
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
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
