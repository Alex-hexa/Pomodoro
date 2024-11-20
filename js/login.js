document.getElementById('loginForm').onsubmit = async function(event) {
    event.preventDefault(); // Empêcher le rechargement de la page

    const formData = new FormData(this);
    const response = await fetch('', { // Assurez-vous que le bon chemin est défini
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        const data = await response.json();
        sessionStorage.setItem('jwtToken', data.token); // Stocker le token dans sessionStorage
        window.location.href = 'index.php'; // Rediriger vers index.php
    } else {
        const errorText = await response.text();
        alert(errorText); // Afficher un message d'erreur
    }
};