<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { getInitials } from '@/composables/useInitials';
import { toUrl, urlIsActive } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, NavItem } from '@/types';
import { InertiaLinkProps, Link, usePage, router } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu, Monitor, Moon, Search, Sun, Bell, Clock } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const { appearance, updateAppearance } = useAppearance();

// Notification state
const notifications = ref<any[]>([]);
const unreadCount = ref(0);
const loadingNotifications = ref(false);

const fetchRecentNotifications = async () => {
    try {
        loadingNotifications.value = true;
        const response = await axios.get('/api/notifications/recent');
        notifications.value = response.data.notifications || [];
        unreadCount.value = response.data.unread_count || 0;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loadingNotifications.value = false;
    }
};

const markAsRead = async (notificationId: number) => {
    try {
        await axios.post(`/api/notifications/${notificationId}/mark-as-read`);
        const notification = notifications.value.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
    } catch (error) {
        console.error('Failed to mark as read:', error);
    }
};

const handleNotificationClick = (notification: any) => {
    markAsRead(notification.id);
    
    // Navigate based on notification type
    if (notification.data?.appointment_id) {
        router.visit(`/appointments/${notification.data.appointment_id}`);
    } else if (notification.data?.url) {
        router.visit(notification.data.url);
    }
};

const formatNotificationTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / 60000);
    
    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours}h ago`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays}d ago`;
    
    return date.toLocaleDateString();
};

onMounted(() => {
    if (auth.value?.user) {
        fetchRecentNotifications();
        
        // Refresh notifications every 60 seconds
        setInterval(fetchRecentNotifications, 60000);
    }
});

const isCurrentRoute = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        urlIsActive(url, page.url),
);

const activeItemStyles = computed(
    () => (url: NonNullable<InertiaLinkProps['href']>) =>
        isCurrentRoute.value(toUrl(url))
            ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100'
            : '',
);

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only"
                                >Navigation Menu</SheetTitle
                            >
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 fill-current text-black dark:text-white"
                                />
                            </SheetHeader>
                            <div
                                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                            >
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="toUrl(item.href)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="dashboard()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList
                            class="flex h-full items-stretch space-x-2"
                        >
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        activeItemStyles(item.href),
                                        'h-9 cursor-pointer px-3',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="isCurrentRoute(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="group h-9 w-9 cursor-pointer"
                        >
                            <Search
                                class="size-5 opacity-80 group-hover:opacity-100"
                            />
                        </Button>

                        <!-- Theme Toggle Dropdown -->
                        <DropdownMenu>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="group h-9 w-9 cursor-pointer"
                                >
                                    <Sun
                                        v-if="appearance === 'light'"
                                        class="size-5 opacity-80 group-hover:opacity-100"
                                    />
                                    <Moon
                                        v-else-if="appearance === 'dark'"
                                        class="size-5 opacity-80 group-hover:opacity-100"
                                    />
                                    <Monitor
                                        v-else
                                        class="size-5 opacity-80 group-hover:opacity-100"
                                    />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-32">
                                <DropdownMenuItem
                                    @click="updateAppearance('light')"
                                    class="cursor-pointer"
                                >
                                    <Sun class="mr-2 h-4 w-4" />
                                    Light
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="updateAppearance('dark')"
                                    class="cursor-pointer"
                                >
                                    <Moon class="mr-2 h-4 w-4" />
                                    Dark
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="updateAppearance('system')"
                                    class="cursor-pointer"
                                >
                                    <Monitor class="mr-2 h-4 w-4" />
                                    System
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Notification Dropdown -->
                        <DropdownMenu>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="group h-9 w-9 cursor-pointer relative"
                                >
                                    <Bell
                                        class="size-5 opacity-80 group-hover:opacity-100"
                                    />
                                    <!-- Notification Indicator Dot -->
                                    <span
                                        v-if="unreadCount > 0"
                                        class="absolute top-1.5 right-1.5 flex h-2 w-2"
                                    >
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-80">
                                <div class="flex items-center justify-between px-3 py-2 border-b">
                                    <h3 class="font-semibold text-sm">Notifications</h3>
                                    <span v-if="unreadCount > 0" class="text-xs bg-primary text-primary-foreground px-2 py-0.5 rounded-full">
                                        {{ unreadCount }}
                                    </span>
                                </div>
                                
                                <div v-if="loadingNotifications" class="px-3 py-8 text-center text-sm text-muted-foreground">
                                    Loading...
                                </div>
                                
                                <div v-else-if="notifications.length === 0" class="px-3 py-8 text-center">
                                    <Bell class="h-8 w-8 mx-auto mb-2 text-muted-foreground opacity-50" />
                                    <p class="text-sm text-muted-foreground">No notifications</p>
                                </div>
                                
                                <div v-else class="max-h-96 overflow-y-auto">
                                    <DropdownMenuItem
                                        v-for="notification in notifications"
                                        :key="notification.id"
                                        @click="handleNotificationClick(notification)"
                                        class="cursor-pointer px-3 py-3 focus:bg-accent"
                                        :class="{ 'bg-accent/50': !notification.is_read }"
                                    >
                                        <div class="flex gap-3 w-full">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <div class="h-2 w-2 rounded-full bg-primary" v-if="!notification.is_read"></div>
                                                <div class="h-2 w-2" v-else></div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium line-clamp-1">{{ notification.title }}</p>
                                                <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ notification.message }}</p>
                                                <div class="flex items-center gap-1 mt-1 text-xs text-muted-foreground">
                                                    <Clock class="h-3 w-3" />
                                                    {{ formatNotificationTime(notification.created_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </DropdownMenuItem>
                                </div>
                                
                                <DropdownMenuSeparator v-if="notifications.length > 0" />
                                
                                <DropdownMenuItem v-if="notifications.length > 0" as-child class="cursor-pointer justify-center">
                                    <Link href="/notifications" class="text-sm font-medium text-primary">
                                        View All Notifications
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <div class="hidden space-x-1 lg:flex">
                            <template
                                v-for="item in rightNavItems"
                                :key="item.title"
                            >
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                as-child
                                                class="group h-9 w-9 cursor-pointer"
                                            >
                                                <a
                                                    :href="toUrl(item.href)"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <span class="sr-only">{{
                                                        item.title
                                                    }}</span>
                                                    <component
                                                        :is="item.icon"
                                                        class="size-5 opacity-80 group-hover:opacity-100"
                                                    />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
