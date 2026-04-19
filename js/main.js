// Library Management System - Main JavaScript

// Utility Functions
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '70px';
    alertDiv.style.right = '20px';
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.zIndex = '1000';
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-IN');
}

function confirmAction(message = 'Are you sure?') {
    return confirm(message);
}

// API Functions
async function apiCall(endpoint, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(endpoint, options);
        const result = await response.json();

        return result;
    } catch (error) {
        console.error('API Error:', error);
        showNotification('An error occurred. Please try again.', 'danger');
        return { success: false, message: 'Network error' };
    }
}

// Search Functions
function searchBooks(searchTerm) {
    const url = new URL(window.location.href);
    url.searchParams.set('search', searchTerm);
    window.location.href = url.toString();
}

function filterByCategory(categoryId) {
    const url = new URL(window.location.href);
    url.searchParams.set('category', categoryId);
    url.searchParams.delete('search');
    window.location.href = url.toString();
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            const errorDiv = input.parentElement.querySelector('.form-error');
            if (errorDiv) {
                errorDiv.classList.add('show');
                errorDiv.textContent = input.name + ' is required';
            }
            isValid = false;
        } else {
            input.classList.remove('error');
            const errorDiv = input.parentElement.querySelector('.form-error');
            if (errorDiv) {
                errorDiv.classList.remove('show');
            }
        }
    });
    
    return isValid;
}

// File Upload Validation
function validateFileUpload(inputElement, maxSize, allowedTypes) {
    const file = inputElement.files[0];
    
    if (!file) {
        return true;
    }
    
    // Check file size
    if (file.size > maxSize) {
        showNotification(`File size exceeds maximum of ${maxSize / 1024 / 1024}MB`, 'danger');
        inputElement.value = '';
        return false;
    }
    
    // Check file type
    const fileExtension = file.name.split('.').pop().toLowerCase();
    const fileType = file.type.split('/')[0];
    
    if (!allowedTypes.includes(fileExtension)) {
        showNotification(`File type not allowed. Allowed types: ${allowedTypes.join(', ')}`, 'danger');
        inputElement.value = '';
        return false;
    }
    
    return true;
}

// Date Calculations
function daysUntilDue(dueDate) {
    const today = new Date();
    const due = new Date(dueDate);
    const timeDiff = due - today;
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    return daysDiff;
}

function isOverdue(dueDate) {
    return daysUntilDue(dueDate) < 0;
}

// Session Management
function extendSession() {
    // Send a request to keep session alive
    fetch('<?php echo SITE_URL; ?>api/ping.php', { method: 'POST' })
        .catch(error => console.log('Session ping error:', error));
}

// Set up session extension on user activity
document.addEventListener('click', () => {
    extendSession();
}, { once: false });

// Auto-logout warning before session timeout
let sessionWarningTimeout;
function resetSessionWarning() {
    clearTimeout(sessionWarningTimeout);
    sessionWarningTimeout = setTimeout(() => {
        showNotification('Your session is about to expire. Please save your work.', 'warning');
    }, 55 * 60 * 1000); // 55 minutes
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    resetSessionWarning();
    
    // Hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Add form submit validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
                showNotification('Please fill in all required fields', 'danger');
            }
        });
    });
});

// Print function
function printPage() {
    window.print();
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    let csv = [];
    
    table.querySelectorAll('tr').forEach(row => {
        let rowData = [];
        row.querySelectorAll('th, td').forEach(cell => {
            rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', filename);
    link.click();
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }
});

