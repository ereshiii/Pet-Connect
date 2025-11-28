import { router } from '@inertiajs/vue3';

// Global error handler for Inertia.js
export const setupGlobalErrorHandler = () => {
    // Handle global errors (like 500 errors that don't get caught by onError)
    router.on('error', (event) => {
        console.error('Global Inertia error:', event.detail);
        console.error('Full error details:', JSON.stringify(event.detail, null, 2));
        
        // Log specific error properties if available
        if (event.detail?.errors) {
            console.error('Validation errors:', event.detail.errors);
        }
        if (event.detail?.message) {
            console.error('Error message:', event.detail.message);
        }
        
        // You can dispatch a global event or use a global store here
        window.dispatchEvent(new CustomEvent('inertia-error', {
            detail: {
                error: event.detail,
                suggestions: [
                    'Check your internet connection',
                    'Refresh the page and try again',
                    'Contact support if the issue persists'
                ]
            }
        }));
    });

    // Handle promise rejections (for uncaught server errors)
    window.addEventListener('unhandledrejection', (event) => {
        console.error('Unhandled promise rejection:', event.reason);
        
        // Parse error if it's from a server response
        let errorMessage = 'An unexpected error occurred';
        let technicalDetails = '';
        
        if (event.reason?.response) {
            const response = event.reason.response;
            errorMessage = `Server Error (${response.status}): ${response.statusText}`;
            technicalDetails = JSON.stringify(response.data || response, null, 2);
        } else if (event.reason?.message) {
            errorMessage = event.reason.message;
            technicalDetails = event.reason.stack || '';
        }
        
        window.dispatchEvent(new CustomEvent('global-error', {
            detail: {
                title: 'Unexpected Error',
                message: errorMessage,
                technicalDetails,
                suggestions: [
                    'Refresh the page and try again',
                    'Check your internet connection',
                    'Clear your browser cache',
                    'Contact support if the problem continues'
                ]
            }
        }));
    });
};

// Helper function to show user-friendly error messages
export const handleFormError = (errors: any, form?: any) => {
    let errorData = {
        title: 'Error',
        message: '',
        validationErrors: {} as Record<string, string | string[]>,
        technicalDetails: '',
        suggestions: [] as string[],
    };

    if (errors && typeof errors === 'object' && !Array.isArray(errors)) {
        // Check if it's validation errors (422)
        const hasValidationFields = Object.keys(errors).some(key => 
            typeof errors[key] === 'string' || Array.isArray(errors[key])
        );
        
        if (hasValidationFields) {
            errorData = {
                title: 'Validation Error',
                message: 'Please check the form and correct any errors.',
                validationErrors: errors,
                technicalDetails: '',
                suggestions: [
                    'Review all required fields',
                    'Check date and time formats',
                    'Ensure phone numbers are valid',
                    'Make sure selections are from available options'
                ],
            };
        } else {
            // Server error with details
            errorData = {
                title: 'Server Error',
                message: 'We encountered an issue processing your request.',
                validationErrors: {},
                technicalDetails: JSON.stringify(errors, null, 2),
                suggestions: [
                    'Try again in a few moments',
                    'Check your internet connection',
                    'Contact support if the issue persists'
                ],
            };
        }
    } else {
        // Generic error
        errorData = {
            title: 'Unexpected Error',
            message: 'Something went wrong. Please try again.',
            validationErrors: {},
            technicalDetails: typeof errors === 'string' ? errors : JSON.stringify(errors, null, 2),
            suggestions: [
                'Refresh the page and try again',
                'Check your internet connection',
                'Contact support if the problem continues'
            ],
        };
    }

    return errorData;
};

// Helper to format server errors for display
export const formatServerError = (error: any) => {
    if (error?.response?.data) {
        const data = error.response.data;
        
        // Laravel error format
        if (data.message) {
            return {
                title: `Error ${error.response.status || ''}`,
                message: data.message,
                technicalDetails: data.exception || JSON.stringify(data, null, 2),
                suggestions: [
                    'Check your input data',
                    'Try again in a few moments',
                    'Contact support if the issue persists'
                ]
            };
        }
    }
    
    return {
        title: 'Server Error',
        message: 'An unexpected server error occurred.',
        technicalDetails: JSON.stringify(error, null, 2),
        suggestions: [
            'Try refreshing the page',
            'Check your internet connection',
            'Contact support if the problem continues'
        ]
    };
};