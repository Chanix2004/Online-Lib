

// Initialize interactions on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeFormValidation();
    initializeButtons();
    initializeAlerts();
    initializeTooltips();
});

// ========== FORM VALIDATION ==========
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('.form-control');
        
        inputs.forEach(input => {
            // Real-time validation feedback
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('focus', function() {
                this.parentElement.classList.remove('has-error');
                this.parentElement.classList.remove('has-success');
            });
            
            // Password strength indicator
            if (input.type === 'password') {
                input.addEventListener('input', function() {
                    showPasswordStrength(this);
                });
            }
            
            // Email validation
            if (input.type === 'email') {
                input.addEventListener('blur', function() {
                    if (this.value && !isValidEmail(this.value)) {
                        this.classList.add('is-invalid');
                        showToast('Invalid email address', 'error');
                    } else if (this.value) {
                        this.classList.add('is-valid');
                    }
                });
            }
        });
        
        // Form submit animation
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-primary');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const fieldGroup = field.parentElement;
    
    if (!value) {
        field.classList.add('is-invalid');
        fieldGroup.classList.add('has-error');
        return false;
    } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        fieldGroup.classList.add('has-success');
        return true;
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showPasswordStrength(input) {
    const password = input.value;
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Add visual indicator if needed
    if (strength < 2) {
        input.style.borderColor = 'var(--danger)';
    } else if (strength < 4) {
        input.style.borderColor = 'var(--warning)';
    } else {
        input.style.borderColor = 'var(--success)';
    }
}

// ========== BUTTON INTERACTIONS ==========
function initializeButtons() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(btn => {
        // Don't add ripple effect - it causes text shifting
        // Hover effect is now handled by CSS
    });
}

// ========== ALERT MANAGEMENT ==========
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Auto-dismiss alerts after 5 seconds
        if (!alert.classList.contains('alert-info')) {
            setTimeout(() => {
                dismissAlert(alert);
            }, 5000);
        }
        
        // New alerts fade in
        alert.style.animation = 'slideIn 0.3s ease';
    });
}

function dismissAlert(alert) {
    alert.classList.add('dismissing');
    setTimeout(() => {
        alert.style.display = 'none';
    }, 300);
}

// ========== TOAST NOTIFICATIONS ==========
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        color: var(--text-primary);
        z-index: 9999;
        animation: slideIn 0.3s ease;
        box-shadow: var(--shadow);
        max-width: 300px;
        word-wrap: break-word;
    `;
    
    // Set icon based on type
    let icon = 'ℹ️';
    if (type === 'success') icon = '✅';
    if (type === 'error') icon = '❌';
    if (type === 'warning') icon = '⚠️';
    
    toast.innerHTML = `<span style="margin-right: 0.5rem;">${icon}</span> ${message}`;
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ========== TOOLTIPS ==========
function initializeTooltips() {
    const elementsWithTooltip = document.querySelectorAll('[data-tooltip]');
    
    elementsWithTooltip.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: rgba(0, 0, 0, 0.9);
                color: var(--text-primary);
                padding: 0.5rem 0.75rem;
                border-radius: 6px;
                font-size: 0.85rem;
                white-space: nowrap;
                pointer-events: none;
                z-index: 1000;
                animation: slideIn 0.2s ease;
            `;
            
            this.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (-tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (this.offsetWidth / 2 - tooltip.offsetWidth / 2) + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.style.animation = 'fadeOut 0.2s ease forwards';
                setTimeout(() => tooltip.remove(), 200);
            }
        });
    });
}

// ========== SIDEBAR TOGGLE ==========
// MOVED TO header.php - toggleMenu() and toggleSidebar() are now handled there

// ========== SMOOTH PAGE TRANSITIONS ==========
window.addEventListener('beforeunload', function() {
    document.body.style.animation = 'fadeOut 0.3s ease';
});

// ========== KEYBOARD SHORTCUTS ==========
document.addEventListener('keydown', function(e) {
    // ESC to close modals/menus
    if (e.key === 'Escape') {
        const userMenu = document.getElementById('userMenu');
        if (userMenu) userMenu.style.display = 'none';
    }
    
    // CTRL/CMD + S to focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const searchInput = document.querySelector('[data-search]');
        if (searchInput) searchInput.focus();
    }
});

// ========== SMOOTH SCROLL ==========
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ========== ANIMATIONS FOR STATS NUMBERS ==========
function animateCounter(element, target, duration = 1000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// Animate all stats numbers on page load
window.addEventListener('load', function() {
    const statsNumbers = document.querySelectorAll('.stats-number');
    statsNumbers.forEach(stat => {
        const targetValue = parseInt(stat.textContent);
        if (!isNaN(targetValue)) {
            animateCounter(stat, targetValue, 800);
        }
    });
});

// ========== FORM FIELD FLOATING LABELS ==========
// DISABLED: Floating labels cause layout issues with flexbox form layout


// ========== EXPORT ==========
window.LibrarySystem = {
    showToast,
    validateField,
    toggleMenu,
    animateCounter
};

