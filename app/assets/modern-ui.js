/**
 * Modern KeyAuth UI Enhancement Library
 * Properly integrates with Flowbite for modal management
 */

// Wait for everything to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern UI initializing...');
    
    // Hide the loader immediately
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'none';
        console.log('Loader hidden successfully');
    }
    
    // Wait a bit for Flowbite to fully load
    setTimeout(() => {
        initializeModals();
        initializeEnhancements();
    }, 500);
});

// Initialize all Flowbite modals manually
function initializeModals() {
    if (typeof Modal === 'undefined') {
        console.error('Flowbite Modal not loaded');
        return;
    }

    // List of modal IDs to initialize
    const modalIds = [
        'pause-app-modal',
        'unpause-app-modal',
        'refresh-app-modal',
        'delete-app-modal',
        'create-app-modal',
        'rename-app-modal'
    ];

    modalIds.forEach(modalId => {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            try {
                // Initialize modal with Flowbite
                const modal = new Modal(modalElement, {
                    placement: 'center',
                    backdrop: 'dynamic',
                    backdropClasses: 'bg-gray-900 bg-opacity-50 fixed inset-0 z-40',
                    closable: true,
                    onHide: () => {
                        modalElement.classList.remove('modal-animate-in');
                        modalElement.classList.add('modal-animate-out');
                        setTimeout(() => {
                            modalElement.classList.remove('modal-animate-out');
                        }, 300);
                    },
                    onShow: () => {
                        modalElement.classList.add('modal-animate-in');
                        modalElement.classList.remove('modal-animate-out');
                    }
                });

                console.log(`Modal initialized: ${modalId}`);

                // Set up trigger buttons
                const triggers = document.querySelectorAll(`[data-modal-target="${modalId}"], [data-modal-toggle="${modalId}"]`);
                triggers.forEach(trigger => {
                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        modal.show();
                    });
                });

                // Set up close buttons
                const closeButtons = modalElement.querySelectorAll(`[data-modal-hide="${modalId}"]`);
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        // Only prevent default if it's not a form submit button
                        if (btn.type !== 'submit') {
                            e.preventDefault();
                        }
                        modal.hide();
                    });
                });

            } catch (error) {
                console.error(`Error initializing modal ${modalId}:`, error);
            }
        }
    });
}

// Initialize other UI enhancements
function initializeEnhancements() {
    setupAnimations();
    setupCopyButtons();
    setupNotifications();
}

// Enhanced Animations
function setupAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all cards and sections
    document.querySelectorAll('.modern-card, .content-section, .animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// Enhanced Copy to Clipboard
function setupCopyButtons() {
    document.querySelectorAll('.copy-button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const targetId = button.getAttribute('data-copy-target');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                try {
                    await navigator.clipboard.writeText(targetElement.textContent);
                    
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Copied!';
                    button.classList.add('bg-green-600');
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('bg-green-600');
                    }, 2000);
                    
                    showToast('Copied to clipboard!', 'success');
                } catch (err) {
                    console.error('Failed to copy:', err);
                    showToast('Failed to copy to clipboard', 'error');
                }
            }
        });
    });
}

// Notification System
function setupNotifications() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-20 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(container);
    }
}

function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    const icons = {
        success: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>',
        error: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>',
        warning: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
        info: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
    };

    const toast = document.createElement('div');
    toast.className = `flex items-center gap-3 ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 max-w-sm`;
    toast.innerHTML = `
        ${icons[type] || icons.info}
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="hover:opacity-75">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
    `;

    container.appendChild(toast);

    // Slide in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);

    // Auto remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Make showToast available globally
window.showToast = showToast;

console.log('Modern UI loaded successfully');
