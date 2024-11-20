function checkSessionStorage() {
    const token = sessionStorage.getItem('jwtToken');
    return token !== null; // Retourne true si le token est présent, false sinon
}

// Vérifie si le token est présent dans le sessionStorage
const isTokenPresent = checkSessionStorage();
const currentPage = window.location.pathname;

if (!isTokenPresent) {
    // Si le token n'est pas présent, redirige vers la page de connexion
    if (currentPage !== '/login.php' && currentPage !== '/register.php') {
        window.location.href = 'login.php'; // Redirection
    }
} else {
    // Si le token est présent, empêche l'accès aux pages de connexion et d'inscription
    if (currentPage.endsWith('login.php') || currentPage.endsWith('register.php')) {
        window.location.href = 'index.php'; // Redirige vers index.php
    }
}

// Exporter la fonction pour qu'elle soit accessible dans d'autres fichiers
if (typeof module !== 'undefined') {
    module.exports = { checkSessionStorage };
}