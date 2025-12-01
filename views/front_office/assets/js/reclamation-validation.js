/**
 * Validation côté client pour les réclamations - SOLIDA
 * Validation sans HTML5 (required, pattern, etc.)
 */

document.addEventListener('DOMContentLoaded', function() {
    const reclamationForm = document.getElementById('reclamationForm');
    const updateReclamationForm = document.getElementById('updateReclamationForm');
    
    // Appliquer la validation au formulaire d'ajout
    if (reclamationForm) {
        setupValidation(reclamationForm);
    }
    
    // Appliquer la validation au formulaire de modification
    if (updateReclamationForm) {
        setupValidation(updateReclamationForm);
    }
    
    function setupValidation(form) {
        // Empêcher la soumission par défaut
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Validation en temps réel
        const nomInput = form.querySelector('#nom');
        const prenomInput = form.querySelector('#prenom');
        const telephoneInput = form.querySelector('#telephone');
        const emailInput = form.querySelector('#email');
        const gouvernoratSelect = form.querySelector('#gouvernorat');
        const delegationSelect = form.querySelector('#delegation');
        const positionGpsInput = form.querySelector('#position_gps');
        const descriptionTextarea = form.querySelector('#description_detaillee');
        
        if (nomInput) {
            nomInput.addEventListener('blur', () => validateNom(nomInput));
            nomInput.addEventListener('input', () => clearError(nomInput));
        }
        
        if (prenomInput) {
            prenomInput.addEventListener('blur', () => validatePrenom(prenomInput));
            prenomInput.addEventListener('input', () => clearError(prenomInput));
        }
        
        if (telephoneInput) {
            telephoneInput.addEventListener('blur', () => validateTelephone(telephoneInput));
            telephoneInput.addEventListener('input', () => clearError(telephoneInput));
        }
        
        if (emailInput) {
            emailInput.addEventListener('blur', () => validateEmail(emailInput));
            emailInput.addEventListener('input', () => clearError(emailInput));
        }
        
        if (gouvernoratSelect) {
            gouvernoratSelect.addEventListener('change', () => {
                clearError(gouvernoratSelect);
                updateDelegations(gouvernoratSelect.value);
            });
        }
        
        if (delegationSelect) {
            delegationSelect.addEventListener('change', () => clearError(delegationSelect));
        }
        
        if (positionGpsInput) {
            positionGpsInput.addEventListener('blur', () => validatePositionGps(positionGpsInput));
            positionGpsInput.addEventListener('input', () => clearError(positionGpsInput));
        }
        
        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('blur', () => validateDescription(descriptionTextarea));
            descriptionTextarea.addEventListener('input', () => {
                clearError(descriptionTextarea);
                updateDescriptionCounter(descriptionTextarea);
            });
        }
    }
    
    // ========== FONCTIONS DE VALIDATION ==========
    
    function validateNom(input) {
        const nom = input.value.trim();
        
        if (nom === '') {
            showError(input, "Le nom est obligatoire.");
            return false;
        }
        
        if (nom.length < 2) {
            showError(input, "Le nom doit contenir au moins 2 caractères.");
            return false;
        }
        
        if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(nom)) {
            showError(input, "Le nom ne peut contenir que des lettres, espaces et tirets.");
            return false;
        }
        
        if (nom.length > 50) {
            showError(input, "Le nom ne peut pas dépasser 50 caractères.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    function validatePrenom(input) {
        const prenom = input.value.trim();
        
        if (prenom === '') {
            showError(input, "Le prénom est obligatoire.");
            return false;
        }
        
        if (prenom.length < 2) {
            showError(input, "Le prénom doit contenir au moins 2 caractères.");
            return false;
        }
        
        if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(prenom)) {
            showError(input, "Le prénom ne peut contenir que des lettres, espaces et tirets.");
            return false;
        }
        
        if (prenom.length > 50) {
            showError(input, "Le prénom ne peut pas dépasser 50 caractères.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    function validateTelephone(input) {
        const telephone = input.value.trim();
        
        if (telephone === '') {
            showError(input, "Le téléphone est obligatoire.");
            return false;
        }
        
        if (!/^[0-9]+$/.test(telephone)) {
            showError(input, "Le téléphone ne doit contenir que des chiffres.");
            return false;
        }
        
        if (telephone.length < 8) {
            showError(input, "Le téléphone doit contenir au moins 8 chiffres.");
            return false;
        }
        
        if (telephone.length > 15) {
            showError(input, "Le téléphone ne peut pas dépasser 15 chiffres.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    function validateEmail(input) {
        const email = input.value.trim();
        
        if (email === '') {
            showError(input, "L'email est obligatoire.");
            return false;
        }
        
        // Validation email simple (sans HTML5)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError(input, "L'email doit être valide (exemple: nom@domaine.com).");
            return false;
        }
        
        if (email.length > 100) {
            showError(input, "L'email ne peut pas dépasser 100 caractères.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    function validateGouvernorat(select) {
        const value = select.value.trim();
        
        if (value === '' || value === null) {
            showError(select, "Le gouvernorat est obligatoire.");
            return false;
        }
        
        showSuccess(select);
        return true;
    }
    
    function validateDelegation(select) {
        const value = select.value.trim();
        
        if (value === '' || value === null) {
            showError(select, "La délégation est obligatoire.");
            return false;
        }
        
        showSuccess(select);
        return true;
    }
    
    function validatePositionGps(input) {
        const position = input.value.trim();
        
        if (position === '') {
            showError(input, "La position GPS est obligatoire.");
            return false;
        }
        
        if (position.length < 8) {
            showError(input, "La position GPS doit contenir au moins 8 caractères.");
            return false;
        }
        
        if (!/[a-zA-ZÀ-ÿ]/.test(position)) {
            showError(input, "La position GPS doit contenir des lettres.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    function validateDescription(textarea) {
        const description = textarea.value.trim();
        
        if (description === '') {
            showError(textarea, "La description est obligatoire.");
            return false;
        }
        
        if (description.length < 10) {
            showError(textarea, "La description doit contenir au moins 10 caractères.");
            return false;
        }
        
        if (description.length > 1000) {
            showError(textarea, "La description ne peut pas dépasser 1000 caractères.");
            return false;
        }
        
        showSuccess(textarea);
        return true;
    }
    
    function validateForm(form) {
        let isValid = true;
        
        const nomInput = form.querySelector('#nom');
        const prenomInput = form.querySelector('#prenom');
        const telephoneInput = form.querySelector('#telephone');
        const emailInput = form.querySelector('#email');
        const gouvernoratSelect = form.querySelector('#gouvernorat');
        const delegationSelect = form.querySelector('#delegation');
        const positionGpsInput = form.querySelector('#position_gps');
        const descriptionTextarea = form.querySelector('#description_detaillee');
        
        if (nomInput && !validateNom(nomInput)) isValid = false;
        if (prenomInput && !validatePrenom(prenomInput)) isValid = false;
        if (telephoneInput && !validateTelephone(telephoneInput)) isValid = false;
        if (emailInput && !validateEmail(emailInput)) isValid = false;
        if (gouvernoratSelect && !validateGouvernorat(gouvernoratSelect)) isValid = false;
        if (delegationSelect && !validateDelegation(delegationSelect)) isValid = false;
        if (positionGpsInput && !validatePositionGps(positionGpsInput)) isValid = false;
        if (descriptionTextarea && !validateDescription(descriptionTextarea)) isValid = false;
        
        if (!isValid) {
            // Faire défiler vers le premier champ en erreur
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
        
        return isValid;
    }
    
    // ========== FONCTIONS UTILITAIRES ==========
    
    function showError(input, message) {
        const formGroup = input.closest('.form-group') || input.parentElement;
        let errorDiv = formGroup.querySelector('.error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.cssText = 'color: #F44336; font-size: 12px; margin-top: 5px; display: block;';
            formGroup.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        input.style.borderColor = '#F44336';
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    }
    
    function showSuccess(input) {
        const formGroup = input.closest('.form-group') || input.parentElement;
        const errorDiv = formGroup.querySelector('.error-message');
        
        if (errorDiv) {
            errorDiv.remove();
        }
        
        input.style.borderColor = '#4CAF50';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
    
    function clearError(input) {
        const formGroup = input.closest('.form-group') || input.parentElement;
        const errorDiv = formGroup.querySelector('.error-message');
        
        if (errorDiv) {
            errorDiv.remove();
        }
        
        input.style.borderColor = '';
        input.classList.remove('is-invalid');
    }
    
    function updateDescriptionCounter(textarea) {
        const length = textarea.value.length;
        let counter = document.getElementById('description-counter');
        
        if (!counter) {
            counter = document.createElement('div');
            counter.id = 'description-counter';
            counter.style.cssText = 'text-align: right; color: #666; font-size: 12px; margin-top: 5px;';
            textarea.parentElement.appendChild(counter);
        }
        
        counter.textContent = length + '/1000 caractères';
        
        if (length > 1000) {
            counter.style.color = '#F44336';
        } else if (length > 900) {
            counter.style.color = '#FF9800';
        } else {
            counter.style.color = '#666';
        }
    }
    
    function updateDelegations(gouvernorat) {
        // Cette fonction devrait être définie dans le fichier principal
        // On la laisse vide ici car elle dépend des données PHP
    }
});


