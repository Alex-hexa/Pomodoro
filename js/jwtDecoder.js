// Fonction pour obtenir le token depuis sessionStorage
function getToken() {
    return sessionStorage.getItem('jwtToken');
}

function generateSessionId() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let session = '';

    for (let i = 0; i < 10; i++) {
        const randomIndex = Math.floor(Math.random() * 10);
        session += characters[randomIndex];
    }

    return session;
}

const session = generateSessionId();

// Décoder le JWT
function decodeJWT(token) {
    const payload = JSON.parse(atob(token.split('.')[1]));
    return payload;
}

const jwt = getToken();

if (jwt) {
    // Décoder le JWT et récupérer le nom d'utilisateur
    const decoded = decodeJWT(jwt);
    const username = decoded.username; // Récupérer le nom d'utilisateur
    const userId = decoded.userId; // Récupérer l'ID de l'utilisateur
    const uniq_id = decoded.uniq_id; // Récupérer l'ID unique de l'utilisateur
    document.cookie = `userId=${userId}`;
    document.cookie = `username=${username}`;
    document.cookie = `session=${session}`;
    document.cookie = `uniq_id=${uniq_id}`;
    // ! Ici le cookie est créé pour stocker les informations de l'utilisateur et le passer à la page suivante
    
    // ! Mettre à jour le contenu de la page avec le nom d'utilisateur
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('username').textContent = username;
        document.getElementById('userId').textContent = userId;
        document.getElementById('session').textContent = session;
        document.getElementById('uniq_id').textContent = uniq_id;
    });
} else {
    // * Si le token n'est pas présent, rediriger vers la page de connexion
    window.location.href = 'login.php';
}

function createSessionUrl() {
    const date = new Date();
    const formattedDate = date.toLocaleString('fr-FR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' })
        .replace(/\//g, '-')
        .replace(',', '')
        .replace(/:/g, '-');

    const sessionUrl = `sharedSession.php?date=${formattedDate}`;
}

// ! On s'assure que le DOM est chargé avant d'exécuter le code
createSessionUrl();