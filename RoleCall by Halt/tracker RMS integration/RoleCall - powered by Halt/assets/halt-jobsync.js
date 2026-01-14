/**
 * Halt JobSync Plugin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sync modal functionality
    initSyncModal();
    
    // Initialize any other functionality
    initGeneralFeatures();
    
    // Initialize countdown timer
    initCountdownTimer();
});

/**
 * Initialize the sync modal functionality
 */
function initSyncModal() {
    const syncForm = document.getElementById('halt-sync-form');
    const syncButton = document.getElementById('halt-sync-button');
    const modal = document.getElementById('halt-sync-modal');
    
    if (syncForm && syncButton && modal) {
        syncForm.addEventListener('submit', function(e) {
            // Show the modal immediately
            modal.style.display = 'block';
            
            // Disable the button to prevent double-clicks
            syncButton.disabled = true;
            syncButton.textContent = 'Syncing...';
            
            // Allow the form to submit normally - don't prevent default
            // The modal will stay visible until the page redirects
        });
        
        // Hide modal if page is refreshed (sync completed)
        if (window.location.search.includes('synced=1')) {
            modal.style.display = 'none';
        }
    }
}

/**
 * Initialize general features
 */
function initGeneralFeatures() {
    // Add any general JavaScript functionality here
    // For example: form validation, tooltips, etc.
    
    // Example: Add confirmation to clear sync lock button
    const clearLockButtons = document.querySelectorAll('form[action*="halt_tracker_clear_lock"]');
    clearLockButtons.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to clear the sync lock? This should only be done if a sync appears to be stuck.')) {
                e.preventDefault();
            }
        });
    });
    
    // Example: Add JSON validation to field mapping textarea
    const fieldMappingTextarea = document.getElementById('halt_tracker_field_map');
    if (fieldMappingTextarea) {
        fieldMappingTextarea.addEventListener('blur', function() {
            validateJsonField(this);
        });
    }
}

/**
 * Toggle sync report accordion
 */
function toggleSyncReport(index) {
    const toggle = document.getElementById('toggle-' + index);
    const content = document.getElementById('content-' + index);
    
    if (content.classList.contains('expanded')) {
        // Collapse
        content.classList.remove('expanded');
        toggle.classList.remove('expanded');
    } else {
        // Expand
        content.classList.add('expanded');
        toggle.classList.add('expanded');
    }
}

/**
 * Toggle individual job item accordion
 */
function toggleJobItem(reportIndex, jobIndex) {
    const toggle = document.getElementById('job-toggle-' + reportIndex + '-' + jobIndex);
    const content = document.getElementById('job-content-' + reportIndex + '-' + jobIndex);
    
    if (content.classList.contains('expanded')) {
        // Collapse
        content.classList.remove('expanded');
        toggle.classList.remove('expanded');
    } else {
        // Expand
        content.classList.add('expanded');
        toggle.classList.add('expanded');
    }
}

/**
 * Validate JSON field mapping
 */
function validateJsonField(textarea) {
    const value = textarea.value.trim();
    if (value === '') {
        removeFieldError(textarea);
        return;
    }
    
    try {
        const parsed = JSON.parse(value);
        if (typeof parsed !== 'object' || parsed === null) {
            throw new Error('Field mapping must be a valid JSON object');
        }
        removeFieldError(textarea);
    } catch (error) {
        showFieldError(textarea, 'Invalid JSON format: ' + error.message);
    }
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    removeFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'halt-field-error';
    errorDiv.style.color = '#d63384';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = '#d63384';
}

/**
 * Remove field error
 */
function removeFieldError(field) {
    const existingError = field.parentNode.querySelector('.halt-field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '#ddd';
}

/**
 * Utility function to show loading state
 */
function showLoading(element) {
    if (element) {
        element.disabled = true;
        const originalText = element.textContent;
        element.setAttribute('data-original-text', originalText);
        element.textContent = 'Loading...';
    }
}

/**
 * Utility function to hide loading state
 */
function hideLoading(element) {
    if (element) {
        element.disabled = false;
        const originalText = element.getAttribute('data-original-text');
        if (originalText) {
            element.textContent = originalText;
        }
    }
}

/**
 * Initialize countdown timer for next sync
 */
function initCountdownTimer() {
    const countdownElement = document.getElementById('countdown-timer');
    if (!countdownElement) return;
    
    updateCountdown();
    
    // Update every second for real-time countdown
    setInterval(updateCountdown, 1000);
}

/**
 * Update the countdown timer display
 */
function updateCountdown() {
    const countdownElement = document.getElementById('countdown-timer');
    if (!countdownElement) return;
    
    const now = new Date();
    const nextSync = getNextSyncTime(now);
    const timeDiff = nextSync - now;
    
    if (timeDiff <= 0) {
        countdownElement.innerHTML = '<span>Sync in progress...</span>';
        return;
    }
    
    const hours = Math.floor(timeDiff / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
    
    countdownElement.innerHTML = `
        <span class="time-unit">
            ${hours.toString().padStart(2, '0')}
            <span class="time-label">Hours</span>
        </span>
        <span class="time-unit">
            ${minutes.toString().padStart(2, '0')}
            <span class="time-label">Minutes</span>
        </span>
        <span class="time-unit">
            ${seconds.toString().padStart(2, '0')}
            <span class="time-label">Seconds</span>
        </span>
    `;
}

/**
 * Calculate the next sync time (9am, midday, 6pm, or midnight UTC)
 */
function getNextSyncTime(now) {
    const intervalMs = 5 * 60 * 1000; // every 5 minutes
    const currentMs = now.getTime();
    const nextMs = Math.ceil(currentMs / intervalMs) * intervalMs;
    return new Date(nextMs);
}

/**
 * Utility function to show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `halt-notification halt-notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        color: white;
        font-family: Arial, sans-serif;
        z-index: 1000000;
        max-width: 300px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    `;
    
    switch (type) {
        case 'success':
            notification.style.background = '#28a745';
            break;
        case 'error':
            notification.style.background = '#dc3545';
            break;
        case 'warning':
            notification.style.background = '#ffc107';
            notification.style.color = '#000';
            break;
        default:
            notification.style.background = '#0073aa';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
