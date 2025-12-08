/**
 * Validation côté client pour les participations - SOLIDA
 */

document.addEventListener('DOMContentLoaded', function() {
    const participationForm = document.getElementById('participationForm');
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
    
    // ========== FORMULAIRE DE PARTICIPATION ==========
    if (participationForm) {
        const motivationTextarea = participationForm.querySelector('#motivation');
        const sourceInput = participationForm.querySelector('#source_information');
        const typeParticipationSelect = participationForm.querySelector('#type_participation');
        const nombrePersonnesInput = participationForm.querySelector('#nombre_personnes');
        const nombrePersonnesGroup = participationForm.querySelector('#nombre_personnes_group');
        
        // Toggle nombre de personnes based on type
        if (typeParticipationSelect) {
            typeParticipationSelect.addEventListener('change', function() {
                if (this.value === 'groupe') {
                    nombrePersonnesGroup.style.display = 'block';
                    nombrePersonnesInput.setAttribute('data-required', 'true');
                } else {
                    nombrePersonnesGroup.style.display = 'none';
                    nombrePersonnesInput.removeAttribute('data-required');
                    nombrePersonnesInput.value = '1';
                    showSuccess(nombrePersonnesInput);
                }
            });
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
        participationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isMotivationValid = validateMotivation(motivationTextarea);
            const isSourceValid = validateSourceInformation(sourceInput);
            const isNombreValid = validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value);
            
            if (isMotivationValid && isSourceValid && isNombreValid) {
                const submitBtn = participationForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement en cours...';
                submitBtn.disabled = true;
                participationForm.submit();
            } else {
                const firstError = participationForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
    
    // ========== FORMULAIRE DE MODIFICATION ==========
    if (editParticipationForm) {
        const motivationTextarea = editParticipationForm.querySelector('#edit_motivation');
        const sourceInput = editParticipationForm.querySelector('#edit_source_information');
        const typeParticipationSelect = editParticipationForm.querySelector('#edit_type_participation');
        const nombrePersonnesInput = editParticipationForm.querySelector('#edit_nombre_personnes');
        const nombrePersonnesGroup = editParticipationForm.querySelector('#edit_nombre_personnes_group');
        
        // Toggle nombre de personnes based on type
        if (typeParticipationSelect) {
            typeParticipationSelect.addEventListener('change', function() {
                if (this.value === 'groupe') {
                    nombrePersonnesGroup.style.display = 'block';
                    nombrePersonnesInput.setAttribute('data-required', 'true');
                } else {
                    nombrePersonnesGroup.style.display = 'none';
                    nombrePersonnesInput.removeAttribute('data-required');
                    nombrePersonnesInput.value = '1';
                    showSuccess(nombrePersonnesInput);
                }
            });
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
            const isNombreValid = validateNombrePersonnes(nombrePersonnesInput, typeParticipationSelect.value);
            
            if (isMotivationValid && isSourceValid && isNombreValid) {
                const submitBtn = editParticipationForm.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mise à jour en cours...';
                submitBtn.disabled = true;
                editParticipationForm.submit();
            } else {
                const firstError = editParticipationForm.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }
});

