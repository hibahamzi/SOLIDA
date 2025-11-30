/**
 * Validation côté client pour les participations - SOLIDA (Back Office)
 */

document.addEventListener('DOMContentLoaded', function() {
    const addParticipationForm = document.getElementById('addParticipationForm');
    const editParticipationForm = document.getElementById('editParticipationForm');
    
    // ========== FONCTIONS DE VALIDATION ==========
    
    // Validation de la motivation
    function validateMotivation(textarea) {
        const motivation = textarea.value.trim();
        const wordCount = motivation.split(/\s+/).filter(word => word.length > 0).length;
        
        if (motivation === '') {
            showError(textarea, "La motivation est obligatoire.");
            return false;
        }
        
        if (wordCount < 10) {
            showError(textarea, `La motivation doit contenir au moins 10 mots. (${wordCount}/10)`);
            return false;
        }
        
        showSuccess(textarea);
        return true;
    }
    
    // Compteur de mots pour la motivation
    function updateMotivationWordCount(textarea) {
        const motivation = textarea.value.trim();
        const wordCount = motivation.split(/\s+/).filter(word => word.length > 0).length;
        const counterId = textarea.id + 'WordCount';
        const counter = document.getElementById(counterId);
        
        if (counter) {
            counter.textContent = `${wordCount} mots`;
            counter.style.color = wordCount >= 10 ? '#59ab6e' : '#e74c3c';
        }
    }
    
    // Validation de la source d'information
    function validateSourceInformation(input) {
        const source = input.value.trim();
        
        if (source === '') {
            showError(input, "La source d'information est obligatoire.");
            return false;
        }
        
        if (source.length < 3) {
            showError(input, "La source d'information doit contenir au moins 3 caractères.");
            return false;
        }
        
        if (source.length > 100) {
            showError(input, "La source d'information ne peut pas dépasser 100 caractères.");
            return false;
        }
        
        showSuccess(input);
        return true;
    }
    
    // Validation du nombre de personnes
    function validateNombrePersonnes(input, typeParticipation) {
        const nombre = input.value.trim();
        
        if (typeParticipation === 'groupe') {
            if (nombre === '') {
                showError(input, "Le nombre de personnes est obligatoire pour une participation en groupe.");
                return false;
            }
            
            const nombreNum = parseInt(nombre);
            
            if (isNaN(nombreNum)) {
                showError(input, "Le nombre de personnes doit être un nombre.");
                return false;
            }
            
            if (nombreNum < 2) {
                showError(input, "Pour une participation en groupe, le nombre de personnes doit être d'au moins 2.");
                return false;
            }
            
            if (nombreNum > 50) {
                showError(input, "Le nombre de personnes ne peut pas dépasser 50.");
                return false;
            }
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
    
    // ========== FORMULAIRE DE MODIFICATION ==========
    if (editParticipationForm) {
        const motivationTextarea = editParticipationForm.querySelector('#edit_motivation');
        const sourceInput = editParticipationForm.querySelector('#edit_source_information');
        const typeParticipationSelect = editParticipationForm.querySelector('#edit_type_participation');
        const nombrePersonnesInput = editParticipationForm.querySelector('#edit_nombre_personnes');
        const nombrePersonnesGroup = editParticipationForm.querySelector('#edit_nombre_personnes_group');
        
        // Toggle nombre de personnes based on type (admin can change this now)
        if (typeParticipationSelect) {
            typeParticipationSelect.addEventListener('change', function() {
                if (this.value === 'groupe') {
                    nombrePersonnesGroup.style.display = 'block';
                    nombrePersonnesInput.removeAttribute('disabled');
                    nombrePersonnesInput.setAttribute('name', 'nombre_personnes');
                } else {
                    nombrePersonnesGroup.style.display = 'none';
                    nombrePersonnesInput.setAttribute('disabled', 'disabled');
                    nombrePersonnesInput.removeAttribute('name');
                    nombrePersonnesInput.value = '1';
                    showSuccess(nombrePersonnesInput);
                }
            });
        }
        
        // Show/hide nombre group based on initial value
        if (typeParticipationSelect && typeParticipationSelect.value === 'groupe') {
            if (nombrePersonnesGroup) {
                nombrePersonnesGroup.style.display = 'block';
            }
        }
        
        // Validation en temps réel (on input, not just blur)
        if (motivationTextarea) {
            motivationTextarea.addEventListener('input', () => {
                validateMotivation(motivationTextarea);
                updateMotivationWordCount(motivationTextarea);
            });
            motivationTextarea.addEventListener('blur', () => validateMotivation(motivationTextarea));
        }
        
        if (sourceInput) {
            sourceInput.addEventListener('input', () => validateSourceInformation(sourceInput));
            sourceInput.addEventListener('blur', () => validateSourceInformation(sourceInput));
        }
        
        if (nombrePersonnesInput) {
            nombrePersonnesInput.addEventListener('input', () => {
                validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value);
            });
            nombrePersonnesInput.addEventListener('blur', () => {
                validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value);
            });
        }
        
        // Initialiser le compteur de mots
        if (motivationTextarea) updateMotivationWordCount(motivationTextarea);
        
        // Validation à la soumission
        editParticipationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isMotivationValid = validateMotivation(motivationTextarea);
            const isSourceValid = validateSourceInformation(sourceInput);
            // Skip nombre validation if type is 'seul' since it's disabled
            const isNombreValid = typeParticipationSelect.value === 'groupe' 
                ? validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value)
                : true;
            
            if (isMotivationValid && isSourceValid && isNombreValid) {
                const submitBtn = editParticipationForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
                submitBtn.disabled = true;
                
                // Create FormData manually to include disabled field values
                const formData = new FormData();
                
                // Add all enabled fields
                formData.append('action', 'update');
                formData.append('id_participation', document.getElementById('edit_participation_id').value);
                formData.append('motivation', motivationTextarea.value);
                formData.append('source_information', sourceInput.value);
                
                // Add all fields including type_participation and desir_dejeuner (admin can modify everything)
                formData.append('type_participation', typeParticipationSelect.value);
                formData.append('desir_dejeuner', document.getElementById('edit_desir_dejeuner').value);
                
                // Add nombre_personnes based on type
                if (typeParticipationSelect.value === 'groupe') {
                    formData.append('nombre_personnes', nombrePersonnesInput.value || 2);
                } else {
                    formData.append('nombre_personnes', 1);
                }
                
                // Use relative path like the event form
                fetch('../../controllers/participationController.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    // Check if response is actually JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If not JSON, get text to see what we got
                        return response.text().then(text => {
                            console.error('Expected JSON but got:', text.substring(0, 200));
                            throw new Error('Server returned non-JSON response');
                        });
                    }
                })
                .then(data => {
                    if (data && data.success) {
                        // Show success message using the same function as events
                        if (typeof showMessage === 'function') {
                            showMessage(data.message, 'success');
                        } else {
                            showSuccessMessage(data.message);
                        }
                        // Close modal
                        closeEditParticipationModal();
                        // Reload page after a short delay to show updated data (like event form)
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Show error message
                        const errorMsg = data && data.message ? data.message : 'Erreur lors de la mise à jour.';
                        if (typeof showMessage === 'function') {
                            showMessage(errorMsg, 'error');
                        } else {
                            showErrorMessage(errorMsg);
                        }
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMsg = 'Une erreur est survenue lors de la mise à jour.';
                    if (typeof showMessage === 'function') {
                        showMessage(errorMsg, 'error');
                    } else {
                        showErrorMessage(errorMsg);
                    }
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                });
            } else {
                const firstError = editParticipationForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
        
        // Helper functions for messages
        function showSuccessMessage(message) {
            // Remove existing messages
            const existingMsg = document.querySelector('.participation-success-message');
            if (existingMsg) existingMsg.remove();
            
            // Create success message
            const msgDiv = document.createElement('div');
            msgDiv.className = 'participation-success-message';
            msgDiv.style.cssText = 'background: #59ab6e; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; position: fixed; top: 20px; right: 20px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            msgDiv.innerHTML = '<i class="fas fa-check-circle"></i> <span>' + message + '</span>';
            document.body.appendChild(msgDiv);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                msgDiv.style.transition = 'opacity 0.5s ease';
                msgDiv.style.opacity = '0';
                setTimeout(() => msgDiv.remove(), 500);
            }, 3000);
        }
        
        function showErrorMessage(message) {
            // Remove existing messages
            const existingMsg = document.querySelector('.participation-error-message');
            if (existingMsg) existingMsg.remove();
            
            // Create error message
            const msgDiv = document.createElement('div');
            msgDiv.className = 'participation-error-message';
            msgDiv.style.cssText = 'background: #e74c3c; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; position: fixed; top: 20px; right: 20px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            msgDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <span>' + message + '</span>';
            document.body.appendChild(msgDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                msgDiv.style.transition = 'opacity 0.5s ease';
                msgDiv.style.opacity = '0';
                setTimeout(() => msgDiv.remove(), 500);
            }, 5000);
        }
        
        // Update participation row in table without page reload
        function updateParticipationRow(formData) {
            // The row will be updated on next page load, but for now we just close modal
            // If you want to update the row dynamically, you can do it here
            // For simplicity, we just close the modal and show success message
        }
        
        // Make closeEditParticipationModal accessible globally
        window.closeEditParticipationModal = function() {
            const modal = document.getElementById('editParticipationModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                // Reset form
                const form = document.getElementById('editParticipationForm');
                if (form) {
                    form.reset();
                    // Clear validation states
                    const invalidFields = form.querySelectorAll('.is-invalid');
                    invalidFields.forEach(field => {
                        field.classList.remove('is-invalid');
                    });
                    const feedbacks = form.querySelectorAll('.invalid-feedback');
                    feedbacks.forEach(feedback => feedback.remove());
                }
            }
        };
    }
    
    // ========== FORMULAIRE DE CRÉATION (BACK OFFICE) ==========
    if (addParticipationForm) {
        const eventSelect = addParticipationForm.querySelector('#add_participation_event');
        const userSelect = addParticipationForm.querySelector('#add_participation_user');
        const motivationTextarea = addParticipationForm.querySelector('#add_motivation');
        const sourceInput = addParticipationForm.querySelector('#add_source_information');
        const typeParticipationSelect = addParticipationForm.querySelector('#add_type_participation');
        const nombrePersonnesInput = addParticipationForm.querySelector('#add_nombre_personnes');
        const nombrePersonnesGroup = addParticipationForm.querySelector('#add_nombre_personnes_group');
        
        // Toggle nombre de personnes based on type
        if (typeParticipationSelect) {
            typeParticipationSelect.addEventListener('change', function() {
                if (this.value === 'groupe') {
                    nombrePersonnesGroup.style.display = 'block';
                    nombrePersonnesInput.removeAttribute('disabled');
                    nombrePersonnesInput.setAttribute('name', 'nombre_personnes');
                } else {
                    nombrePersonnesGroup.style.display = 'none';
                    nombrePersonnesInput.setAttribute('disabled', 'disabled');
                    nombrePersonnesInput.removeAttribute('name');
                    nombrePersonnesInput.value = '1';
                    showSuccess(nombrePersonnesInput);
                }
            });
        }
        
        // Validation en temps réel
        if (motivationTextarea) {
            motivationTextarea.addEventListener('input', () => {
                validateMotivation(motivationTextarea);
                updateMotivationWordCount(motivationTextarea);
            });
            motivationTextarea.addEventListener('blur', () => validateMotivation(motivationTextarea));
        }
        
        if (sourceInput) {
            sourceInput.addEventListener('input', () => validateSourceInformation(sourceInput));
            sourceInput.addEventListener('blur', () => validateSourceInformation(sourceInput));
        }
        
        if (nombrePersonnesInput) {
            nombrePersonnesInput.addEventListener('input', () => {
                validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value);
            });
            nombrePersonnesInput.addEventListener('blur', () => {
                validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value);
            });
        }
        
        // Initialiser le compteur de mots
        if (motivationTextarea) updateMotivationWordCount(motivationTextarea);
        
        // Validation à la soumission
        addParticipationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required selects
            let isValid = true;
            
            if (!eventSelect || !eventSelect.value) {
                showError(eventSelect, "Veuillez sélectionner un événement.");
                isValid = false;
            } else {
                showSuccess(eventSelect);
            }
            
            if (!userSelect || !userSelect.value) {
                showError(userSelect, "Veuillez sélectionner un participant.");
                isValid = false;
            } else {
                showSuccess(userSelect);
            }
            
            const isMotivationValid = validateMotivation(motivationTextarea);
            const isSourceValid = validateSourceInformation(sourceInput);
            const isNombreValid = typeParticipationSelect.value === 'groupe' 
                ? validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value)
                : true;
            
            if (isValid && isMotivationValid && isSourceValid && isNombreValid) {
                const submitBtn = addParticipationForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
                submitBtn.disabled = true;
                addParticipationForm.submit();
            } else {
                const firstError = addParticipationForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
});

