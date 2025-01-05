let totalSeconds;
let interval;
let isPaused = false;

document.getElementById('startTimer').onclick = async function () {
    const hours = parseInt(document.getElementById('hours').value) || 0;
    const minutes = parseInt(document.getElementById('minutes').value) || 0;
    const seconds = parseInt(document.getElementById('seconds').value) || 0;

    totalSeconds = (hours * 3600) + (minutes * 60) + seconds;

    document.getElementById('startTimer').disabled = true;
    document.getElementById('pauseTimer').disabled = false;
    document.getElementById('resetTimer').disabled = false;

    await logTimerDuration(totalSeconds);

    updateCountdown();
};

async function logTimerDuration(duration) {
    const token = sessionStorage.getItem('jwtToken');

    console.log("Logging timer duration. Token:", token);

    if (!token) {
        console.error("No token found in session storage.");
        return;
    }

    const userId = JSON.parse(atob(token.split('.')[1])).userId;
    console.log("User ID:", userId);

    const response = await fetch('http://localhost:8080/logTimer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            userId: userId,
            duration: duration
        })
    });

    if (!response.ok) {
        const errorResponse = await response.text();
        console.error('Failed to log timer duration:', errorResponse);
    } else {
        console.log("Timer logged successfully.");
    }
}

document.getElementById('resetTimer').onclick = function () {
    clearInterval(interval);
    totalSeconds = 0;
    document.getElementById('countdown').innerHTML = "0h 0m 0s";
    document.getElementById('startTimer').disabled = false;
    document.getElementById('pauseTimer').disabled = true;
    document.getElementById('resetTimer').disabled = true;
};

function updateCountdown() {
    if (totalSeconds <= 0) {
        document.getElementById('countdown').innerHTML = "Temps écoulé!";
        confetti();
        clearInterval(interval);
        return;
    }

    interval = setInterval(() => {
        if (isPaused) return;

        const hrs = Math.floor(totalSeconds / 3600);
        const mins = Math.floor((totalSeconds % 3600) / 60);
        const secs = totalSeconds % 60;

        document.getElementById('countdown').innerHTML = `${hrs}h ${mins}m ${secs}s`;
        totalSeconds--;

        if (totalSeconds < 0) {
            clearInterval(interval);
            document.getElementById('countdown').innerHTML = "Temps écoulé !";
            confetti();
        }
    }, 1000);
}

// Fonctionnalité de pause et reprendre
document.getElementById('pauseTimer').onclick = function () {
    if (isPaused) {
        isPaused = false;
        this.innerText = "Pause";
        updateCountdown();
    } else {
        isPaused = true;
        clearInterval(interval);
        this.innerText = "Reprendre";
    }
};

function showBootstrapAlert(message, type = 'success') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.setAttribute('role', 'alert');
    alertContainer.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    document.querySelector('body').prepend(alertContainer);

    // ! Auto-fermeture de l'alerte après quelques secondes
    setTimeout(() => {
        (alertContainer).alert('close');
    }, 3000);
}

async function deleteTimer(uniq_id_timer) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce minuteur ?")) {
        const response = await fetch('deleteTimer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'uniq_id_timer=' + encodeURIComponent(uniq_id_timer)
        });

        const data = await response.json();
        if (data.status === 'success') {
            showBootstrapAlert(data.message, 'success');
            await refreshTimerList();
        } else {
            showBootstrapAlert(data.message, 'danger');
        }
    }
}

// Fonction qui actualise la liste des minuteurs
async function refreshTimerList() {
    const response = await fetch('getTimers.php');
    const timersHtml = await response.text(); 
    const timerList = document.getElementById('timerList');
    timerList.innerHTML = timersHtml;
}