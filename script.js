// Enhanced Reading Tracker JavaScript

// Show message notification
function showMessage(message, type = 'success') {
    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);

    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 300);
    }, 3000);
}

// Form validation
function validateBookForm(title, author, totalPages, pagesRead) {
    const errors = [];

    if (!title || title.trim().length === 0) {
        errors.push('Book title is required');
    }

    if (!author || author.trim().length === 0) {
        errors.push('Author name is required');
    }

    if (isNaN(totalPages) || totalPages < 1) {
        errors.push('Total pages must be at least 1');
    }

    if (isNaN(pagesRead) || pagesRead < 0) {
        errors.push('Pages read cannot be negative');
    }

    if (pagesRead > totalPages) {
        errors.push('Pages read cannot exceed total pages');
    }

    return errors;
}

// Calculate percentage
function calculatePercentage(pagesRead, totalPages) {
    if (totalPages === 0) return 0;
    return Math.min(Math.round((pagesRead / totalPages) * 100), 100);
}

// Check if book is completed
function isBookCompleted(pagesRead, totalPages) {
    return pagesRead >= totalPages;
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Validate email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// File validation
function validateImageFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB

    if (!allowedTypes.includes(file.type)) {
        return { valid: false, error: 'Only JPG, PNG, and GIF files are allowed' };
    }

    if (file.size > maxSize) {
        return { valid: false, error: 'File size must be less than 2MB' };
    }

    return { valid: true };
}

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transitions to all links
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.hostname !== window.location.hostname || this.getAttribute('href').startsWith('#')) {
                return;
            }
        });
    });

    // Add focus styles for accessibility
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (this.parentElement) {
                this.parentElement.classList.add('focused');
            }
        });
        input.addEventListener('blur', function() {
            if (this.parentElement) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});