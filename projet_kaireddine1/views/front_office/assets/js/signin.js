/**
 * Validation côté client pour le formulaire de connexion - SOLIDA
 */

document.addEventListener('DOMContentLoaded', function() {
    const signinForm = document.getElementById('signinForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
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
    
    // Validation du mot de passe
    function validatePassword() {
        const password = passwordInput.value;
        
        if (password === '') {
            showError(passwordInput, "Le mot de passe est obligatoire.");
            return false;
        }
        
        if (password.length < 8) {
            showError(passwordInput, "Le mot de passe doit contenir au moins 8 caractères.");
            return false;
        }
        
        showSuccess(passwordInput);
        return true;
    }
    
    // Afficher une erreur
    function showError(input, message) {
        const formGroup = input.closest('.mb-3');
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        // Supprimer l'ancien message d'erreur s'il existe
        let feedback = formGroup.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            formGroup.appendChild(feedback);
        }
        feedback.textContent = message;
    }
    
    // Afficher le succès
    function showSuccess(input) {
        const formGroup = input.closest('.mb-3');
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        
        // Supprimer le message d'erreur s'il existe
        const feedback = formGroup.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
    
    // Validation en temps réel (blur)
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('blur', validatePassword);
    
    // Supprimer les erreurs lors de la saisie
    emailInput.addEventListener('input', function() {
        if (emailInput.classList.contains('is-invalid')) {
            emailInput.classList.remove('is-invalid');
            const feedback = emailInput.closest('.mb-3').querySelector('.invalid-feedback');
            if (feedback) feedback.remove();
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (passwordInput.classList.contains('is-invalid')) {
            passwordInput.classList.remove('is-invalid');
            const feedback = passwordInput.closest('.mb-3').querySelector('.invalid-feedback');
            if (feedback) feedback.remove();
        }
    });
    
    // Validation lors de la soumission du formulaire
    signinForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        
        if (isEmailValid && isPasswordValid) {
            // Afficher un indicateur de chargement
            const submitBtn = signinForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion en cours...';
            submitBtn.disabled = true;
            
            // Soumettre le formulaire
            signinForm.submit();
        } else {
            // Faire défiler vers le premier champ en erreur
            const firstError = signinForm.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        }
    });
});
