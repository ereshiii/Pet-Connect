// Calendar component TypeScript interfaces and types

export interface CalendarEvent {
    id: string | number;
    title: string;
    date: string; // YYYY-MM-DD format
    time?: string;
    type?: 'appointment' | 'reminder' | 'event' | 'holiday' | 'blocked' | 'custom';
    color?: string; // Tailwind CSS class or hex color
    description?: string;
    status?: 'scheduled' | 'pending' | 'cancelled' | 'completed' | 'rescheduled';
    category?: string;
    priority?: 'low' | 'medium' | 'high' | 'urgent';
    location?: string;
    attendees?: string[];
    metadata?: Record<string, any>; // For custom data
}

export interface CalendarDay {
    date: number;
    fullDate: string; // YYYY-MM-DD format
    isCurrentMonth: boolean;
    isPrevMonth: boolean;
    isNextMonth: boolean;
    isToday: boolean;
    isWeekend: boolean;
    isDisabled: boolean;
    isSelected: boolean;
    weekNumber: number;
    events?: CalendarEvent[];
}

export interface CalendarProps {
    // Display options
    showWeekNumbers?: boolean;
    showWeekends?: boolean;
    highlightToday?: boolean;
    highlightWeekends?: boolean;
    
    // Selection options
    selectable?: boolean;
    multiSelect?: boolean;
    selectMode?: 'single' | 'range' | 'multiple';
    
    // Event handling
    events?: CalendarEvent[];
    showEvents?: boolean;
    maxEventsPerDay?: number;
    
    // Date constraints
    minDate?: string;
    maxDate?: string;
    disabledDates?: string[];
    disabledDaysOfWeek?: number[]; // 0=Sunday, 1=Monday, etc.
    
    // Initial values
    initialDate?: string;
    initialView?: 'month' | 'week' | 'day';
    
    // Styling options
    size?: 'small' | 'medium' | 'large';
    theme?: 'default' | 'minimal' | 'professional';
    
    // Locale options
    locale?: string;
    firstDayOfWeek?: number; // 0=Sunday, 1=Monday
    
    // Custom styling
    customClasses?: {
        container?: string;
        header?: string;
        dayName?: string;
        day?: string;
        today?: string;
        selected?: string;
        event?: string;
        disabled?: string;
    };
}

export interface CalendarEmits {
    'date-select': [date: string];
    'date-range-select': [startDate: string, endDate: string];
    'multiple-dates-select': [dates: string[]];
    'event-click': [event: CalendarEvent];
    'event-hover': [event: CalendarEvent];
    'month-change': [year: number, month: number];
    'view-change': [view: string];
    'day-hover': [date: string];
    'day-double-click': [date: string];
    'week-click': [weekNumber: number];
}

export interface CalendarMethods {
    // Navigation
    goToPrevMonth: () => void;
    goToNextMonth: () => void;
    goToToday: () => void;
    goToDate: (date: string) => void;
    
    // Selection
    clearSelection: () => void;
    selectDate: (date: string) => void;
    selectDateRange: (startDate: string, endDate: string) => void;
    getSelectedDates: () => string[];
    
    // Events
    getEventsForDate: (date: string) => CalendarEvent[];
    addEvent: (event: CalendarEvent) => void;
    removeEvent: (eventId: string | number) => void;
    updateEvent: (eventId: string | number, updates: Partial<CalendarEvent>) => void;
    
    // Utility
    isDateDisabled: (date: string) => boolean;
    isDateSelected: (date: string) => boolean;
    formatDate: (date: Date) => string;
    parseDate: (dateString: string) => Date;
}

// Appointment-specific interfaces for PetConnect
export interface AppointmentEvent extends CalendarEvent {
    type: 'appointment';
    petId?: string | number;
    petName?: string;
    petType?: 'dog' | 'cat' | 'bird' | 'rabbit' | 'other';
    clinicId?: string | number;
    clinicName?: string;
    doctorId?: string | number;
    doctorName?: string;
    duration?: number; // minutes
    cost?: number;
    services?: string[];
    confirmationNumber?: string;
    clientId?: string | number;
    clientName?: string;
    clientPhone?: string;
    clientEmail?: string;
    notes?: string;
    followUpRequired?: boolean;
    vaccinations?: string[];
    medicalConditions?: string[];
}

export interface HolidayEvent extends CalendarEvent {
    type: 'holiday';
    isNational?: boolean;
    isRegional?: boolean;
    closedClinic?: boolean;
    alternativeHours?: string;
}

export interface ReminderEvent extends CalendarEvent {
    type: 'reminder';
    reminderType?: 'vaccine_due' | 'followup' | 'medication' | 'general';
    autoSend?: boolean;
    sendMethods?: ('email' | 'sms' | 'phone')[];
    daysBeforeReminder?: number;
    completed?: boolean;
}

// Calendar view types
export type CalendarView = 'month' | 'week' | 'day' | 'agenda';

// Calendar theme configuration
export interface CalendarTheme {
    name: string;
    colors: {
        primary: string;
        secondary: string;
        background: string;
        text: string;
        border: string;
        today: string;
        selected: string;
        event: string;
        weekend: string;
        disabled: string;
    };
    spacing: {
        padding: string;
        margin: string;
        gap: string;
    };
    typography: {
        fontSize: string;
        fontWeight: string;
        lineHeight: string;
    };
}

// Utility types
export type DateString = string; // YYYY-MM-DD format
export type TimeString = string; // HH:MM AM/PM format
export type DateTimeString = string; // ISO 8601 format

// Calendar configuration for different use cases
export interface VeterinaryCalendarConfig extends CalendarProps {
    showAppointmentTypes?: boolean;
    showClientInfo?: boolean;
    showPetInfo?: boolean;
    allowWalkIns?: boolean;
    emergencySlots?: boolean;
    operatingHours?: {
        start: string;
        end: string;
        lunch?: { start: string; end: string };
    };
    slotDuration?: number; // minutes
    maxAdvanceBooking?: number; // days
}

export default CalendarEvent;