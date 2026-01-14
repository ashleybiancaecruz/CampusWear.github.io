(function () {
    'use strict';

    const API_URL = 'api/shared/feedback.php';

    async function shareFeedback(data) {
        const userField = document.getElementById('current_user_id');
        const actualUserId = userField ? userField.value : 0;

        const formData = new FormData();
        formData.append('user_id', actualUserId);
        formData.append('name', data.name);
        formData.append('email', data.email);
        formData.append('mobile_number', data.mobile_number);
        formData.append('body', data.body);
        formData.append('category_name', data.category_name);

        try {
            const response = await fetch(`${API_URL}?action=submit`, {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            try {
                const result = JSON.parse(text);
                if (result.status === 'success') {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Server Error: ' + result.message);
                }
                return result;
            } catch (e) {
                console.error('PHP Error detected:', text);
                alert('An internal server error occurred. Check the Network tab.');
            }
        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    window.FeedbackTracker = { shareFeedback };
})();