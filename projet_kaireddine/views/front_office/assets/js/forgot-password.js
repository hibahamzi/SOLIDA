document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotForm');
    const emailInput = document.getElementById('email');

    form.addEventListener('submit', function(event) {
        let isValid = true;
        const emailInput = document.getElementById('email');
        const email = emailInput.value.trim();

        // Clear previous errors
        emailInput.classList.remove('is-invalid');
        const existingFeedback = emailInput.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        // Check if email is filled
        if (email.length === 0) {
            isValid = false;
            emailInput.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = "L'email est obligatoire.";
            emailInput.parentNode.appendChild(feedback);
        }

        // Validate email contains @
        if (email.length > 0 && !email.includes('@')) {
            isValid = false;
            emailInput.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = "L'email doit contenir le symbole @.";
            emailInput.parentNode.appendChild(feedback);
        }

        // Validate basic email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email.length > 0 && !emailRegex.test(email)) {
            isValid = false;
            if (!emailInput.classList.contains('is-invalid')) {
                emailInput.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = "Format d'email invalide.";
                emailInput.parentNode.appendChild(feedback);
            } else {
                // Update existing feedback
                const feedback = emailInput.parentNode.querySelector('.invalid-feedback');
                feedback.textContent = "Format d'email invalide.";
            }
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});