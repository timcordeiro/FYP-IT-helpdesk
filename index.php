<?php 
// Include the file with the redirect function
include "lib/redirect.php";

// Check if a session is not already started and start it if not
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if the user is not logged in (no role set in session)
if (!isset($_SESSION['role'])) {
  // Redirect the user to the login page
  redirect('login.php');
  die(); // Terminate the script to prevent further execution
}

// Check the user's role to determine the appropriate redirection
if ($_SESSION['role'] >= 1) { // If the role is greater than or equal to 1 (Staff or Admin)
    // Redirect the user to the "View Tickets" page
    redirect('viewTickets.php');
} else { // If the role is less than 1 (Student or unauthorized)
    // Redirect the user to the homepage
    redirect('homepage.php');
}

// Exit the script (this line may not be necessary as the redirect function should terminate the script)
exit;
?>
