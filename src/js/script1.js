document.addEventListener('DOMContentLoaded', function() {
    // Initialize star ratings
    const ratingElements = document.querySelectorAll('.stars');
    ratingElements.forEach(element => {
        const rating = parseFloat(element.dataset.rating);
        const starsHtml = Array.from({ length: 5 }, (_, index) => {
            const starClass = index < Math.floor(rating) ? 'filled' : '';
            return `
                <svg class="star ${starClass}" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="${starClass ? '#FCD34D' : 'none'}" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            `;
        }).join('');
        element.innerHTML = starsHtml;
    });

    // Tab switching functionality
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    // Update notification close functionality
    const updateBtn = document.querySelector('.update-notification .btn-primary');
    if (updateBtn) {
        updateBtn.addEventListener('click', () => {
            const notification = document.querySelector('.update-notification');
            notification.style.display = 'none';
        });
    }

    // Notification button functionality
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            alert('Notifications feature coming soon!');
        });
    }

    // Edit and Delete button functionality in admin view
    const editButtons = document.querySelectorAll('.btn-edit');
    const deleteButtons = document.querySelectorAll('.btn-delete');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.closest('tr').querySelector('td:first-child').textContent;
            alert(`Edit user with ID: ${userId}`);
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.closest('tr').querySelector('td:first-child').textContent;
            if (confirm(`Are you sure you want to delete user with ID: ${userId}?`)) {
                alert(`User with ID: ${userId} deleted`);
                // Here you would typically make an API call to delete the user
                // and then remove the row from the table
            }
        });
    });

    // User profile modal functionality
    const profileMenu = document.getElementById('profile-menu');
    const userprofile = document.getElementById('userprofile');

    document.addEventListener('click', function(event) {
        if (!profileMenu.contains(event.target) && !event.target.closest('.avatar')) {
            profileMenu.classList.remove('show');
        }
    });

    window.onclick = function(event) {
        if (event.target == userprofile) {
            userprofile.style.display = 'none';
        }
    };
});

function toggleProfileMenu() {
    const profileMenu = document.getElementById('profile-menu');
    profileMenu.classList.toggle('show');
}

function showUserProfile() {
    const modal = document.getElementById('userprofile');
    modal.style.display = 'block';
}

function closeUserProfile() {
    const modal = document.getElementById('userprofile');
    modal.style.display = 'none';
}



function closePopup() {
    const popupMessage = document.querySelector('.popup-message');
    if (popupMessage) {
        popupMessage.style.display = 'none';
    }
}


