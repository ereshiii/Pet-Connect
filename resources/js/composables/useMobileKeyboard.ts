/**
 * Mobile Keyboard Composable
 * 
 * Handles mobile keyboard visibility issues by automatically scrolling
 * focused input fields into view when the virtual keyboard appears.
 * 
 * Usage:
 * ```ts
 * import { useMobileKeyboard } from '@/composables/useMobileKeyboard';
 * 
 * const { handleInputFocus } = useMobileKeyboard();
 * 
 * // In template:
 * <form @focusin="handleInputFocus">
 *   <!-- form fields -->
 * </form>
 * ```
 */

export function useMobileKeyboard() {
  /**
   * Handles input focus events on mobile devices
   * Scrolls the focused element into view to prevent keyboard overlap
   * 
   * @param event - The focus event triggered by user interaction
   */
  const handleInputFocus = (event: FocusEvent) => {
    const target = event.target as HTMLElement;
    
    // Only apply on mobile devices (viewport width <= 768px)
    if (window.innerWidth <= 768) {
      // Check if the target is an input, textarea, or select element
      if (
        target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.tagName === 'SELECT'
      ) {
        // Delay to allow keyboard animation to complete
        setTimeout(() => {
          // Scroll element into view with center alignment
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
          });
          
          // Additional scroll adjustment to account for keyboard height
          // This ensures the element is fully visible above the keyboard
          const yOffset = -50; // Offset from top in pixels
          const y = target.getBoundingClientRect().top + window.pageYOffset + yOffset;
          
          window.scrollTo({
            top: y,
            behavior: 'smooth'
          });
        }, 300); // 300ms delay for keyboard animation
      }
    }
  };

  return {
    handleInputFocus
  };
}
