(function () {
    'use strict';

    const APPLICANT_API = 'isc_application.php'; 

    function shareApplicationToISC(applicationData) {
        const btn = document.querySelector('button[type="submit"]');
        if(btn) btn.disabled = true;

        fetch(APPLICANT_API, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(applicationData)
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Server sent non-JSON response:", text);
                throw new Error("Server returned an invalid format. Check for stray 'echo' statements in PHP.");
            }
        })
        .then(data => {
            if (data.status === 'success') {
                alert('Success! ' + data.message);
                window.location.reload(); 
            } else {
                alert("Server says: " + data.message);
                if(btn) btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            alert('Error: ' + error.message);
            if(btn) btn.disabled = false;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('iscApplicationForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                shareApplicationToISC(Object.fromEntries(formData.entries()));
            });
        }
    });
})();