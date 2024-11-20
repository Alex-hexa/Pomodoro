let totalSeconds;
let interval;
let isPaused = false;

document.getElementById('startTimer').onclick = async function () {
    const hours = parseInt(document.getElementById('hours').value) || 0;
    const minutes = parseInt(document.getElementById('minutes').value) || 0;
    const seconds = parseInt(document.getElementById('seconds').value) || 0;

    totalSeconds = (hours * 3600) + (minutes * 60) + seconds;

    // Disable the start button and enable pause/reset buttons
    document.getElementById('startTimer').disabled = true;
    document.getElementById('pauseTimer').disabled = false;
    document.getElementById('resetTimer').disabled = false;

    // Log the timer duration in the database
    await logTimerDuration(totalSeconds);

    updateCountdown();
};

async function logTimerDuration(duration) {
    // Retrieve the token from sessionStorage
    const token = sessionStorage.getItem('jwtToken');

    console.log("Logging timer duration. Token:", token);

    if (!token) {
        console.error("No token found in session storage.");
        return; // Exit if there's no token
    }

    const userId = JSON.parse(atob(token.split('.')[1])).userId; // Decode the token to get the user ID
    console.log("User ID:", userId);

    // Sending the request to log the timer duration
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
    clearInterval(interval); // Clear the interval
    totalSeconds = 0; // Reset the total seconds
    document.getElementById('countdown').innerHTML = "0h 0m 0s"; // Reset display
    document.getElementById('startTimer').disabled = false; // Enable start button
    document.getElementById('pauseTimer').disabled = true; // Disable pause button
    document.getElementById('resetTimer').disabled = true; // Disable reset button
};

function updateCountdown() {
    if (totalSeconds <= 0) {
        document.getElementById('countdown').innerHTML = "Temps écoulé!";
        confetti(); // Animation de confettis
        clearInterval(interval);
        return;
    }

    interval = setInterval(() => {
        if (isPaused) return; // If paused, skip decrementing

        const hrs = Math.floor(totalSeconds / 3600);
        const mins = Math.floor((totalSeconds % 3600) / 60);
        const secs = totalSeconds % 60;

        document.getElementById('countdown').innerHTML = `${hrs}h ${mins}m ${secs}s`;
        totalSeconds--;

        if (totalSeconds < 0) {
            clearInterval(interval);
            document.getElementById('countdown').innerHTML = "Temps écoulé !";
            confetti(); // Animation de confettis
        }
    }, 1000);
}

// Pause and resume functionality
document.getElementById('pauseTimer').onclick = function () {
    if (isPaused) {
        isPaused = false; // Resume the timer
        this.innerText = "Pause"; // Change button text to "Pause"
        updateCountdown(); // Start counting down again
    } else {
        isPaused = true; // Pause the timer
        clearInterval(interval); // Clear the interval
        this.innerText = "Reprendre"; // Change button text to "Reprendre"
    }
};

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
            alert(data.message);
            // Refresh the timer list after successful deletion
            await refreshTimerList();
        } else {
            alert(data.message);
        }
    }
}

// Function to refresh the timer list
async function refreshTimerList() {
    const response = await fetch('getTimers.php'); // Fetch the updated timer list
    const timersHtml = await response.text(); // Get the HTML response
    const timerList = document.getElementById('timerList');
    timerList.innerHTML = timersHtml; // Update the timer list with new content
}