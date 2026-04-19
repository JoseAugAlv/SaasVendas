/**
 * Funções utilitárias JavaScript
 * Sistema de Gestão para Vendedores Autônomos
 */

// Utilitários gerais
const Utils = {
    /**
     * Formata valor para moeda brasileira
     * @param {number} value
     * @returns {string}
     */
    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },

    /**
     * Formata data para padrão brasileiro
     * @param {string|Date} date
     * @returns {string}
     */
    formatDate(date) {
        const d = new Date(date);
        return d.toLocaleDateString('pt-BR');
    },

    /**
     * Formata data e hora
     * @param {string|Date} date
     * @returns {string}
     */
    formatDateTime(date) {
        const d = new Date(date);
        return d.toLocaleString('pt-BR');
    },

    /**
     * Valida CPF
     * @param {string} cpf
     * @returns {boolean}
     */
    validateCPF(cpf) {
        cpf = cpf.replace(/[^\d]/g, '');
        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
        
        let sum = 0, remainder;
        for (let i = 1; i <= 9; i++) sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.substring(9, 10))) return false;
        
        sum = 0;
        for (let i = 1; i <= 10; i++) sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.substring(10, 11))) return false;
        
        return true;
    },

    /**
     * Formata CPF com máscara
     * @param {string} value
     * @returns {string}
     */
    formatCPF(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})/, '$1-$2')
            .replace(/(-\d{2})\d+?$/, '$1');
    },

    /**
     * Debounce para funções
     * @param {Function} func
     * @param {number} wait
     * @returns {Function}
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Gera UUID
     * @returns {string}
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
};

// Manipulação de formulários
const FormHelper = {
    /**
     * Serializa formulário para objeto
     * @param {HTMLFormElement} form
     * @returns {Object}
     */
    serialize(form) {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        return data;
    },

    /**
     * Valida formulário HTML5
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    validate(form) {
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        return true;
    },

    /**
     * Mostra erro em campo
     * @param {HTMLElement} input
     * @param {string} message
     */
    showError(input, message) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;
        
        // Remove erros existentes
        this.clearError(input);
        
        // Adiciona classe de erro
        input.classList.add('is-invalid');
        
        // Cria mensagem de erro
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.textContent = message;
        
        formGroup.appendChild(errorDiv);
    },

    /**
     * Limpa erro de campo
     * @param {HTMLElement} input
     */
    clearError(input) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;
        
        input.classList.remove('is-invalid');
        const existingError = formGroup.querySelector('.form-error');
        if (existingError) {
            existingError.remove();
        }
    },

    /**
     * Limpa todos os erros do formulário
     * @param {HTMLFormElement} form
     */
    clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(input => {
            this.clearError(input);
        });
    },

    /**
     * Habilita/desabilita formulário
     * @param {HTMLFormElement} form
     * @param {boolean} disabled
     */
    setDisabled(form, disabled) {
        form.querySelectorAll('input, select, textarea, button').forEach(el => {
            el.disabled = disabled;
        });
    }
};

// Requisições AJAX
const API = {
    baseURL: '',

    /**
     * Faz requisição fetch com tratamento de erros
     * @param {string} url
     * @param {Object} options
     * @returns {Promise<any>}
     */
    async request(url, options = {}) {
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            ...options
        };

        try {
            const response = await fetch(this.baseURL + url, config);
            
            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Erro na requisição' }));
                throw new Error(error.message || `Erro ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * GET request
     * @param {string} url
     * @param {Object} params
     * @returns {Promise<any>}
     */
    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(queryString ? `${url}?${queryString}` : url);
    },

    /**
     * POST request
     * @param {string} url
     * @param {Object} data
     * @returns {Promise<any>}
     */
    async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    /**
     * PUT request
     * @param {string} url
     * @param {Object} data
     * @returns {Promise<any>}
     */
    async put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    /**
     * DELETE request
     * @param {string} url
     * @returns {Promise<any>}
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }
};

// Notificações toast
const Toast = {
    container: null,

    init() {
        if (this.container) return;
        
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(this.container);
    },

    /**
     * Mostra notificação
     * @param {string} message
     * @param {string} type success|error|warning|info
     * @param {number} duration
     */
    show(message, type = 'info', duration = 4000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `alert alert-${type}`;
        toast.style.cssText = `
            min-width: 300px;
            max-width: 400px;
            animation: slideIn 0.3s ease;
            cursor: pointer;
        `;
        toast.textContent = message;

        toast.addEventListener('click', () => this.remove(toast));

        this.container.appendChild(toast);

        setTimeout(() => this.remove(toast), duration);
    },

    remove(toast) {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'danger');
    },

    warning(message) {
        this.show(message, 'warning');
    },

    info(message) {
        this.show(message, 'info');
    }
};

// Confirmação de ações
const Confirm = {
    /**
     * Mostra confirmação antes de ação
     * @param {string} message
     * @returns {Promise<boolean>}
     */
    async ask(message = 'Tem certeza que deseja continuar?') {
        return new Promise((resolve) => {
            const confirmed = confirm(message);
            resolve(confirmed);
        });
    }
};

// Modal simples
const Modal = {
    /**
     * Abre modal com conteúdo
     * @param {string} content HTML ou seletor
     * @param {Object} options
     */
    open(content, options = {}) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9998;
        `;

        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content card';
        modalContent.style.cssText = `
            max-width: ${options.maxWidth || '500px'};
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        `;

        if (content.startsWith('#')) {
            const template = document.querySelector(content);
            modalContent.innerHTML = template ? template.innerHTML : content;
        } else {
            modalContent.innerHTML = content;
        }

        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        // Fecha ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.close(modal);
            }
        });

        // Fecha com ESC
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                this.close(modal);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);

        return modal;
    },

    close(modal) {
        if (modal && modal.parentNode) {
            modal.remove();
        }
    }
};

// Exporta para escopo global
window.Utils = Utils;
window.FormHelper = FormHelper;
window.API = API;
window.Toast = Toast;
window.Confirm = Confirm;
window.Modal = Modal;

// Adiciona animações CSS dinâmicas
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
