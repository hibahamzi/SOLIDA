document.addEventListener('DOMContentLoaded', function() {
    const verifyCodeForm = document.getElementById('verifyCodeForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');

    if (verifyCodeForm) {
        // Validation for verification code form
        verifyCodeForm.addEventListener('submit', function(event) {
            const codeInput = document.getElementById('verification_code');
            let isValid = true;

            // Clear previous errors
            codeInput.classList.remove('is-invalid');
            const existingFeedback = codeInput.parentNode.querySelector('.invalid-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }

            const code = codeInput.value.trim();

            if (code.length === 0) {
                isValid = false;
                codeInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "Le code de vérification est obligatoire.";
                codeInput.parentNode.appendChild(feedback);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }

    if (resetPasswordForm) {
        // Validation for reset password form
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        resetPasswordForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Clear previous errors
            [newPasswordInput, confirmPasswordInput].forEach(input => {
                input.classList.remove('is-invalid');
                const existingFeedback = input.parentNode.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }
            });

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Check if fields are filled
            if (newPassword.length === 0) {
                isValid = false;
                newPasswordInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "Le nouveau mot de passe est obligatoire.";
                newPasswordInput.parentNode.appendChild(feedback);
            }

            if (confirmPassword.length === 0) {
                isValid = false;
                confirmPasswordInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "La confirmation du mot de passe est obligatoire.";
                confirmPasswordInput.parentNode.appendChild(feedback);
            }

            // Validate new password
            if (newPassword.length > 0 && newPassword.length < 8) {
                isValid = false;
                newPasswordInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
                newPasswordInput.parentNode.appendChild(feedback);
            } else if (newPassword.length >= 8 && !/\d/.test(newPassword) && !/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword)) {
                isValid = false;
                newPasswordInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "Le mot de passe doit contenir au moins un chiffre ou un symbole.";
                newPasswordInput.parentNode.appendChild(feedback);
            }

            // Validate confirm password
            if (confirmPassword.length > 0 && confirmPassword !== newPassword) {
                isValid = false;
                confirmPasswordInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "Les mots de passe ne correspondent pas.";
                confirmPasswordInput.parentNode.appendChild(feedback);
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    }
});