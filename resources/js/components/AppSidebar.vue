<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { 
    dashboard, 
    schedule, 
    history, 
    clinics, 
    petsIndex, 
    appointmentCalendar,
    bookingHistory,
    clinicDashboard, 
    clinicAppointments, 
    clinicHistory,
    clinicPatients, 
    clinicScheduleManagement,
    clinicServices,
    clinicReports,
    adminDashboard
} from '@/routes';
import admin from '@/routes/admin';
import user from '@/routes/user';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid, 
    Calendar,
    Users,
    ClipboardList,
    DollarSign,
    BarChart,
    Package,
    UserCheck,
    Stethoscope,
    CalendarClock,
    Building2,
    Shield,
    Monitor,
    UserCog,
    Settings,
    Home,
    Wrench,
    History as HistoryIcon,
    Bell,
    CreditCard,
    FlaskConical,
    FileText,
    MapPin,
    Clock,
    Heart,
    PawPrint,
    Phone,
    Images,
    Activity
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

// More reactive approach to account type detection with explicit dependency tracking
const currentUser = computed(() => {
    const user = page.props.auth?.user;
    return user;
});

const isClinic = computed(() => {
    const user = currentUser.value;
    const result = user?.is_clinic || user?.account_type === 'clinic';
    return result;
});

const isAdmin = computed(() => {
    const user = currentUser.value;
    const result = user?.is_admin || user?.account_type === 'admin';
    return result;
});

const userNavItems = [
    {
        category: 'Pet Management',
        items: [
            {
                title: 'My Pets',
                href: petsIndex().url,
                icon: PawPrint,
            },
            {
                title: 'Appointments',
                href: appointmentCalendar().url,
                icon: Calendar,
            },
        ]
    },
    {
        category: 'Services',
        items: [
            {
                title: 'Find Clinics',
                href: clinics().url,
                icon: Building2,
            },
            {
                title: 'Favorited Clinics',
                href: user.favorites.index().url,
                icon: Heart,
            },
        ]
    },
    {
        category: 'Booking',
        items: [
            {
                title: 'Booking History',
                href: bookingHistory().url,
                icon: Clock,
            },
        ]
    },
];

const clinicNavItems = [
    {
        category: 'Patient Management',
        items: [
            {
                title: 'Appointments',
                href: clinicAppointments().url,
                icon: Calendar,
            },
            {
                title: 'History',
                href: clinicHistory().url,
                icon: HistoryIcon,
            },
            {
                title: 'Patients',
                href: clinicPatients().url,
                icon: Users,
            },
        ]
    },
    {
        category: 'Clinic Operations',
        items: [
            {
                title: 'Schedule Management',
                href: clinicScheduleManagement().url,
                icon: CalendarClock,
            },
            {
                title: 'Staff Management',
                href: '/clinic/staff',
                icon: UserCheck,
            },
            {
                title: 'Services',
                href: clinicServices().url,
                icon: Stethoscope,
            },
        ]
    },
    {
        category: 'Clinic Profile',
        items: [
            {
                title: 'Profile',
                href: '/clinic/settings/clinic-profile',
                icon: Building2,
            },
            {
                title: 'Gallery',
                href: '/clinic/settings/clinic-gallery',
                icon: Images,
            },
        ]
    },
    {
        category: 'Reports & Analytics',
        items: [
            {
                title: 'Overview',
                href: clinicReports().url,
                icon: BarChart,
            },
            {
                title: 'Patient Analytics',
                href: '/clinic/reports/patients',
                icon: Users,
            },
            {
                title: 'Services Analytics',
                href: '/clinic/reports/services',
                icon: Stethoscope,
            },
            {
                title: 'Clinic Reviews',
                href: '/clinic/reports/reviews',
                icon: Activity,
            },
        ]
    },
];

const adminNavItems = [
    {
        category: 'User Management',
        items: [
            {
                title: 'Overview',
                href: '/admin/user-management/overview',
                icon: Users,
            },
            {
                title: 'Admins',
                href: '/admin/user-management/admins',
                icon: Shield,
            },
            {
                title: 'Pet Owners',
                href: '/admin/user-management/pet-owners',
                icon: PawPrint,
            },
            {
                title: 'Clinics',
                href: '/admin/user-management/clinics',
                icon: Building2,
            },
        ]
    },
    {
        category: 'System Monitoring',
        items: [
            {
                title: 'Overview',
                href: '/admin/system-monitoring/overview',
                icon: Activity,
            },
            {
                title: 'Server',
                href: '/admin/system-monitoring/server',
                icon: Monitor,
            },
            {
                title: 'Database',
                href: '/admin/system-monitoring/database',
                icon: Folder,
            },
            {
                title: 'Security',
                href: '/admin/system-monitoring/security',
                icon: Shield,
            },
        ]
    },
    {
        category: 'Financial',
        items: [
            {
                title: 'Subscriptions',
                href: '/admin/financial/subscriptions',
                icon: CreditCard,
            },
        ]
    },
    {
        category: 'Tools',
        items: [
            {
                title: 'Test Cards',
                href: '/admin/testing-tools/mock-payment',
                icon: DollarSign,
            },
            {
                title: 'Subscription Removal',
                href: '/admin/testing-tools/subscription-removal',
                icon: CreditCard,
            },
            {
                title: 'Account Reset',
                href: '/admin/testing-tools/account-reset',
                icon: FlaskConical,
            },
        ]
    },
];

const mainNavItems = computed(() => {
    // Ensure reactive dependencies are tracked
    const adminCheck = isAdmin.value;
    const clinicCheck = isClinic.value;
    
    if (adminCheck) return adminNavItems;
    if (clinicCheck) return clinicNavItems;
    return userNavItems;
});

const dashboardLink = computed(() => {
    // Ensure reactive dependencies are tracked
    const adminCheck = isAdmin.value;
    const clinicCheck = isClinic.value;
    
    if (adminCheck) return adminDashboard().url;
    if (clinicCheck) return clinicDashboard().url;
    return dashboard().url;
});

const footerNavItems = computed<NavItem[]>(() => {
    return [];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" :key="`sidebar-${currentUser?.id}-${currentUser?.account_type}`">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg">
                        <Link :href="dashboardLink" class="flex w-full items-center gap-2">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>
    </Sidebar>
    <slot />
</template>
