// Configuration constants
const API_ENDPOINT = '';  // Empty string uses current page for API requests

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

// Function to convert local time to UTC
function convertLocalToUTC(localDateTimeString) {
    const localDate = new Date(localDateTimeString);
    return localDate.toISOString();
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
});

document.getElementById(SELECTORS.PASSWORD_FORM)?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Convert local time to UTC and send to server
    const localDateTime = document.getElementById(SELECTORS.DATETIME_INPUT).value;
    const utcDateTime = convertLocalToUTC(localDateTime);
    
    const formData = new FormData();
    formData.append('action', 'generate');
    formData.append('datetime', utcDateTime);
    
    try {
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const resultDiv = document.getElementById(SELECTORS.RESULT_DIV);
        
        if (data.error) {
            resultDiv.className = 'error';
            resultDiv.innerHTML = data.error;
        } else {
            // Convert UTC time to local time for display
            const localUnlockTime = convertUTCToLocal(data.unlock_time);
            
            resultDiv.className = 'success';
            resultDiv.innerHTML = `
                <strong>Generated Password:</strong><br>
                <div class="url-box">${data.password}</div>
                <button class="copy-btn" onclick="copyToClipboard('${data.password}')">Copy Password</button>
                
                <div style="margin-top: 30px;">
                <strong>Decrypt URL:</strong><br>
                <div class="url-box">
                    <a href="${data.decrypt_url}" class="decrypt-link" target="_blank">${data.decrypt_url}</a>
                </div>
                <button class="copy-btn" onclick="copyToClipboard('${data.decrypt_url}')">Copy URL</button>
                </div>
                
                <small>Unlock time: ${localUnlockTime}</small>
            `;
        }
        
        resultDiv.style.display = 'block';
    } catch (error) {
        const resultDiv = document.getElementById(SELECTORS.RESULT_DIV);
        resultDiv.className = 'error';
        resultDiv.innerHTML = 'An error occurred';
        resultDiv.style.display = 'block';
    }
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard');
    });
}