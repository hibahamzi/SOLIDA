<?php
// chatbot.php - Assistant SOLIDA
// Ce fichier DOIT être inclus dans d'autres fichiers PHP
?>

<!-- Notification -->
<div id="chatbot-notification" class="chatbot-notification">
    <strong><i class="fas fa-robot"></i> Assistant SOLIDA</strong>
    <p style="margin: 5px 0 0 0; font-size: 14px;">Besoin d'aide ? Cliquez sur l'icône !</p>
</div>

<!-- Chatbot -->
<div id="solida-chatbot">
    <div id="chatbot-icon">
        <i class="fas fa-comment"></i>
    </div>
    
    <div id="chatbot-box">
        <div class="chatbot-header">
            <strong><i class="fas fa-robot"></i> Assistant SOLIDA</strong>
            <span id="chatbot-close">✕</span>
        </div>
        
        <div id="chatbot-messages">
            <!-- Les messages seront ajoutés ici dynamiquement -->
        </div>
        
        <div class="quick-actions">
            <button class="quick-btn" onclick="sendQuickMessage('Comment créer une réclamation ?')">Créer réclamation</button>
            <button class="quick-btn" onclick="sendQuickMessage('Voir mes réclamations')">Mes réclamations</button>
            <button class="quick-btn" onclick="sendQuickMessage('Comment contacter SOLIDA ?')">Contact</button>
            <button class="quick-btn" onclick="sendQuickMessage('Aide générale')">Aide</button>
        </div>
        
        <div class="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Tapez votre message ici..." autocomplete="off">
            <button id="chatbot-send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Styles pour le chatbot */
    #solida-chatbot {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        font-family: Arial, sans-serif;
    }
    
    #chatbot-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
    }
    
    #chatbot-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(76, 175, 80, 0); }
        100% { box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
    }
    
    #chatbot-box {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 350px;
        height: 500px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }
    
    .chatbot-header {
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        color: white;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .chatbot-header strong {
        font-size: 16px;
    }
    
    .chatbot-header span {
        cursor: pointer;
        font-size: 20px;
        opacity: 0.8;
    }
    
    .chatbot-header span:hover {
        opacity: 1;
    }
    
    #chatbot-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f9f9f9;
    }
    
    .message-container {
        margin-bottom: 10px;
        display: flex;
    }
    
    .bot-message {
        justify-content: flex-start;
    }
    
    .user-message {
        justify-content: flex-end;
    }
    
    .message-bubble {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 18px;
        line-height: 1.4;
        word-wrap: break-word;
    }
    
    .bot-bubble {
        background: #e8f5e9;
        color: #2E7D32;
        border-bottom-left-radius: 5px;
    }
    
    .user-bubble {
        background: #4CAF50;
        color: white;
        border-bottom-right-radius: 5px;
    }
    
    .message-time {
        font-size: 11px;
        opacity: 0.7;
        margin-top: 5px;
    }
    
    .chatbot-input-area {
        padding: 15px;
        background: white;
        border-top: 1px solid #e0e0e0;
        display: flex;
        gap: 10px;
    }
    
    #chatbot-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
    }
    
    #chatbot-input:focus {
        border-color: #4CAF50;
    }
    
    #chatbot-send {
        background: #4CAF50;
        color: white;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s;
    }
    
    #chatbot-send:hover {
        background: #45a049;
    }
    
    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 15px;
        background: #f0f0f0;
        border-top: 1px solid #ddd;
    }
    
    .quick-btn {
        background: white;
        border: 1px solid #4CAF50;
        color: #4CAF50;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .quick-btn:hover {
        background: #4CAF50;
        color: white;
    }
    
    /* Styles pour les liens dans les messages */
    .chatbot-link {
        color: #2E7D32;
        text-decoration: underline;
        font-weight: bold;
        cursor: pointer;
        margin: 0 3px;
    }
    
    .chatbot-link:hover {
        color: #1B5E20;
    }
    
    /* Scrollbar personnalisée */
    #chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #chatbot-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    #chatbot-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    #chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Notification */
    .chatbot-notification {
        position: fixed;
        bottom: 90px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 10px 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        display: none;
        max-width: 300px;
        z-index: 999;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        #chatbot-box {
            width: 300px;
            height: 450px;
            right: 10px;
            bottom: 60px;
        }
        
        #chatbot-icon {
            width: 50px;
            height: 50px;
        }
        
        .chatbot-notification {
            right: 10px;
            bottom: 80px;
            max-width: 250px;
        }
    }
</style>

<script>
// Variables globales
let chatHistory = [];
let chatOpen = false;
let notificationShown = false;

// Initialisation du chatbot
document.addEventListener('DOMContentLoaded', function() {
    const chatbotIcon = document.getElementById('chatbot-icon');
    const chatbotBox = document.getElementById('chatbot-box');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const chatbotMessages = document.getElementById('chatbot-messages');
    
    // Vérifier si les éléments existent
    if (!chatbotIcon || !chatbotBox || !chatbotClose) {
        console.error('Éléments du chatbot non trouvés');
        return;
    }
    
    // Ouvrir/fermer le chatbot
    chatbotIcon.addEventListener('click', toggleChat);
    chatbotClose.addEventListener('click', toggleChat);
    
    // Envoyer un message avec le bouton
    chatbotSend.addEventListener('click', sendMessage);
    
    // Envoyer un message avec Enter
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Message de bienvenue automatique
    setTimeout(() => {
        if (!chatOpen && !notificationShown) {
            showNotification("L'assistant SOLIDA est là pour vous aider !");
        }
    }, 3000);
    
    // Fermer la notification en cliquant dessus
    const notification = document.getElementById('chatbot-notification');
    if (notification) {
        notification.addEventListener('click', hideNotification);
    }
});

function toggleChat() {
    const chatbotBox = document.getElementById('chatbot-box');
    const chatbotMessages = document.getElementById('chatbot-messages');
    
    if (!chatbotBox || !chatbotMessages) return;
    
    chatOpen = !chatOpen;
    chatbotBox.style.display = chatOpen ? 'flex' : 'none';
    
    if (chatOpen && chatbotMessages.innerHTML === '') {
        addBotMessage('Bonjour ! 👋 Je suis l\'assistant SOLIDA. Je peux vous aider avec :<br>• La création et gestion des réclamations<br>• Les événements<br>• Les dons<br>• Les informations générales<br><br>Comment puis-je vous aider aujourd\'hui ?');
    }
    
    // Fermer la notification si elle est ouverte
    hideNotification();
}

function showNotification(message) {
    const notification = document.getElementById('chatbot-notification');
    if (!notification) return;
    
    notificationShown = true;
    
    // Mettre à jour le message
    const paragraph = notification.querySelector('p');
    if (paragraph) {
        paragraph.textContent = message;
    }
    notification.style.display = 'block';
    
    // Fermer automatiquement après 8 secondes
    setTimeout(hideNotification, 8000);
}

function hideNotification() {
    const notification = document.getElementById('chatbot-notification');
    if (notification) {
        notification.style.display = 'none';
        notificationShown = false;
    }
}

function sendMessage() {
    const chatbotInput = document.getElementById('chatbot-input');
    if (!chatbotInput) return;
    
    const message = chatbotInput.value.trim();
    
    if (message) {
        addUserMessage(message);
        chatbotInput.value = '';
        processUserMessage(message);
    }
}

function sendQuickMessage(message) {
    addUserMessage(message);
    processUserMessage(message);
}

function addUserMessage(text) {
    const chatbotMessages = document.getElementById('chatbot-messages');
    if (!chatbotMessages) return;
    
    const time = getCurrentTime();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message-container user-message';
    messageDiv.innerHTML = `
        <div class="message-bubble user-bubble">
            ${escapeHtml(text)}
            <div class="message-time">${time}</div>
        </div>
    `;
    
    chatbotMessages.appendChild(messageDiv);
    scrollToBottom();
    
    // Ajouter à l'historique
    chatHistory.push({
        type: 'user',
        message: text,
        time: time
    });
}

function addBotMessage(text) {
    const chatbotMessages = document.getElementById('chatbot-messages');
    if (!chatbotMessages) return;
    
    const time = getCurrentTime();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message-container bot-message';
    messageDiv.innerHTML = `
        <div class="message-bubble bot-bubble">
            ${text}
            <div class="message-time">${time}</div>
        </div>
    `;
    
    chatbotMessages.appendChild(messageDiv);
    scrollToBottom();
    
    // Ajouter à l'historique
    chatHistory.push({
        type: 'bot',
        message: text,
        time: time
    });
}

function processUserMessage(message) {
    const lowerMessage = message.toLowerCase();
    
    // Simuler un temps de réponse
    setTimeout(() => {
        const response = generateBotResponse(lowerMessage);
        addBotMessage(response);
    }, 800);
}

function generateBotResponse(message) {
    // Détection des intentions
    if (message.includes('bonjour') || message.includes('salut') || message.includes('hello') || message.includes('hi')) {
        return 'Bonjour ! 😊 Comment puis-je vous aider aujourd\'hui sur la plateforme SOLIDA ?';
    }
    
    if (message.includes('merci') || message.includes('thanks') || message.includes('thank you')) {
        return 'Je vous en prie ! N\'hésitez pas si vous avez d\'autres questions. 😊';
    }
    
    if (message.includes('au revoir') || message.includes('bye') || message.includes('goodbye')) {
        return 'Au revoir ! À bientôt sur SOLIDA. 👋';
    }
    
    if ((message.includes('créer') || message.includes('creer') || message.includes('ajouter') || message.includes('nouvelle')) && 
        (message.includes('réclamation') || message.includes('reclamation'))) {
        return 'Pour créer une réclamation :<br><br>1. Cliquez sur le bouton <strong>"+ Ajouter une réclamation"</strong> en haut de la page<br>2. Remplissez le formulaire avec vos informations<br>3. Validez l\'envoi<br><br><span class="chatbot-link" onclick="redirectToPage(\'AddReclamation.php\')">Cliquez ici pour créer une réclamation maintenant</span>';
    }
    
    if (message.includes('mes réclamations') || message.includes('liste') || message.includes('voir') || 
        message.includes('reclamations') || message.includes('réclamations')) {
        return 'Pour voir vos réclamations :<br><br>• Vous êtes actuellement sur la page de la liste des réclamations<br>• Vous pouvez modifier ou supprimer vos réclamations<br>• Cliquez sur "Modifier" ou "Supprimer" dans la colonne Actions<br><br><span class="chatbot-link" onclick="location.reload()">Rafraîchir la liste</span>';
    }
    
    if ((message.includes('modifier') || message.includes('edit') || message.includes('update')) && 
        (message.includes('réclamation') || message.includes('reclamation'))) {
        return 'Pour modifier une réclamation :<br><br>1. Trouvez votre réclamation dans la liste<br>2. Cliquez sur le bouton <strong>"Modifier"</strong><br>3. Modifiez les informations nécessaires<br>4. Sauvegardez les changements<br><br>Assurez-vous d\'avoir l\'ID de votre réclamation.';
    }
    
    if ((message.includes('supprimer') || message.includes('delete') || message.includes('remove')) && 
        (message.includes('réclamation') || message.includes('reclamation'))) {
        return 'Pour supprimer une réclamation :<br><br>1. Trouvez votre réclamation dans la liste<br>2. Cliquez sur le bouton <strong>"Supprimer"</strong><br>3. Confirmez la suppression<br><br><span style="color: #d32f2f;">⚠️ Attention : Cette action est irréversible !</span>';
    }
    
    if (message.includes('contact') || message.includes('contacter') || message.includes('email') || 
        message.includes('téléphone') || message.includes('telephone') || message.includes('phone') || 
        message.includes('mail')) {
        return 'Pour nous contacter :<br><br>📧 Email : <strong>info@company.com</strong><br>📞 Téléphone : <strong>010-020-0340</strong><br><br>Nous sommes disponibles du lundi au vendredi, de 9h à 17h.';
    }
    
    if (message.includes('aide') || message.includes('help') || message.includes('assistance')) {
        return 'Je peux vous aider avec plusieurs aspects :<br><br>1. <strong>Réclamations</strong> : Créer, modifier ou suivre vos réclamations<br>2. <strong>Événements</strong> : <span class="chatbot-link" onclick="redirectToPage(\'evenement.php\')">Voir les événements à venir</span><br>3. <strong>Dons</strong> : Informations sur les dons (bientôt disponible)<br>4. <strong>Compte</strong> : <span class="chatbot-link" onclick="redirectToPage(\'sign-in.php\')">Connexion</span> / <span class="chatbot-link" onclick="redirectToPage(\'sign-up.php\')">Inscription</span><br><br>Quel sujet vous intéresse ?';
    }
    
    if (message.includes('solida') || message.includes('plateforme') || message.includes('site') || 
        message.includes('website') || message.includes('application')) {
        return 'SOLIDA est une plateforme de solidarité communautaire qui permet :<br><br>• De créer et gérer des réclamations<br>• De participer à des événements<br>• De faire des dons (bientôt)<br>• De connecter la communauté<br><br><span class="chatbot-link" onclick="redirectToPage(\'index.html\')">Retour à l\'accueil</span>';
    }
    
    if (message.includes('événement') || message.includes('evenement') || message.includes('event') || 
        message.includes('activité') || message.includes('activite')) {
        return 'Pour les événements :<br><br>• Consultez la page des événements pour voir tous les événements à venir<br>• Inscrivez-vous aux événements qui vous intéressent<br>• Participez aux activités communautaires<br><br><span class="chatbot-link" onclick="redirectToPage(\'evenement.php\')">Voir les événements maintenant</span>';
    }
    
    if (message.includes('don') || message.includes('donner') || message.includes('donation')) {
        return 'La fonctionnalité <strong>Dons</strong> sera bientôt disponible sur SOLIDA !<br><br>Nous travaillons actuellement sur cette fonctionnalité pour permettre :<br>• Les dons financiers<br>• Les dons en nature<br>• Le suivi des dons<br><br>Revenez bientôt !';
    }
    
    if (message.includes('statut') || message.includes('status') || message.includes('suivre') || 
        message.includes('état') || message.includes('etat') || message.includes('follow')) {
        return 'Pour suivre le statut de votre réclamation :<br><br>1. Consultez la liste des réclamations<br>2. Regardez la colonne <strong>"Statut"</strong><br>3. Les statuts possibles sont : En attente, En cours, Résolu<br>4. Vous pouvez modifier une réclamation pour mettre à jour son statut';
    }
    
    if (message.includes('urgence') || message.includes('urgent') || message.includes('important') || 
        message.includes('priorité') || message.includes('priorite') || message.includes('priority')) {
        return 'Pour les urgences :<br><br>1. Lors de la création d\'une réclamation, sélectionnez la priorité <strong>"Haute"</strong><br>2. Pour les urgences médicales ou de sécurité, contactez directement les services d\'urgence<br>3. Notre équipe traite les réclamations par ordre de priorité';
    }
    
    if (message.includes('problème') || message.includes('probleme') || message.includes('problem') || 
        message.includes('bug') || message.includes('erreur') || message.includes('error')) {
        return 'Si vous rencontrez un problème technique :<br><br>1. Essayez de rafraîchir la page (F5)<br>2. Vérifiez votre connexion internet<br>3. Contactez notre support technique à <strong>support@company.com</strong><br>4. Décrivez le problème en détail avec une capture d\'écran si possible';
    }
    
    if (message.includes('connexion') || message.includes('login') || message.includes('signin') || 
        message.includes('se connecter')) {
        return 'Pour vous connecter :<br><br>1. Cliquez sur "Sign in" dans le menu principal<br>2. Entrez vos identifiants<br>3. Si vous n\'avez pas de compte, <span class="chatbot-link" onclick="redirectToPage(\'sign-up.php\')">inscrivez-vous ici</span>';
    }
    
    if (message.includes('inscription') || message.includes('register') || message.includes('signup') || 
        message.includes('créer compte') || message.includes('creer compte')) {
        return 'Pour créer un compte :<br><br>1. Cliquez sur "Sign in" dans le menu principal<br>2. Puis cliquez sur "Créer un compte" ou <span class="chatbot-link" onclick="redirectToPage(\'sign-up.php\')">inscrivez-vous directement ici</span><br>3. Remplissez le formulaire d\'inscription<br>4. Validez votre email si nécessaire';
    }
    
    // Réponse par défaut
    return 'Je ne suis pas sûr de comprendre votre question. 😕<br><br>Voici ce que je peux vous aider avec :<br>• Création et gestion de réclamations<br>• Informations sur les événements<br>• Contact avec SOLIDA<br>• Aide générale sur la plateforme<br><br>Pouvez-vous reformuler votre question ou choisir une option ci-dessous ?';
}

function redirectToPage(page) {
    if (page) {
        window.location.href = page;
    }
}

function getCurrentTime() {
    const now = new Date();
    return now.getHours().toString().padStart(2, '0') + ':' + 
           now.getMinutes().toString().padStart(2, '0');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollToBottom() {
    const chatbotMessages = document.getElementById('chatbot-messages');
    if (chatbotMessages) {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
}

// Exposer les fonctions globalement pour qu'elles soient accessibles depuis d'autres fichiers
window.toggleChat = toggleChat;
window.sendMessage = sendMessage;
window.sendQuickMessage = sendQuickMessage;
window.addUserMessage = addUserMessage;
window.addBotMessage = addBotMessage;
window.processUserMessage = processUserMessage;
window.redirectToPage = redirectToPage;
</script>