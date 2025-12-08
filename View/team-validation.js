/**
 * team-validation.js - Validation stricte des formulaires
 * Validation JavaScript complète avec messages d'erreur en temps réel
 */

// ============ RÈGLES DE VALIDATION ============

const validationRules = {
    // Tournoi
    id_tournoi: {
        required: true,
        message: 'Veuillez sélectionner un tournoi'
    },
    
    // Nom d'équipe
    team_name: {
        required: true,
        minLength: 3,
        maxLength: 50,
        pattern: /^[a-zA-ZÀ-ÿ0-9\s\-']+$/,
        messages: {
            required: 'Le nom de l\'équipe est obligatoire',
            minLength: 'Le nom doit contenir au moins 3 caractères',
            maxLength: 'Le nom ne peut pas dépasser 50 caractères',
            pattern: 'Le nom contient des caractères non autorisés'
        }
    },
    
    // Tag d'équipe
    team_tag: {
        required: true,
        minLength: 2,
        maxLength: 10,
        pattern: /^[A-Z0-9]+$/,
        messages: {
            required: 'Le tag de l\'équipe est obligatoire',
            minLength: 'Le tag doit contenir au moins 2 caractères',
            maxLength: 'Le tag ne peut pas dépasser 10 caractères',
            pattern: 'Le tag ne peut contenir que des lettres majuscules et chiffres'
        }
    },
    
    // Pays
    country: {
        required: true,
        message: 'Veuillez sélectionner un pays'
    },
    
    // Catégorie
    category: {
        required: true,
        message: 'Veuillez sélectionner une catégorie'
    },
    
    // Nom du leader
    leader_name: {
        required: true,
        minLength: 3,
        maxLength: 100,
        pattern: /^[a-zA-ZÀ-ÿ\s\-']+$/,
        messages: {
            required: 'Le nom du leader est obligatoire',
            minLength: 'Le nom doit contenir au moins 3 caractères',
            maxLength: 'Le nom ne peut pas dépasser 100 caractères',
            pattern: 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes'
        }
    },
    
    // Email du leader
    leader_email: {
        required: true,
        pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        messages: {
            required: 'L\'email du leader est obligatoire',
            pattern: 'Veuillez entrer une adresse email valide (ex: nom@exemple.com)'
        }
    },
    
    // Téléphone du leader
    leader_phone: {
        required: true,
        pattern: /^\+?[0-9\s\-()]{8,20}$/,
        messages: {
            required: 'Le téléphone du leader est obligatoire',
            pattern: 'Veuillez entrer un numéro de téléphone valide (ex: +216 12 345 678)'
        }
    },
    
    // Nom du membre (pour rejoindre)
    member_name: {
        required: true,
        minLength: 3,
        maxLength: 100,
        pattern: /^[a-zA-ZÀ-ÿ\s\-']+$/,
        messages: {
            required: 'Votre nom est obligatoire',
            minLength: 'Le nom doit contenir au moins 3 caractères',
            maxLength: 'Le nom ne peut pas dépasser 100 caractères',
            pattern: 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes'
        }
    },
    
    // Email du membre
    member_email: {
        required: true,
        pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        messages: {
            required: 'Votre email est obligatoire',
            pattern: 'Veuillez entrer une adresse email valide'
        }
    },
    
    // Téléphone du membre
    member_phone: {
        required: true,
        pattern: /^\+?[0-9\s\-()]{8,20}$/,
        messages: {
            required: 'Votre téléphone est obligatoire',
            pattern: 'Veuillez entrer un numéro de téléphone valide'
        }
    }
};

// ============ FONCTION DE VALIDATION PRINCIPALE ============

function validateField(fieldName, value, isRequired = true) {
    const rules = validationRules[fieldName];
    
    if (!rules) return { valid: true };
    
    // Trim la valeur
    value = String(value || '').trim();
    
    // Vérification champ requis
    if (rules.required && !value) {
        return {
            valid: false,
            message: rules.message || rules.messages?.required || 'Ce champ est obligatoire'
        };
    }
    
    // Si le champ n'est pas requis et est vide, c'est valide
    if (!isRequired && !value) {
        return { valid: true };
    }
    
    // Vérification longueur minimale
    if (rules.minLength && value.length < rules.minLength) {
        return {
            valid: false,
            message: rules.messages?.minLength || `Minimum ${rules.minLength} caractères`
        };
    }
    
    // Vérification longueur maximale
    if (rules.maxLength && value.length > rules.maxLength) {
        return {
            valid: false,
            message: rules.messages?.maxLength || `Maximum ${rules.maxLength} caractères`
        };
    }
    
    // Vérification pattern
    if (rules.pattern && !rules.pattern.test(value)) {
        return {
            valid: false,
            message: rules.messages?.pattern || 'Format invalide'
        };
    }
    
    return { valid: true };
}

// ============ AFFICHAGE DES ERREURS ============

function showFieldError(fieldName, message) {
    const errorElement = document.getElementById(`error_${fieldName}`);
    const inputElement = document.querySelector(`[name="${fieldName}"]`);
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
        errorElement.style.color = '#ec4899';
        errorElement.style.fontSize = '13px';
        errorElement.style.marginTop = '5px';
    }
    
    if (inputElement) {
        inputElement.classList.add('error');
        inputElement.style.borderColor = '#ec4899';
        inputElement.style.backgroundColor = 'rgba(236, 72, 153, 0.1)';
    }
}

function clearFieldError(fieldName) {
    const errorElement = document.getElementById(`error_${fieldName}`);
    const inputElement = document.querySelector(`[name="${fieldName}"]`);
    
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
    
    if (inputElement) {
        inputElement.classList.remove('error');
        inputElement.style.borderColor = '';
        inputElement.style.backgroundColor = '';
    }
}

// ============ VALIDATION EN TEMPS RÉEL ============

function setupRealtimeValidation() {
    // Validation pour tous les champs
    const fieldsToValidate = [
        'id_tournoi', 'team_name', 'team_tag', 'country', 'category',
        'leader_name', 'leader_email', 'leader_phone',
        'member_name', 'member_email', 'member_phone'
    ];
    
    fieldsToValidate.forEach(fieldName => {
        const element = document.querySelector(`[name="${fieldName}"]`);
        if (!element) return;
        
        // Validation sur blur (perte de focus)
        element.addEventListener('blur', function() {
            const result = validateField(fieldName, this.value);
            if (!result.valid) {
                showFieldError(fieldName, result.message);
            } else {
                clearFieldError(fieldName);
            }
        });
        
        // Effacer l'erreur sur input
        element.addEventListener('input', function() {
            clearFieldError(fieldName);
        });
        
        // Conversion automatique en majuscules pour team_tag
        if (fieldName === 'team_tag') {
            element.addEventListener('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
        }
    });
}

// ============ VALIDATION DES MEMBRES DYNAMIQUES ============

function validateDynamicMember(memberIndex) {
    const nameInput = document.querySelector(`.member-item:nth-child(${memberIndex}) [name="member_names[]"]`);
    const emailInput = document.querySelector(`.member-item:nth-child(${memberIndex}) [name="member_emails[]"]`);
    const phoneInput = document.querySelector(`.member-item:nth-child(${memberIndex}) [name="member_phones[]"]`);
    
    let isValid = true;
    
    // Si un champ est rempli, tous doivent l'être
    const hasAnyValue = (nameInput?.value || emailInput?.value || phoneInput?.value);
    
    if (hasAnyValue) {
        // Valider le nom
        if (nameInput) {
            const result = validateField('leader_name', nameInput.value, true);
            if (!result.valid) {
                showMemberFieldError(nameInput, result.message);
                isValid = false;
            }
        }
        
        // Valider l'email
        if (emailInput) {
            const result = validateField('leader_email', emailInput.value, true);
            if (!result.valid) {
                showMemberFieldError(emailInput, result.message);
                isValid = false;
            }
        }
        
        // Valider le téléphone
        if (phoneInput) {
            const result = validateField('leader_phone', phoneInput.value, true);
            if (!result.valid) {
                showMemberFieldError(phoneInput, result.message);
                isValid = false;
            }
        }
    }
    
    return isValid;
}

function showMemberFieldError(inputElement, message) {
    if (!inputElement) return;
    
    // Chercher ou créer l'élément d'erreur
    let errorElement = inputElement.parentElement.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        inputElement.parentElement.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    errorElement.classList.add('show');
    errorElement.style.color = '#ec4899';
    
    inputElement.classList.add('error');
    inputElement.style.borderColor = '#ec4899';
}

function clearMemberFieldError(inputElement) {
    if (!inputElement) return;
    
    const errorElement = inputElement.parentElement.querySelector('.error-message');
    if (errorElement) {
        errorElement.classList.remove('show');
        errorElement.textContent = '';
    }
    
    inputElement.classList.remove('error');
    inputElement.style.borderColor = '';
}

// ============ VALIDATION COMPLÈTE DU FORMULAIRE ============

function validateCreateTeamForm() {
    let isValid = true;
    const errors = [];
    
    // Valider chaque champ
    const fields = [
        { name: 'id_tournoi', element: document.getElementById('id_tournoi') },
        { name: 'team_name', element: document.getElementById('team_name') },
        { name: 'team_tag', element: document.getElementById('team_tag') },
        { name: 'country', element: document.getElementById('country') },
        { name: 'category', element: document.getElementById('category') },
        { name: 'leader_name', element: document.getElementById('leader_name') },
        { name: 'leader_email', element: document.getElementById('leader_email') },
        { name: 'leader_phone', element: document.getElementById('leader_phone') }
    ];
    
    fields.forEach(field => {
        if (!field.element) return;
        
        const result = validateField(field.name, field.element.value);
        if (!result.valid) {
            showFieldError(field.name, result.message);
            errors.push({ field: field.name, message: result.message });
            isValid = false;
        } else {
            clearFieldError(field.name);
        }
    });
    
    // Valider les membres dynamiques
    const memberItems = document.querySelectorAll('.member-item');
    memberItems.forEach((item, index) => {
        if (!validateDynamicMember(index + 1)) {
            isValid = false;
        }
    });
    
    // Afficher un message récapitulatif si erreurs
    if (!isValid) {
        showAlert('createAlert', `⚠️ Veuillez corriger les ${errors.length} erreur(s) dans le formulaire`, 'error');
        
        // Scroller vers la première erreur
        const firstError = document.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
    
    return isValid;
}

function validateJoinTeamForm() {
    let isValid = true;
    const errors = [];
    
    const fields = [
        { name: 'member_name', element: document.getElementById('member_name') },
        { name: 'member_email', element: document.getElementById('member_email') },
        { name: 'member_phone', element: document.getElementById('member_phone') }
    ];
    
    fields.forEach(field => {
        if (!field.element) return;
        
        const result = validateField(field.name, field.element.value);
        if (!result.valid) {
            showFieldError(field.name, result.message);
            errors.push({ field: field.name, message: result.message });
            isValid = false;
        } else {
            clearFieldError(field.name);
        }
    });
    
    if (!isValid) {
        const modalContent = document.querySelector('.modal-content');
        const existingAlert = modalContent.querySelector('.alert');
        if (existingAlert) existingAlert.remove();
        
        const alert = document.createElement('div');
        alert.className = 'alert error show';
        alert.innerHTML = `⚠️ Veuillez corriger les ${errors.length} erreur(s)`;
        alert.style.backgroundColor = 'rgba(236, 72, 153, 0.2)';
        alert.style.color = '#ec4899';
        alert.style.borderLeft = '4px solid #ec4899';
        modalContent.insertBefore(alert, modalContent.firstChild);
    }
    
    return isValid;
}

// ============ VALIDATION DES EMAILS UNIQUES ============

let existingEmails = new Set();

function checkEmailUniqueness(email, currentFieldName) {
    email = email.toLowerCase().trim();
    
    // Vérifier contre le leader
    const leaderEmail = document.getElementById('leader_email')?.value.toLowerCase().trim();
    if (leaderEmail && email === leaderEmail && currentFieldName !== 'leader_email') {
        return {
            valid: false,
            message: 'Cet email est déjà utilisé par le leader'
        };
    }
    
    // Vérifier contre les autres membres
    const memberEmails = Array.from(document.querySelectorAll('[name="member_emails[]"]'))
        .map(input => input.value.toLowerCase().trim())
        .filter(e => e);
    
    const emailCount = memberEmails.filter(e => e === email).length;
    if (emailCount > 1) {
        return {
            valid: false,
            message: 'Cet email est déjà utilisé par un autre membre'
        };
    }
    
    return { valid: true };
}

// ============ VALIDATION DES TÉLÉPHONES UNIQUES ============

function checkPhoneUniqueness(phone, currentFieldName) {
    phone = phone.replace(/[\s\-()]/g, '').trim();
    
    // Vérifier contre le leader
    const leaderPhone = document.getElementById('leader_phone')?.value.replace(/[\s\-()]/g, '').trim();
    if (leaderPhone && phone === leaderPhone && currentFieldName !== 'leader_phone') {
        return {
            valid: false,
            message: 'Ce numéro est déjà utilisé par le leader'
        };
    }
    
    // Vérifier contre les autres membres
    const memberPhones = Array.from(document.querySelectorAll('[name="member_phones[]"]'))
        .map(input => input.value.replace(/[\s\-()]/g, '').trim())
        .filter(p => p);
    
    const phoneCount = memberPhones.filter(p => p === phone).length;
    if (phoneCount > 1) {
        return {
            valid: false,
            message: 'Ce numéro est déjà utilisé par un autre membre'
        };
    }
    
    return { valid: true };
}

// ============ INITIALISATION ============

document.addEventListener('DOMContentLoaded', function() {
    setupRealtimeValidation();
    
    // Ajouter la validation aux nouveaux membres dynamiques
    const originalAddMember = window.addMember;
    window.addMember = function() {
        originalAddMember();
        
        // Attendre que l'élément soit ajouté au DOM
        setTimeout(() => {
            const lastMember = document.querySelector('.member-item:last-child');
            if (lastMember) {
                const inputs = lastMember.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        const fieldType = this.name.replace('[]', '').replace('member_', 'leader_');
                        const result = validateField(fieldType, this.value, false);
                        if (!result.valid) {
                            showMemberFieldError(this, result.message);
                        } else {
                            clearMemberFieldError(this);
                        }
                    });
                    
                    input.addEventListener('input', function() {
                        clearMemberFieldError(this);
                    });
                });
            }
        }, 100);
    };
    
    // Validation email unique
    document.getElementById('leader_email')?.addEventListener('blur', function() {
        const uniqueCheck = checkEmailUniqueness(this.value, 'leader_email');
        if (!uniqueCheck.valid) {
            showFieldError('leader_email', uniqueCheck.message);
        }
    });
    
    // Validation téléphone unique
    document.getElementById('leader_phone')?.addEventListener('blur', function() {
        const uniqueCheck = checkPhoneUniqueness(this.value, 'leader_phone');
        if (!uniqueCheck.valid) {
            showFieldError('leader_phone', uniqueCheck.message);
        }
    });
});

// ============ EXPORT DES FONCTIONS ============

window.validateCreateTeamForm = validateCreateTeamForm;
window.validateJoinTeamForm = validateJoinTeamForm;
window.validateField = validateField;
window.showFieldError = showFieldError;
window.clearFieldError = clearFieldError;