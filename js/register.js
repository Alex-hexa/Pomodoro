document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.onsubmit = async function(event) {
            event.preventDefault(); // Empêcher le rechargement de la page
            console.log("Formulaire soumis.");

            const formData = new FormData(this);
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });

            console.log("Réponse du serveur : ", response); // Afficher la réponse

            if (response.ok) {
                const data = await response.json();
                console.log("Données reçues : ", data); // Afficher les données reçues

                // Vérifier si le token est présent dans la réponse
                if (data.token) {
                    sessionStorage.setItem('jwtToken', data.token); // Stocker le token dans sessionStorage
                    window.location.href = 'index.php'; // Rediriger vers index.php
                } else {
                    alert("Erreur lors de la récupération du token.");
                }
            } else {
                const errorText = await response.text();
                alert("Erreur : " + errorText); // Affiche l'erreur
            }
        };
        window.location.href = 'index.php'; // Rediriger vers index.php
    } else {
        console.error("Formulaire non trouvé.");
    }
});