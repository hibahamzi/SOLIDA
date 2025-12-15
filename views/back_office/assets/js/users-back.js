/**
 * Validation côté client pour la gestion des utilisateurs (Back Office) - SOLIDA
 */

document.addEventListener('DOMContentLoaded', function() {
    const addUserForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');
    
    // Liste des centres d'intérêt disponibles
    const availableInterests = [
        'Sports', 'Musique', 'Technologie', 'Arts', 'Voyages', 'Lecture',
        'Cinéma', 'Cuisine', 'Photographie', 'Danse', 'Sciences', 'Mode',
        'Jeux Vidéo', 'Nature', 'Bénévolat', 'Entrepreneuriat'
    ];
    
    // ========== FONCTIONS DE VALIDATION ==========
    
    // Validation du nom complet
    function validateFullname(input) {
        const fullname = input.value.trim();
        const words = fullname.split(/\s+/).filter(word => word.length > 0);
        
        if (fullname === '') {
            showError(input, "Le nom complet est obligatoire.");
            return false;
        }
        
        if (words.length < 2) {
            showError(input, "Le nom complet doit contenir au moins 2 mots (prénom et nom).");
            return false;
        }
        
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(fullname)) {
            showError(input, "Le nom complet ne peut contenir que des lettres.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation de l'email
    function validateEmail(input) {
        const email = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email === '') {
            showError(input, "L'email est obligatoire.");
            return false;
        }
        
        if (!emailRegex.test(email)) {
            showError(input, "Format d'email invalide.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation du mot de passe
    function validatePassword(input, isRequired = true) {
        const password = input.value;
        
        // Si le mot de passe n'est pas requis et est vide, c'est valide
        if (!isRequired && password === '') {
            showSuccess(input);
            return true;
        }
        
        if (password === '') {
            if (isRequired) {
                showError(input, "Le mot de passe est obligatoire.");
                return false;
            }
            return true;
        }
        
        if (password.length < 8) {
            showError(input, "Le mot de passe doit contenir au moins 8 caractères.");
            return false;
        }
        
        if (!/[0-9!@#$%^&*(),.?":{}|<>]/.test(password)) {
            showError(input, "Le mot de passe doit contenir au moins un chiffre ou un symbole.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation de l'âge
    function validateAge(input) {
        const age = parseInt(input.value);
        
        if (isNaN(age) || age === 0) {
            showError(input, "L'âge est obligatoire.");
            return false;
        }
        
        if (age < 18) {
            showError(input, "Vous devez avoir au moins 18 ans.");
            return false;
        }
        
        if (age > 120) {
            showError(input, "Veuillez entrer un âge valide.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation de l'adresse
    function validateAddress(input) {
        const address = input.value.trim();
        const addressPattern = /^\d+\s+(rue|avenue|boulevard|impasse|allée|place)\s+.+$/i;
        
        if (address === '') {
            showError(input, "L'adresse est obligatoire.");
            return false;
        }
        
        if (!addressPattern.test(address)) {
            showError(input, "L'adresse doit suivre le format: numéro + rue/avenue/boulevard + nom.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation de la biographie
    function validateBio(textarea) {
        const bio = textarea.value.trim();
        const wordCount = bio.split(/\s+/).filter(word => word.length > 0).length;
        
        if (bio === '') {
            showError(textarea, "La biographie est obligatoire.");
            return false;
        }
        
        if (wordCount < 10) {
            showError(textarea, `La biographie doit contenir au moins 10 mots. (${wordCount}/10)`);
            return false;
        }
        
        showSuccess(textarea);
        return true;
    }
    
    // Compteur de mots pour la biographie
    function updateBioWordCount(textarea) {
        const bio = textarea.value.trim();
        const wordCount = bio.split(/\s+/).filter(word => word.length > 0).length;
        const counterId = textarea.id + 'WordCount';
        const counter = document.getElementById(counterId);
        
        if (counter) {
            counter.textContent = `${wordCount} mots`;
            counter.style.color = wordCount >= 10 ? '#59ab6e' : '#e74c3c';
        }
    }
    
    // Validation des centres d'intérêt
    function validateInterests(form) {
        const checkboxes = form.querySelectorAll('input[name="interests[]"]');
        const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
        const container = form.querySelector('.interests-grid');
        
        if (checkedBoxes.length === 0) {
            showError(container.closest('.form-group'), "Veuillez sélectionner au moins un centre d'intérêt.");
            return false;
        }
        
        showSuccess(container.closest('.form-group'));
        return true;
    }
    
    // Afficher une erreur
    function showError(element, message) {
        let formGroup;
        if (element.classList && element.classList.contains('form-group')) {
            formGroup = element;
        } else {
            formGroup = element.closest('.form-group');
        }
        
        if (element.classList && !element.classList.contains('form-group')) {
            element.classList.remove('is-valid');
            element.classList.add('is-invalid');
        }
        
        let feedback = formGroup.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.style.display = 'block';
            formGroup.appendChild(feedback);
        }
        feedback.textContent = message;
    }
    
    // Afficher le succès
    function showSuccess(element) {
        let formGroup;
        if (element.classList && element.classList.contains('form-group')) {
            formGroup = element;
        } else {
            formGroup = element.closest('.form-group');
        }
        
        if (element.classList && !element.classList.contains('form-group')) {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
        }
        
        const feedback = formGroup.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
    
    // ========== FORMULAIRE D'AJOUT ==========
    if (addUserForm) {
        const fullnameInput = addUserForm.querySelector('#add_fullname');
        const emailInput = addUserForm.querySelector('#add_email');
        const passwordInput = addUserForm.querySelector('#add_password');
        const ageInput = addUserForm.querySelector('#add_age');
        const addressInput = addUserForm.querySelector('#add_address');
        const bioTextarea = addUserForm.querySelector('#add_bio');
        
        // Validation en temps réel
        if (fullnameInput) fullnameInput.addEventListener('blur', () => validateFullname(fullnameInput));
        if (emailInput) emailInput.addEventListener('blur', () => validateEmail(emailInput));
        if (passwordInput) passwordInput.addEventListener('blur', () => validatePassword(passwordInput, true));
        if (ageInput) ageInput.addEventListener('blur', () => validateAge(ageInput));
        if (addressInput) addressInput.addEventListener('blur', () => validateAddress(addressInput));
        if (bioTextarea) {
            bioTextarea.addEventListener('blur', () => validateBio(bioTextarea));
            bioTextarea.addEventListener('input', () => updateBioWordCount(bioTextarea));
        }
        
        // Initialiser le compteur de mots
        if (bioTextarea) updateBioWordCount(bioTextarea);
        
        // Validation à la soumission
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isFullnameValid = validateFullname(fullnameInput);
            const isEmailValid = validateEmail(emailInput);
            const isPasswordValid = validatePassword(passwordInput, true);
            const isAgeValid = validateAge(ageInput);
            const isAddressValid = validateAddress(addressInput);
            const isBioValid = validateBio(bioTextarea);
            const isInterestsValid = validateInterests(addUserForm);
            
            if (isFullnameValid && isEmailValid && isPasswordValid && isAgeValid && isAddressValid && isBioValid && isInterestsValid) {
                const submitBtn = addUserForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
                submitBtn.disabled = true;
                addUserForm.submit();
            } else {
                const firstError = addUserForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    // ========== FORMULAIRE DE MODIFICATION ==========
    if (editUserForm) {
        const fullnameInput = editUserForm.querySelector('#edit_fullname');
        const emailInput = editUserForm.querySelector('#edit_email');
        const passwordInput = editUserForm.querySelector('#edit_password');
        const ageInput = editUserForm.querySelector('#edit_age');
        const addressInput = editUserForm.querySelector('#edit_address');
        const bioTextarea = editUserForm.querySelector('#edit_bio');
        
        // Validation en temps réel
        if (fullnameInput) fullnameInput.addEventListener('blur', () => validateFullname(fullnameInput));
        if (emailInput) emailInput.addEventListener('blur', () => validateEmail(emailInput));
        if (passwordInput) passwordInput.addEventListener('blur', () => validatePassword(passwordInput, false)); // Pas obligatoire pour la modification
        if (ageInput) ageInput.addEventListener('blur', () => validateAge(ageInput));
        if (addressInput) addressInput.addEventListener('blur', () => validateAddress(addressInput));
        if (bioTextarea) {
            bioTextarea.addEventListener('blur', () => validateBio(bioTextarea));
            bioTextarea.addEventListener('input', () => updateBioWordCount(bioTextarea));
        }
        
        // Initialiser le compteur de mots
        if (bioTextarea) updateBioWordCount(bioTextarea);
        
        // Validation à la soumission
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isFullnameValid = validateFullname(fullnameInput);
            const isEmailValid = validateEmail(emailInput);
            const isPasswordValid = validatePassword(passwordInput, false);
            const isAgeValid = validateAge(ageInput);
            const isAddressValid = validateAddress(addressInput);
            const isBioValid = validateBio(bioTextarea);
            const isInterestsValid = validateInterests(editUserForm);
            
            if (isFullnameValid && isEmailValid && isPasswordValid && isAgeValid && isAddressValid && isBioValid && isInterestsValid) {
                const submitBtn = editUserForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
                submitBtn.disabled = true;
                editUserForm.submit();
            } else {
                const firstError = editUserForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
});
