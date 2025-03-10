document.addEventListener('DOMContentLoaded', function () {
    // Add event listeners for delete buttons
    document.querySelectorAll('.delete').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent form submission
            const interviewId = this.closest('form').querySelector('input[name="id"]').value;
            if (confirm('Are you sure you want to delete this interview?')) {
                fetch(`delete_interview.php?id=${interviewId}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Interview deleted successfully!');
                        window.location.reload(); // Refresh the page
                    } else {
                        alert('Error deleting interview.');
                    }
                });
            }
        });
    });
  

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const interviewsList = document.getElementById('interviewsList');
    
        if (searchInput && interviewsList) {
            // Add an event listener for the 'input' event
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value;
    
                // Fetch interviews based on the search term
                fetch(`index.php?search=${encodeURIComponent(searchTerm)}`, {
                    method: 'GET'
                })
                .then(response => response.text())
                .then(data => {
                    // Parse the response and update the interview list
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const newInterviewsList = doc.querySelector('.interviews-list').innerHTML;
                    interviewsList.innerHTML = newInterviewsList;
                })
                .catch(error => {
                    console.error('Error fetching interviews:', error);
                });
            });
        }
    });
    

    // Add event listeners for cancel buttons
    document.querySelectorAll('.cancel').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent form submission
            const interviewId = this.closest('form').querySelector('input[name="id"]').value;
            if (confirm('Are you sure you want to cancel this interview?')) {
                fetch(`cancel_interview.php?id=${interviewId}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Interview canceled successfully!');
                        window.location.reload(); // Refresh the page
                    } else {
                        alert('Error canceling interview.');
                    }
                });
            }
        });
    });



    // Function to update the interview list
    function updateInterviewList(interviews) {
        const interviewsList = document.querySelector('.interviews-list');
        if (!interviewsList) return;

        // Clear the current list
        interviewsList.innerHTML = '';

        // Add the new interviews
        interviews.forEach(interview => {
            const interviewCard = document.createElement('div');
            interviewCard.className = 'card';
            interviewCard.innerHTML = `
                <div class="card-header">
                    <span class="date">${interview.scheduled_time}</span>
                    <span class="day">${new Date(interview.scheduled_time).toLocaleDateString('en-US', { weekday: 'long' })}</span>
                    <span class="time">${new Date(interview.scheduled_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
                <div class="card-body">
                    <p><strong>Applicant:</strong> ${interview.applicant}</p>
                    <p><strong>Email:</strong> ${interview.applicant_email}</p>
                    <p><strong>Interviewer:</strong> ${interview.interviewer}</p>
                    <p><strong>Department:</strong> ${interview.interviewer_department}</p>
                </div>
                <div class="card-footer">
                    <a href="edit_interview.php?id=${interview.id}" class="button edit-btn">Edit</a>
                </div>
            `;
            interviewsList.appendChild(interviewCard);
        });
    }
});