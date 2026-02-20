/**
 * Main Application JS - public/assets/js/app.js
 * 
 * htmx integration, form handling, utilities
 */

// ===== htmx Configuration =====
document.addEventListener('htmx:configRequest', (detail) => {
    // Add CSRF token to all requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                      document.querySelector('input[name="csrf_token"]')?.value;
    
    if (csrfToken) {
        detail.detail.headers['X-CSRF-Token'] = csrfToken;
    }
});

// ===== Form Handling =====
class FormHandler {
    constructor(formElement) {
        this.form = formElement;
        this.init();
    }
    
    init() {
        this.form.addEventListener('submit', (e) => {
            // htmx handles the submission if hx-* attributes present
            if (this.form.hasAttribute('hx-post') || 
                this.form.hasAttribute('hx-put') || 
                this.form.hasAttribute('hx-delete')) {
                return;
            }
            
            // Regular form submission validation
            if (!this.validate()) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        this.form.querySelectorAll('[data-validate]').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('change', () => this.validateField(field));
        });
    }
    
    validate() {
        let isValid = true;
        this.form.querySelectorAll('[data-validate]').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    }
    
    validateField(field) {
        const rules = field.getAttribute('data-validate')?.split('|') || [];
        let isValid = true;
        
        rules.forEach(rule => {
            const [ruleName, ...params] = rule.split(':');
            
            switch (ruleName.trim()) {
                case 'required':
                    if (!field.value.trim()) {
                        this.showError(field, 'This field is required');
                        isValid = false;
                    }
                    break;
                case 'email':
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                        this.showError(field, 'Enter a valid email address');
                        isValid = false;
                    }
                    break;
                case 'min':
                    const minLength = parseInt(params[0]);
                    if (field.value.length < minLength) {
                        this.showError(field, `Minimum ${minLength} characters required`);
                        isValid = false;
                    }
                    break;
                case 'max':
                    const maxLength = parseInt(params[0]);
                    if (field.value.length > maxLength) {
                        this.showError(field, `Maximum ${maxLength} characters allowed`);
                        isValid = false;
                    }
                    break;
                case 'match':
                    const matchField = document.querySelector(params[0]);
                    if (field.value !== matchField?.value) {
                        this.showError(field, 'Values do not match');
                        isValid = false;
                    }
                    break;
            }
        });
        
        if (isValid) {
            this.clearError(field);
        }
        
        return isValid;
    }
    
    showError(field, message) {
        this.clearError(field);
        field.classList.add('is-invalid');
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = message;
        field.parentElement.appendChild(feedback);
    }
    
    clearError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) feedback.remove();
    }
}

// Initialize forms
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        new FormHandler(form);
    });
});

// Re-initialize forms added by htmx
document.addEventListener('htmx:afterSwap', () => {
    document.querySelectorAll('form:not([data-initialized])').forEach(form => {
        form.setAttribute('data-initialized', 'true');
        new FormHandler(form);
    });
});

// ===== API Helper =====
class API {
    static async get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    }
    
    static async post(url, data = {}, options = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                         document.querySelector('input[name="csrf_token"]')?.value;
        
        return this.request(url, {
            ...options,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                ...(options.headers || {})
            },
            body: JSON.stringify(data)
        });
    }
    
    static async request(url, options = {}) {
        try {
            const response = await fetch(url, options);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }
            
            return { success: true, data };
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

// ===== Storage Helper =====
class Storage {
    static set(key, value, expiry = null) {
        const item = {
            value,
            expiry: expiry ? Date.now() + expiry : null
        };
        localStorage.setItem(key, JSON.stringify(item));
    }
    
    static get(key) {
        const item = JSON.parse(localStorage.getItem(key));
        
        if (!item) return null;
        
        // Check expiry
        if (item.expiry && Date.now() > item.expiry) {
            localStorage.removeItem(key);
            return null;
        }
        
        return item.value;
    }
    
    static remove(key) {
        localStorage.removeItem(key);
    }
    
    static clear() {
        localStorage.clear();
    }
}

// ===== URL Helper =====
class URL {
    static buildQuery(params) {
        return new URLSearchParams(params).toString();
    }
    
    static addQueryParams(baseUrl, params) {
        const url = new URL(baseUrl, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            url.searchParams.set(key, value);
        });
        return url.toString().replace(window.location.origin, '');
    }
    
    static getQueryParam(name) {
        return new URLSearchParams(window.location.search).get(name);
    }
}

// ===== Format Helper =====
class Format {
    static currency(value, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency
        }).format(value);
    }
    
    static number(value, decimals = 0) {
        return Number(value).toFixed(decimals);
    }
    
    static date(dateStr, format = 'short') {
        const date = new Date(dateStr);
        
        switch(format) {
            case 'short':
                return date.toLocaleDateString();
            case 'long':
                return date.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            case 'time':
                return date.toLocaleTimeString();
            case 'datetime':
                return date.toLocaleString();
            default:
                return date.toString();
        }
    }
    
    static timeAgo(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + ' years ago';
        
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + ' months ago';
        
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + ' days ago';
        
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + ' hours ago';
        
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + ' minutes ago';
        
        return 'Just now';
    }
    
    static bytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
    }
}

// ===== Notification Helper =====
class Notification {
    static show(message, type = 'info', duration = 5000) {
        const bgClass = {
            success: 'bg-success',
            error: 'bg-danger',
            warning: 'bg-warning',
            info: 'bg-info'
        }[type] || 'bg-info';
        
        const icon = {
            success: '<i class="bi bi-check-circle"></i>',
            error: '<i class="bi bi-exclamation-circle"></i>',
            warning: '<i class="bi bi-exclamation-triangle"></i>',
            info: '<i class="bi bi-info-circle"></i>'
        }[type] || '<i class="bi bi-bell"></i>';
        
        const html = `
            <div class="toast" role="alert">
                <div class="toast-header ${bgClass} text-white">
                    <span class="me-2">${icon}</span>
                    <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        const container = document.getElementById('toastContainer') || 
                         document.querySelector('.toast-container') ||
                         this.createContainer();
        
        const toastEl = document.createElement('div');
        toastEl.innerHTML = html;
        container.appendChild(toastEl.firstElementChild);
        
        const toast = new bootstrap.Toast(toastEl.firstElementChild);
        toast.show();
        
        setTimeout(() => {
            toastEl.firstElementChild.remove();
        }, duration + 1000);
    }
    
    static createContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.id = 'toastContainer';
        document.body.appendChild(container);
        return container;
    }
    
    static success(message) { this.show(message, 'success'); }
    static error(message) { this.show(message, 'error'); }
    static warning(message) { this.show(message, 'warning'); }
    static info(message) { this.show(message, 'info'); }
}

// ===== Modal Helper =====
class Modal {
    static show(elementId) {
        const modal = document.getElementById(elementId);
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }
    
    static hide(elementId) {
        const modal = document.getElementById(elementId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
        }
    }
    
    static confirm(message, onConfirm, onCancel) {
        const html = `
            <div class="modal fade" id="confirmModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const container = document.createElement('div');
        container.innerHTML = html;
        document.body.appendChild(container);
        
        const modalEl = container.querySelector('#confirmModal');
        const bsModal = new bootstrap.Modal(modalEl);
        
        modalEl.querySelector('#confirmBtn').addEventListener('click', () => {
            bsModal.hide();
            onConfirm?.();
        });
        
        modalEl.addEventListener('hidden.bs.modal', () => {
            container.remove();
            onCancel?.();
        });
        
        bsModal.show();
    }
}

// ===== Expose to Global Scope =====
window.API = API;
window.Storage = Storage;
window.URL = URL;
window.Format = Format;
window.Notification = Notification;
window.Modal = Modal;
