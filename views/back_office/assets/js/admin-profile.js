/**
 * Validation côté client pour le profil administrateur - SOLIDA
 */

document.addEventListener('DOMContentLoaded', function() {
    const adminProfileForm = document.getElementById('adminProfileForm');
    
    if (!adminProfileForm) return;
    
    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const ageInput = document.getElementById('age');
    const addressInput = document.getElementById('address');
    const bioTextarea = document.getElementById('bio');
    const interestsCheckboxes = document.querySelectorAll('input[name="interests[]"]');
    
    // Validation du nom complet
    function validateFullname() {
        const fullname = fullnameInput.value.trim();
        const words = fullname.split(/\s+/).filter(word => word.length > 0);
        
        if (fullname === '') {
            showError(fullnameInput, "Le nom complet est obligatoire.");
            return false;
        }
        
        if (words.length < 2) {
            showError(fullnameInput, "Le nom complet doit contenir au moins 2 mots (prénom et nom).");
            return false;
        }
        
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(fullname)) {
            showError(fullnameInput, "Le nom complet ne peut contenir que des lettres.");
            return false;
        }
        
        showSuccess(fullnameInput);
        return true;
    }
    
    // Validation de l'email
    function validateEmail() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email === '') {
            showError(emailInput, "L'email est obligatoire.");
            return false;
        }
        
        if (!emailRegex.test(email)) {
            showError(emailInput, "Format d'email invalide.");
            return false;
        }
        
        showSuccess(emailInput);
        return true;
    }
    
    // Validation de l'âge
    function validateAge() {
        const age = parseInt(ageInput.value);
        
        if (isNaN(age) || age === 0) {
            showError(ageInput, "L'âge est obligatoire.");
            return false;
        }
        
        if (age < 18) {
            showError(ageInput, "Vous devez avoir au moins 18 ans.");
            return false;
        }
        
        if (age > 120) {
            showError(ageInput, "Veuillez entrer un âge valide.");
            return false;
        }
        
        showSuccess(ageInput);
        return true;
    }
    
    // Validation de l'adresse
    function validateAddress() {
        const address = addressInput.value.trim();
        const addressPattern = /^\d+\s+(rue|avenue|boulevard|impasse|allée|place)\s+.+$/i;
        
        if (address === '') {
            showError(addressInput, "L'adresse est obligatoire.");
            return false;
        }
        
        if (!addressPattern.test(address)) {
            showError(addressInput, "L'adresse doit suivre le format: numéro + rue/avenue/boulevard + nom.");
            return false;
        }
        
        showSuccess(addressInput);
        return true;
    }
    
    // Validation de la biographie
    function validateBio() {
        const bio = bioTextarea.value.trim();
        const wordCount = bio.split(/\s+/).filter(word => word.length > 0).length;
        
        if (bio === '') {
            showError(bioTextarea, "La biographie est obligatoire.");
            return false;
        }
        
        if (wordCount < 10) {
            showError(bioTextarea, `La biographie doit contenir au moins 10 mots. (${wordCount}/10)`);
            return false;
        }
        
        showSuccess(bioTextarea);
        return true;
    }
    
    // Compteur de mots pour la biographie
    function updateBioWordCount() {
        const bio = bioTextarea.value.trim();
        const wordCount = bio.split(/\s+/).filter(word => word.length > 0).length;
        const counter = document.getElementById('bioWordCount');
        
        if (counter) {
            counter.textContent = `${wordCount} mots`;
            
            if (wordCount >= 10) {
                counter.style.color = '#59ab6e';
            } else {
                counter.style.color = '#e74c3c';
            }
        }
    }
    
    // Validation des centres d'intérêt
    function validateInterests() {
        const checkedBoxes = Array.from(interestsCheckboxes).filter(cb => cb.checked);
        const container = document.querySelector('.interests-grid').closest('.form-group');
        
        if (checkedBoxes.length === 0) {
            showError(container, "Veuillez sélectionner au moins un centre d'intérêt.");
            return false;
        }
        
        showSuccess(container);
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
    
    // Validation en temps réel
    if (fullnameInput) fullnameInput.addEventListener('blur', validateFullname);
    if (emailInput) emailInput.addEventListener('blur', validateEmail);
    if (ageInput) ageInput.addEventListener('blur', validateAge);
    if (addressInput) addressInput.addEventListener('blur', validateAddress);
    if (bioTextarea) {
        bioTextarea.addEventListener('blur', validateBio);
        bioTextarea.addEventListener('input', updateBioWordCount);
    }
    
    interestsCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateInterests);
    });
    
    // Initialiser le compteur de mots
    updateBioWordCount();
    
    // Validation lors de la soumission du formulaire
    adminProfileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const isFullnameValid = validateFullname();
        const isEmailValid = validateEmail();
        const isAgeValid = validateAge();
        const isAddressValid = validateAddress();
        const isBioValid = validateBio();
        const isInterestsValid = validateInterests();
        
        if (isFullnameValid && isEmailValid && isAgeValid && isAddressValid && isBioValid && isInterestsValid) {
            // Afficher un indicateur de chargement
            const submitBtn = adminProfileForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
            submitBtn.disabled = true;
            
            // Soumettre le formulaire
            adminProfileForm.submit();
        } else {
            // Faire défiler vers le premier champ en erreur
            const firstError = adminProfileForm.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });
});
