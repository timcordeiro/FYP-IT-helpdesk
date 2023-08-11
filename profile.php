<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include the file with the redirect function
include "lib/redirect.php";

// Include the files with database connection and CSRF token functions
include "lib/connection.php";
include "lib/csrf.php";

// Check if a user is not logged in (no 'id' in session)
if (!isset($_SESSION['id'])) {
  redirect(); // Redirect the user to the homepage (login page) if not logged in
}

// Check if a form has been submitted
if (filter_has_var(INPUT_POST, 'submit')) {
  $alertLevel = $alertLevel ?? 0;
  
  // Check the CSRF token to protect against CSRF attacks
  if (!csrf_token($_POST['CSRF'])) {
    redirect(); // Redirect the user to the homepage if CSRF token is incorrect
  }

  // If the form submission is for changing the password
  if ($_POST['submit'] == 'Change Password') {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if the new passwords match
    if ($_POST['password'] != $_POST['repeat_pass']) {
      $alertMsg = 'Passwords do not match.';
      $alertLevel = 2;
    }
    
    // Validate the new password
    if (!validatePassword($_POST['password'])) {
      $alertMsg = "Password does not meet the minimum requirements: At least 1 Capital Letter, 1 Numerical Number, 1 Special Character";
      $alertLevel = 2;
    }
    
    // If there are no validation errors, update the password in the database
    if ($alertLevel < 2) {
      // Verify the old password before updating the new one
      if ($login($_SESSION['email'], $_POST['old_password'])) {
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id=:id");
        $stmt->bindParam(':id', $_SESSION['id']);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $alertMsg = "Password Changed";
        $alertLevel = 1;
      } else {
        $alertMsg = "Current Password Incorrect";
        $alertLevel = 2;
      }
    }
  }
  
  // If the form submission is for updating profile information
  if ($_POST['submit'] == 'Update') {
    $name = ucwords(filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    // Check for empty fields
    if (empty($email) || empty($name)) {
      $alertMsg = "Fields cannot be blank";
      $alertLevel = 2;
    }

    // Check if the email is available (not already used by another user)
    if ($_SESSION['email'] != $email) {
      if ($checkUserAvailabilty($email, $_SESSION['student_id'])) {
        $alertMsg = 'That email is currently not available.';
        $alertLevel = 2;
      }
    }
    
    // If there are no validation errors, update the profile information in the database
    if ($alertLevel < 2) {
      $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email WHERE id=:id");
      $stmt->bindParam(':id', $_SESSION['id']);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':email', $email);
      $stmt->execute();
      
      // Update the profile information in the session variables as well
      $_SESSION = array_replace(
        $_SESSION,
        array(
          "name" => $name,
          "email" => $email,
        )
      );
      
      $alertMsg = "Account details updated";
      $alertLevel = 1;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link rel="stylesheet" href="styles/navbar.css">
  <link rel="stylesheet" href="styles/home.css">
  <link rel="icon" href="images/favicon.ico">
</head>

<body class="no-animation">
  <div class="home-wrapper">
    <?php include 'lib/alerts.php' ?>

    <!-- NAVBAR 
  =============================== -->
    <div class="navbar">
      <div class="menu">
        <div id="header">
          <div id="header-content">
            <div id="nav-menu">
              <div class="icon-bar"></div>
              <div class="icon-bar"></div>
              <div class="icon-bar"></div>
            </div>

            <p id="helpdesk-title">
              <span>RP HELPDESK</span>
            </p>

            <ul id="nav-links">
              <li><a href="homepage.php">HOME</a></li>
              <?php if (($_SESSION['role'] ?? -1) >= 2) { ?>
                <li><a href="viewLOA.php">VIEW LOA</a></li>
              <?php } ?>
              <?php if (($_SESSION['role'] ?? -1) >= 1) { ?>
                <li><a href="writeAnnouncement.php">WRITE ANNOUNCEMENTS</a></li>
                <li><a href="applyLOA.php">LOA</a></li>
                <li><a href="viewTickets.php">VIEW TICKETS</a></li>
              <?php } else { ?>
                <li><a href="myTickets.php">MY TICKETS</a></li>
                <li><a href="submitTicket.php">SUBMIT TICKET</a></li>
              <?php } ?>
              <li><a href="profile.php">PROFILE</a></li>
              <li><a href="lib/logout.php">LOGOUT</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="lang">Eng</div>
    </div>
    <div class="text">
      <h1><span class="hide--text" style="letter-spacing:-15px">Republic</span></h1>
      <h2 style="letter-spacing:-5px">Polytechnic</h2>
      <h3>
        <span class="hide--text">Profile &<br />Credentials</span>
      </h3>
      <p>
        <span class="hide--text">
        </span>
      </p>
    </div>
    <section class="loginWrapper">
      <ul class="form-tabs">
        <li class="active">Profile</li>
        <li>Password</li>
      </ul>

      <ul class="form-tab__content">

        <li class="active">
          <div class="content__wrapper">
            <form method="POST" action="">
              <input type="email" name="email" placeholder="email" value="<?php echo $_SESSION['email']; ?>">
              <input type="text" name="name" placeholder="name" value="<?php echo $_SESSION['name']; ?>">
              <input type="hidden" name="CSRF" value="<?php echo $_SESSION['csrf_token']; ?>" />
              <input type="submit" value="Update" name="submit">

            </form>
          </div>
        </li>

        <li>
          <div class="content__wrapper">
            <form method="POST" action="">
              <input type="password" name="old_password" placeholder="Current Password">
              <input type="password" name="password" placeholder="New Pasword">
              <input type="password" name="repeat_pass" placeholder="Repeat Password">
              <input type="hidden" name="CSRF" value="<?php echo $_SESSION['csrf_token']; ?>" />
              <input type="submit" value="Change Password" name="submit">

            </form>
          </div>
        </li>

      </ul>

    </section>
    <div class="school">
      <p>Republic Polytechnic</p>
    </div>

  </div>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/3.2.6/gsap.min.js'></script>
  <script src='https://unpkg.com/ionicons@5.0.0/dist/ionicons.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js'></script>
  <script src="styles/home.js"></script>
  <script src="styles/navbar.js"></script>

</body>

</html>