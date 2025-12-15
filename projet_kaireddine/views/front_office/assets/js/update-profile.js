/**
 * update-profile.js - Validation côté client pour la modification du profil
 * Client-side validation for profile update form in French
 */

document.addEventListener('DOMContentLoaded', function() {
    const updateForm = document.getElementById('updateProfileForm');
    
    if (!updateForm) return;
    
    // Éléments du formulaire
    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const ageInput = document.getElementById('age');
    const addressInput = document.getElementById('address');
    const bioInput = document.getElementById('bio');
    const interestsCheckboxes = document.querySelectorAll('#profileEdit input[name="interests[]"]');
    
    /**
     * Afficher un message d'erreur sous un champ
     */
    function showError(input, message) {
        const formGroup = input.closest('.form-group, .mb-4');
        let errorDiv = formGroup.querySelector('.error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger mt-1 small';
            formGroup.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
    }
    
    /**
     * Effacer le message d'erreur
     */
    function clearError(input) {
        const formGroup = input.closest('.form-group, .mb-4');
        const errorDiv = formGroup.querySelector('.error-message');
        
        if (errorDiv) {
            errorDiv.remove();
        }
        
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
    
    /**
     * Valider le nom complet (au moins 2 mots, pas de chiffres/symboles)
     */
    function validateFullname() {
        const value = fullnameInput.value.trim();
        
        if (value === '') {
            showError(fullnameInput, 'Le nom complet est obligatoire.');
            return false;
        }
        
        const words = value.split(/\s+/).filter(word => word.length > 0);
        if (words.length < 2) {
            showError(fullnameInput, 'Le nom complet doit contenir au moins 2 mots (prénom et nom).');
            return false;
        }
        
        const nameRegex = /^[a-zA-ZÀ-ÿ\s]+$/;
        if (!nameRegex.test(value)) {
            showError(fullnameInput, 'Le nom ne peut contenir que des lettres et des espaces.');
            return false;
        }
        
        clearError(fullnameInput);
        return true;
    }
    
    /**
     * Valider l'email
     */
    function validateEmail() {
        const value = emailInput.value.trim();
        
        if (value === '') {
            showError(emailInput, "L'email est obligatoire.");
            return false;
        }
        
        if (!value.includes('@')) {
            showError(emailInput, "L'email doit contenir le symbole @.");
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showError(emailInput, "Format d'email invalide.");
            return false;
        }
        
        clearError(emailInput);
        return true;
    }
    
    /**
     * Valider l'âge (minimum 18 ans)
     */
    function validateAge() {
        const value = ageInput.value.trim();
        
        if (value === '') {
            showError(ageInput, "L'âge est obligatoire.");
            return false;
        }
        
        const age = parseInt(value);
        
        if (isNaN(age)) {
            showError(ageInput, "L'âge doit être un nombre.");
            return false;
        }
        
        if (age < 18) {
            showError(ageInput, 'Vous devez avoir au moins 18 ans.');
            return false;
        }
        
        if (age > 100) {
            showError(ageInput, 'Veuillez entrer un âge valide.');
            return false;
        }
        
        clearError(ageInput);
        return true;
    }
    
    /**
     * Valider l'adresse (format: chiffre + rue + texte)
     */
    function validateAddress() {
        const value = addressInput.value.trim();
        
        if (value === '') {
            showError(addressInput, "L'adresse est obligatoire.");
            return false;
        }
        
        const addressRegex = /^\d+\s+(rue|avenue|boulevard|av|bd|impasse|chemin)/i;
        if (!addressRegex.test(value)) {
            showError(addressInput, "L'adresse doit commencer par un numéro suivi de 'rue', 'avenue' ou 'boulevard' (ex: 8 rue de la République).");
            return false;
        }
        
        if (value.length < 10) {
            showError(addressInput, "L'adresse doit être plus détaillée.");
            return false;
        }
        
        clearError(addressInput);
        return true;
    }
    
    /**
     * Valider la biographie (minimum 10 mots)
     */
    function validateBio() {
        const value = bioInput.value.trim();
        
        if (value === '') {
            showError(bioInput, 'La biographie est obligatoire.');
            return false;
        }
        
        const words = value.split(/\s+/).filter(word => word.length > 0);
        const wordCount = words.length;
        
        if (wordCount < 10) {
            showError(bioInput, `La biographie doit contenir au moins 10 mots. Vous avez ${wordCount} mot(s).`);
            return false;
        }
        
        clearError(bioInput);
        return true;
    }
    
    /**
     * Valider les intérêts (au moins un sélectionné)
     */
    function validateInterests() {
        const checked = Array.from(interestsCheckboxes).some(cb => cb.checked);
        const interestsContainer = document.getElementById('interestsContainer');
        
        if (!checked) {
            let errorDiv = interestsContainer.querySelector('.error-message');
            
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-danger mt-2 small';
                interestsContainer.appendChild(errorDiv);
            }
            
            errorDiv.textContent = 'Veuillez sélectionner au moins un centre d\'intérêt.';
            return false;
        }
        
        const errorDiv = interestsContainer.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
        
        return true;
    }
    
    /**
     * Afficher le compteur de mots pour la bio
     */
    function updateBioWordCount() {
        const value = bioInput.value.trim();
        const words = value.split(/\s+/).filter(word => word.length > 0);
        const wordCount = words.length;
        
        let counterDiv = bioInput.parentElement.querySelector('.word-counter');
        
        if (!counterDiv) {
            counterDiv = document.createElement('div');
            counterDiv.className = 'word-counter small text-muted mt-1';
            bioInput.parentElement.appendChild(counterDiv);
        }
        
        counterDiv.textContent = `${wordCount} mot(s) (minimum 10 requis)`;
        
        if (wordCount >= 10) {
            counterDiv.classList.remove('text-danger');
            counterDiv.classList.add('text-success');
        } else {
            counterDiv.classList.remove('text-success');
            counterDiv.classList.add('text-muted');
        }
    }
    
    // Validation en temps réel
    if (fullnameInput) fullnameInput.addEventListener('blur', validateFullname);
    if (emailInput) emailInput.addEventListener('blur', validateEmail);
    if (ageInput) ageInput.addEventListener('blur', validateAge);
    if (addressInput) addressInput.addEventListener('blur', validateAddress);
    if (bioInput) {
        bioInput.addEventListener('blur', validateBio);
        bioInput.addEventListener('input', updateBioWordCount);
    }
    
    if (interestsCheckboxes.length > 0) {
        interestsCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', validateInterests);
        });
    }
    
    // Validation lors de la soumission du formulaire
    updateForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Valider tous les champs
        const isFullnameValid = validateFullname();
        const isEmailValid = validateEmail();
        const isAgeValid = validateAge();
        const isAddressValid = validateAddress();
        const isBioValid = validateBio();
        const areInterestsValid = validateInterests();
        
        // Si tous les champs sont valides, soumettre le formulaire
        if (isFullnameValid && isEmailValid && isAgeValid && 
            isAddressValid && isBioValid && areInterestsValid) {
            
            // Afficher un indicateur de chargement
            const submitBtn = updateForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
            
            // Soumettre le formulaire
            updateForm.submit();
        } else {
            // Faire défiler jusqu'au premier champ invalide
            const firstInvalid = updateForm.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });
    
    // Initialiser le compteur de mots si bioInput existe
    if (bioInput) {
        updateBioWordCount();
    }
});
