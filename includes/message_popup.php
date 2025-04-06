<?php

function displayMessagePopup() {
    if (isset($_SESSION['message'])) {
        $messageType = $_SESSION['message_type'] ?? 'success'; 
        $backgroundColor = $messageType === 'error' ? '#EF4444' : '#4CAF50';
        echo '<div class="popup-message" style="background-color: ' . $backgroundColor . '; position: fixed; bottom: 20px; right: 20px; padding: 15px; border-radius: 5px; color: white; z-index: 1000;">';
        echo htmlspecialchars($_SESSION['message']);
        echo '<a href="#" class="close-popup" onclick="closePopup()" style="margin-left: 10px; color: white; text-decoration: none; font-weight: bold;">×</a>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}
?>
<script>
function closePopup() {
    document.querySelector('.popup-message').style.display = 'none';
}
</script>
