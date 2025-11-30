/**
 * Validation côté client pour la gestion des événements (Back Office) - SOLIDA
 */

document.addEventListener('DOMContentLoaded', function() {
    const addEventForm = document.getElementById('addEventForm');
    const editEventForm = document.getElementById('editEventForm');
    
    // ========== FONCTIONS DE VALIDATION ==========
    
    // Validation du titre (text only, no numbers, no symbols)
    function validateTitre(input) {
        const titre = input.value.trim();
        
        if (titre === '') {
            showError(input, "Le titre de l'événement est obligatoire.");
            return false;
        }
        
        // Only letters and spaces allowed (French characters included)
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(titre)) {
            showError(input, "Le titre ne peut contenir que des lettres (pas de chiffres ni de symboles).");
            return false;
        }
        
        if (titre.length < 5) {
            showError(input, "Le titre doit contenir au moins 5 caractères.");
            return false;
        }
        
        if (titre.length > 150) {
            showError(input, "Le titre ne peut pas dépasser 150 caractères.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation de la date
    function validateDate(input) {
        const dateValue = input.value;
        
        if (dateValue === '') {
            showError(input, "La date de l'événement est obligatoire.");
            return false;
        }
        
        const eventDate = new Date(dateValue);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (eventDate < today) {
            showError(input, "La date de l'événement ne peut pas être dans le passé.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation de la description
    function validateDescription(textarea) {
        const description = textarea.value.trim();
        const wordCount = description.split(/\s+/).filter(word => word.length > 0).length;
        
        if (description === '') {
            showError(textarea, "La description est obligatoire.");
            return false;
        }
        
        if (wordCount < 10) {
            showError(textarea, `La description doit contenir au moins 10 mots. (${wordCount}/10)`);
            return false;
        }
        
        showSuccess(textarea);
        return true;
    }
    
    // Compteur de mots pour la description
    function updateDescriptionWordCount(textarea) {
        const description = textarea.value.trim();
        const wordCount = description.split(/\s+/).filter(word => word.length > 0).length;
        const counterId = textarea.id + 'WordCount';
        const counter = document.getElementById(counterId);
        
        if (counter) {
            counter.textContent = `${wordCount} mots`;
            counter.style.color = wordCount >= 10 ? '#59ab6e' : '#e74c3c';
        }
    }
    
    // Validation de l'organisateur (text only, no numbers, no symbols)
    function validateOrganisateur(input) {
        const organisateur = input.value.trim();
        
        if (organisateur === '') {
            showError(input, "L'organisateur est obligatoire.");
            return false;
        }
        
        // Only letters and spaces allowed (French characters included)
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(organisateur)) {
            showError(input, "L'organisateur ne peut contenir que des lettres (pas de chiffres ni de symboles).");
            return false;
        }
        
        if (organisateur.length < 3) {
            showError(input, "Le nom de l'organisateur doit contenir au moins 3 caractères.");
            return false;
        }
        
        if (organisateur.length > 100) {
            showError(input, "Le nom de l'organisateur ne peut pas dépasser 100 caractères.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation des frais (minimum 1 DT)
    function validateFrais(input) {
        const frais = input.value.trim();
        
        if (frais === '') {
            showError(input, "Les frais de participation sont obligatoires (minimum 1 DT).");
            return false;
        }
        
        const fraisNum = parseFloat(frais);
        
        if (isNaN(fraisNum)) {
            showError(input, "Les frais de participation doivent être un nombre.");
            return false;
        }
        
        if (fraisNum < 1) {
            showError(input, "Les frais de participation doivent être d'au moins 1 DT.");
            return false;
        }
        
        showSuccess(input);
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
            feedback.style.color = '#e74c3c';
            feedback.style.fontSize = '13px';
            feedback.style.marginTop = '5px';
            formGroup.appendChild(feedback);
        }
        feedback.textContent = message;
        feedback.style.color = '#e74c3c';
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
    if (addEventForm) {
        const titreInput = addEventForm.querySelector('#add_titre');
        const dateInput = addEventForm.querySelector('#add_date');
        const descriptionTextarea = addEventForm.querySelector('#add_description');
        const organisateurInput = addEventForm.querySelector('#add_organisateur');
        const fraisInput = addEventForm.querySelector('#add_frais');
        
        // Validation en temps réel (on input, not just blur)
        if (titreInput) {
            titreInput.addEventListener('input', () => validateTitre(titreInput));
            titreInput.addEventListener('blur', () => validateTitre(titreInput));
        }
        if (dateInput) {
            dateInput.addEventListener('input', () => validateDate(dateInput));
            dateInput.addEventListener('blur', () => validateDate(dateInput));
        }
        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', () => {
                validateDescription(descriptionTextarea);
                updateDescriptionWordCount(descriptionTextarea);
            });
            descriptionTextarea.addEventListener('blur', () => validateDescription(descriptionTextarea));
        }
        if (organisateurInput) {
            organisateurInput.addEventListener('input', () => validateOrganisateur(organisateurInput));
            organisateurInput.addEventListener('blur', () => validateOrganisateur(organisateurInput));
        }
        if (fraisInput) {
            fraisInput.addEventListener('input', () => validateFrais(fraisInput));
            fraisInput.addEventListener('blur', () => validateFrais(fraisInput));
        }
        
        // Initialiser le compteur de mots
        if (descriptionTextarea) updateDescriptionWordCount(descriptionTextarea);
        
        // Validation à la soumission
        addEventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isTitreValid = validateTitre(titreInput);
            const isDateValid = validateDate(dateInput);
            const isDescriptionValid = validateDescription(descriptionTextarea);
            const isOrganisateurValid = validateOrganisateur(organisateurInput);
            const isFraisValid = validateFrais(fraisInput);
            
            if (isTitreValid && isDateValid && isDescriptionValid && isOrganisateurValid && isFraisValid) {
                const submitBtn = addEventForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
                submitBtn.disabled = true;
                addEventForm.submit();
            } else {
                const firstError = addEventForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    // ========== FORMULAIRE DE MODIFICATION ==========
    if (editEventForm) {
        const titreInput = editEventForm.querySelector('#edit_titre');
        const dateInput = editEventForm.querySelector('#edit_date');
        const descriptionTextarea = editEventForm.querySelector('#edit_description');
        const organisateurInput = editEventForm.querySelector('#edit_organisateur');
        const fraisInput = editEventForm.querySelector('#edit_frais');
        
        // Validation en temps réel (on input, not just blur)
        if (titreInput) {
            titreInput.addEventListener('input', () => validateTitre(titreInput));
            titreInput.addEventListener('blur', () => validateTitre(titreInput));
        }
        if (dateInput) {
            dateInput.addEventListener('input', () => validateDate(dateInput));
            dateInput.addEventListener('blur', () => validateDate(dateInput));
        }
        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', () => {
                validateDescription(descriptionTextarea);
                updateDescriptionWordCount(descriptionTextarea);
            });
            descriptionTextarea.addEventListener('blur', () => validateDescription(descriptionTextarea));
        }
        if (organisateurInput) {
            organisateurInput.addEventListener('input', () => validateOrganisateur(organisateurInput));
            organisateurInput.addEventListener('blur', () => validateOrganisateur(organisateurInput));
        }
        if (fraisInput) {
            fraisInput.addEventListener('input', () => validateFrais(fraisInput));
            fraisInput.addEventListener('blur', () => validateFrais(fraisInput));
        }
        
        // Initialiser le compteur de mots
        if (descriptionTextarea) updateDescriptionWordCount(descriptionTextarea);
        
        // Validation à la soumission avec AJAX
        editEventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isTitreValid = validateTitre(titreInput);
            const isDateValid = validateDate(dateInput);
            const isDescriptionValid = validateDescription(descriptionTextarea);
            const isOrganisateurValid = validateOrganisateur(organisateurInput);
            const isFraisValid = validateFrais(fraisInput);
            
            if (isTitreValid && isDateValid && isDescriptionValid && isOrganisateurValid && isFraisValid) {
                const submitBtn = editEventForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
                submitBtn.disabled = true;
                
                // Create FormData
                const formData = new FormData(editEventForm);
                formData.append('action', 'update');
                
                // Send AJAX request
                fetch('../../controllers/evenementControllers.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage(data.message, 'success');
                        // Close modal
                        const editModal = document.getElementById('editEventModal');
                        if (editModal) {
                            editModal.style.display = 'none';
                            document.body.style.overflow = 'auto';
                        }
                        // Reload page after a short delay to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Show error message
                        showMessage(data.message, 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Une erreur est survenue lors de la mise à jour.', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            } else {
                const firstError = editEventForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    // Function to show messages
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.ajax-message');
        existingMessages.forEach(msg => msg.remove());
        
        // Create message element
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ajax-message';
        messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        `;
        
        if (type === 'success') {
            messageDiv.style.background = '#59ab6e';
            messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        } else {
            messageDiv.style.background = '#e74c3c';
            messageDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        }
        
        document.body.appendChild(messageDiv);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            messageDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }
    
    // Add CSS animations
    if (!document.getElementById('ajax-message-styles')) {
        const style = document.createElement('style');
        style.id = 'ajax-message-styles';
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
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});

