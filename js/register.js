document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registrationForm');
    let submitInProgress = false; // Indicateur pour éviter plusieurs soumissions

    if (form) {
        form.onsubmit = async function (event) {
            event.preventDefault(); // Empêche le rechargement de la page

            if (submitInProgress) return; // Si une soumission est déjà en cours, ne rien faire
            submitInProgress = true; // Définir l'indicateur

            console.log("Formulaire soumis.");

            const formData = new FormData(this);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                });

                console.log("Réponse du serveur : ", response); // Afficher la réponse

                if (response.ok) {
                    const data = await response.json();
                    console.log("Données reçues : ", data);
                    window.location.href = 'login.php'; // Rediriger vers index.php
                } else {
                    const errorText = await response.text();
                    alert("Erreur : " + errorText); // Affiche l'erreur
                }
            } catch (error) {
                alert("Une erreur est survenue. Veuillez réessayer.");
            } finally {
                submitInProgress = false; // Réinitialiser l'indicateur
            }
        };
    } else {
        console.error("Formulaire non trouvé.");
    }
});