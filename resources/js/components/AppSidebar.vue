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
    clinicDashboard, 
    clinicAppointments, 
    clinicPatients, 
    clinicScheduleManagement,
    clinicServices,
    clinicBilling,
    clinicReports,
    clinicInventory,
    clinicStaff,
    adminDashboard
} from '@/routes';
import admin from '@/routes/admin';
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
    FileText,
    Wrench,
    Home,
    MapPin,
    Clock,
    History as HistoryIcon,
    Heart,
    CreditCard,
    Bell
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

// More reactive approach to account type detection with explicit dependency tracking
const currentUser = computed(() => {
    const user = page.props.auth?.user;
    console.log('🧭 currentUser computed:', { user: user });
    return user;
});

const isClinic = computed(() => {
    const user = currentUser.value;
    const result = user?.is_clinic || user?.account_type === 'clinic';
    console.log('🏥 isClinic computed:', { 
        user: user, 
        result: result, 
        account_type: user?.account_type, 
        is_clinic: user?.is_clinic,
        timestamp: new Date().toISOString()
    });
    return result;
});

const isAdmin = computed(() => {
    const user = currentUser.value;
    const result = user?.is_admin || user?.account_type === 'admin';
    console.log('👑 isAdmin computed:', { 
        user: user, 
        result: result, 
        account_type: user?.account_type, 
        is_admin: user?.is_admin,
        timestamp: new Date().toISOString()
    });
    return result;
});

const userNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: Home,
    },
    {
        title: 'Clinics',
        href: clinics().url,
        icon: Building2,
    },
    {
        title: 'Schedule',
        href: schedule().url,
        icon: Clock,
    },
    {
        title: 'Calendar',
        href: appointmentCalendar().url,
        icon: Calendar,
    },
    {
        title: 'History',
        href: history().url,
        icon: HistoryIcon,
    },
     {
        title: 'Pet',
        href: petsIndex().url,
        icon: Heart,
    },
];

const clinicNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: clinicDashboard().url,
        icon: Home,
    },
    {
        title: 'Appointments',
        href: clinicAppointments().url,
        icon: Calendar,
    },
    {
        title: 'Patients',
        href: clinicPatients().url,
        icon: Users,
    },
    {
        title: 'Schedule Management',
        href: clinicScheduleManagement().url,
        icon: CalendarClock,
    },
    {
        title: 'Services & Pricing',
        href: clinicServices().url,
        icon: Stethoscope,
    },
    {
        title: 'Billing & Invoicing',
        href: clinicBilling().url,
        icon: DollarSign,
    },
    {
        title: 'Reports & Analytics',
        href: clinicReports().url,
        icon: BarChart,
    },
    {
        title: 'Inventory',
        href: clinicInventory().url,
        icon: Package,
    },
    {
        title: 'Staff Management',
        href: clinicStaff().url,
        icon: UserCheck,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: adminDashboard().url,
        icon: Home,
    },
    {
        title: 'System Monitoring',
        href: admin.systemMonitoring().url,
        icon: Monitor,
    },
    {
        title: 'User Management',
        href: admin.userManagement().url,
        icon: UserCog,
    },
    {
        title: 'Clinic Management',
        href: '/admin/clinic-management',
        icon: Building2,
    },
    {
        title: 'Reports & Analytics',
        href: admin.reports().url,
        icon: BarChart,
    },
    {
        title: 'System Maintenance',
        href: admin.systemMaintenance().url,
        icon: Wrench,
    },
    {
        title: 'Security Center',
        href: admin.securityCenter().url,
        icon: Shield,
    },
];

const mainNavItems = computed(() => {
    // Ensure reactive dependencies are tracked
    const adminCheck = isAdmin.value;
    const clinicCheck = isClinic.value;
    
    console.log('🧭 mainNavItems computed:', { 
        adminCheck, 
        clinicCheck, 
        resultType: adminCheck ? 'admin' : clinicCheck ? 'clinic' : 'user',
        timestamp: new Date().toISOString()
    });
    
    if (adminCheck) return adminNavItems;
    if (clinicCheck) return clinicNavItems;
    return userNavItems;
});

const dashboardLink = computed(() => {
    // Ensure reactive dependencies are tracked
    const adminCheck = isAdmin.value;
    const clinicCheck = isClinic.value;
    
    console.log('🏠 dashboardLink computed:', { 
        adminCheck, 
        clinicCheck,
        timestamp: new Date().toISOString()
    });
    
    if (adminCheck) return adminDashboard().url;
    if (clinicCheck) return clinicDashboard().url;
    return dashboard().url;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Subscriptions',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: CreditCard,
    },
    {
        title: 'Notifications',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: Bell,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" :key="`sidebar-${currentUser?.id}-${currentUser?.account_type}`">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardLink">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
