
// DOM selectors
const SELECTORS = {
    DATETIME_INPUT: 'datetime',
    MESSAGE_DIV: 'messageDiv',
    UNLOCK_TIME_DISPLAY: 'unlockTimeDisplay',
    PASSWORD_FORM: 'passwordForm',
    RESULT_DIV: 'result'
};

// Function to convert UTC time to local time (format according to locale)
function convertUTCToLocal(utcDateTimeString) {
    const utcDate = new Date(utcDateTimeString);
    // Display date and time according to browser locale
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    };
    // In Japanese environment, format would be like "2025年8月19日 火曜日 21:57:00"
    return utcDate.toLocaleString(undefined, options);
}


// Set minimum value for datetime-local input field
document.addEventListener('DOMContentLoaded', function() {
    const datetimeInput = document.getElementById(SELECTORS.DATETIME_INPUT);
    if (datetimeInput) {
        // Get UTC time and convert to local time
        const utcNow = datetimeInput.getAttribute('data-utc-now');
        const now = new Date(utcNow);
        
        // Convert to datetime-local format (YYYY-MM-DDTHH:MM)
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        datetimeInput.setAttribute('min', localDateTime);
    }
    
    // Convert message time to local format
    const messageDiv = document.getElementById(SELECTORS.MESSAGE_DIV);
    if (messageDiv) {
        const unlockTime = messageDiv.getAttribute('data-unlock-time');
        if (unlockTime) {
            // Convert UTC time to local format
            const localUnlockTime = convertUTCToLocal(unlockTime);
            
            // For error messages, display unlock time
            const unlockTimeDisplay = document.getElementById(SELECTORS.UNLOCK_TIME_DISPLAY);
            if (unlockTimeDisplay) {
                unlockTimeDisplay.innerHTML = `Unlock time: <strong>${localUnlockTime}</strong>`;
            }
        }
    }
    
    // Display unlock time for generated password
    const unlockTimeLocal = document.getElementById('unlockTimeLocal');
    if (unlockTimeLocal) {
        const utcTime = unlockTimeLocal.getAttribute('data-utc-time');
        if (utcTime) {
            const localTime = convertUTCToLocal(utcTime);
            unlockTimeLocal.innerHTML = `Unlock time: ${localTime}`;
        }
    }
});

// Set timezone value before form submission
document.getElementById(SELECTORS.PASSWORD_FORM)?.addEventListener('submit', function(e) {
    // Set the user's timezone
    const timezoneInput = document.getElementById('timezone');
    if (timezoneInput) {
        timezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
    }
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard');
    });
}