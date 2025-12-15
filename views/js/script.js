// script.js
console.log("✅ Fichier script.js chargé et exécuté.");
// Données simulées (Inchangées)
const associations = {
  sang: {
    France: ["Croix Rouge", "Établissement Français du Sang"],
    Sénégal: ["Croix Rouge Sénégalaise"],
    Maroc: ["Croix Rouge Marocaine"]
  },
  argent: {
    France: ["Médecins du Monde", "Secours Populaire"],
    Canada: ["Banque Alimentaire Canada", "Croix-Rouge Canadienne"]
  },
  nourriture: {
    France: ["Banque Alimentaire", "Restos du Cœur"],
    Cameroun: ["Caritas Cameroun"]
  }
};

// Récupérer l'état depuis localStorage
let donType = localStorage.getItem('donType') || '';
let selectedCountry = localStorage.getItem('selectedCountry') || '';
let selectedAssociation = localStorage.getItem('selectedAssociation') || '';

// Sauvegarder dans localStorage
function saveState() {
  localStorage.setItem('donType', donType);
  localStorage.setItem('selectedCountry', selectedCountry);
  localStorage.setItem('selectedAssociation', selectedAssociation);
}

// =========================================================
// ÉTAPE 2 : LOGIQUE DE SÉLECTION DU TYPE DE DON & PAYS (CORRIGÉ)
// =========================================================

/**
 * Met à jour le type de don sélectionné, l'UI et le formulaire PHP.
 * @param {string} type - Le type de don ('argent', 'sang', 'nourriture').
 */
function selectDonType(type) {
  donType = type;
  saveState();
  
  // 1. Mise à jour visuelle des cartes
  updateTypeUI(); 
  
  // 2. Mise à jour du champ caché PHP (CRUCIAL pour la soumission à step2.php)
  const typeInput = document.getElementById('donTypeInput');
  if (typeInput) typeInput.value = type;
  
  // 3. Vérification de l'état du bouton "Suivant"
  checkTypeCountryReady();
}

/**
 * Met à jour le pays sélectionné, l'UI et le formulaire PHP.
 * @param {string} country - Le nom du pays.
 */
function selectCountry(country) {
  selectedCountry = country;
  saveState();
  
  // 1. Mise à jour visuelle des cartes
  updateCountryUI(country);
  
  // 2. Mise à jour du champ caché PHP (CRUCIAL pour la soumission à step2.php)
  const countryInput = document.getElementById('countryInput');
  if (countryInput) countryInput.value = country;

  // 3. Vérification de l'état du bouton "Suivant"
  checkTypeCountryReady();
}

/**
 * Active ou désactive le bouton 'Suivant' de step2.php.
 */
function checkTypeCountryReady() {
  const btn = document.getElementById('btn-next-type');
  // Le bouton est activé SEULEMENT si donType ET selectedCountry sont non-vides
  if (btn) { 
    btn.disabled = !(donType && selectedCountry);
  }
}

// -----------------------------------------------------
// Fonctions de mise à jour visuelle (UI)
// -----------------------------------------------------

function updateTypeUI() {
  document.querySelectorAll('.card[data-type]').forEach(card => {
    card.classList.remove('border', 'border-success');
    if (card.dataset.type === donType) {
      card.classList.add('border', 'border-success');
    }
  });
}

function updateCountryUI(country) {
  document.querySelectorAll('.card[data-country]').forEach(el => {
    el.classList.remove('border', 'border-success');
    if (el.dataset.country === country) {
      el.classList.add('border', 'border-success');
    }
  });
}


// =========================================================
// ÉTAPE 3 : SÉLECTION ASSOCIATION (Logique pour step3.php)
// =========================================================

function selectAssociation(asso) {
  selectedAssociation = asso;
  saveState();
}

function loadAssociations() {
  const list = document.getElementById('associations-list');
  const label = document.getElementById('type-country-label');
  if (!list || !label) return;

  const typeLabel = donType === 'sang' ? 'Sang' : donType === 'argent' ? 'Argent' : 'Nourriture';
  label.textContent = `${typeLabel} - ${selectedCountry}`;

  list.innerHTML = '';
  const assos = associations[donType]?.[selectedCountry] || [];
  if (assos.length === 0) {
    list.innerHTML = '<p class="text-center text-muted col-12">Aucune association disponible.</p>';
    return;
  }
  
  assos.forEach((asso, index) => {
    const isSelected = selectedAssociation === asso;
    
    // Structure simulant la soumission par formulaire de step3.php
    const cardHtml = `
      <form method="POST" action="step3.php" id="form-${index}">
        <input type="hidden" name="association_id" value="${index + 1}">
        <input type="hidden" name="association_name" value="${asso}">
        
        <div class="card association-card card-hover p-3 ${isSelected ? 'border border-success' : ''}" 
             onclick="selectAssociation('${asso.replace(/'/g, "\\'")}'); document.getElementById('form-${index}').submit()">
          <h5 class="card-title">${asso}</h5>
          <p class="card-text text-muted small">Cliquez pour sélectionner</p>
        </div>
      </form>
    `;

    const div = document.createElement('div');
    div.className = 'col-md-5 mb-3';
    div.innerHTML = cardHtml;
    list.appendChild(div);
  });
}


// =========================================================
// INITIALISATION DES PAGES
// =========================================================

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Logique d'initialisation pour step2.php ---
    const initialTypeInput = document.getElementById('donTypeInput');
    const initialCountryInput = document.getElementById('countryInput');
    
    if (initialTypeInput && initialCountryInput) {
        
        // 1a. Récupérer l'état initial (PHP > localStorage > vide)
        donType = initialTypeInput.value || localStorage.getItem('donType') || '';
        selectedCountry = initialCountryInput.value || localStorage.getItem('selectedCountry') || '';
        
        // 1b. S'assurer que les champs cachés reflètent les variables globales JS
        initialTypeInput.value = donType;
        initialCountryInput.value = selectedCountry;
        
        // 1c. Mettre à jour visuellement et activer le bouton
        updateTypeUI();
        updateCountryUI(selectedCountry);
        checkTypeCountryReady();
    }
    
    // --- 2. Logique d'initialisation pour step3.php ---
    if (document.getElementById('associations-list')) {
      loadAssociations();
    }
    
    // --- 3. Logique d'initialisation pour step5.html (Confirmation) ---
    const confTypeElement = document.getElementById('conf-type');
    if (confTypeElement) {
        // Cette logique est purement illustrative si PHP n'a pas pu envoyer les détails
        const typeDisplay = localStorage.getItem('donType') ? localStorage.getItem('donType').charAt(0).toUpperCase() + localStorage.getItem('donType').slice(1) : 'N/A';
        const associationDisplay = localStorage.getItem('selectedAssociation') || 'N/A';
        const countryDisplay = localStorage.getItem('selectedCountry') || 'N/A';
        
        const confAssociation = document.getElementById('conf-association');
        const confPays = document.getElementById('conf-pays');
        const confDate = document.getElementById('conf-date');

        if (confTypeElement) confTypeElement.textContent = typeDisplay;
        if (confAssociation) confAssociation.textContent = associationDisplay;
        if (confPays) confPays.textContent = countryDisplay;
        
        if (confDate) {
            const date = new Date();
            const dateStr = String(date.getDate()).padStart(2, '0') + '/' +
                            String(date.getMonth() + 1).padStart(2, '0') + '/' +
                            date.getFullYear();
            confDate.textContent = dateStr;
        }
    } 

});