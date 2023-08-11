<?php
// Include the file with the redirect function
include "lib/redirect.php";

// Include the files with database connection and CSRF token functions
include "lib/connection.php";
include "lib/csrf.php";

// Check if a session is not already started and start it if not
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if the user is already logged in (role set in session)
if (isset($_SESSION['role'])) {
  // Redirect the user to the appropriate page based on their role and terminate the script
  redirect();
}

// Check if the login/register form is submitted
if (filter_has_var(INPUT_POST, 'submit')) {
  $alertLevel = $alertLevel ?? 0; // Initialize the alert level (0 means no alert)
  $submitted = true; // Flag to indicate that the form is submitted

  // Handle registration form submission
  if ($_POST['submit'] == 'register') {
    // Sanitize and validate input data for registration
    $student_id = filter_var($_POST['student_id'], FILTER_SANITIZE_SPECIAL_CHARS);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password (SHA-256)
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    // Check if the passwords match
    if ($_POST['password'] != $_POST['repeat_pass']) {
      $alertMsg = "Passwords do not match";
      $alertLevel = 2; // 2 means an error alert
    }

    // Validate the password format
    if (!validatePassword($_POST['password'])) { // The order matters for the password validation
      $alertMsg = "Password does not meet the minimum requirements: At least 1 Capital Letter, 1 Numerical Number, 1 Special Character";
      $alertLevel = 2; // 2 means an error alert
    }

    // Check if the email is empty
    if (empty($email)) {
      $alertMsg = "Email cannot be empty";
      $alertLevel = 2; // 2 means an error alert
    }

    // Check if the student ID is empty
    if (empty($student_id)) {
      $alertMsg = "Name cannot be blank";
      $alertLevel = 2; // 2 means an error alert
    }

    // Check if the user account already exists
    if ($checkUserAvailabilty($email, $student_id)) {
      $alertLevel = 2; // 2 means an error alert
      $alertMsg = "Account has already been created!";
    }

    // If no error alerts, proceed with registration
    if ($alertLevel < 2) {
      $name = $_POST['name'];
      if ($register($email, $password, $student_id, $name)) { // Call the registration function and check if it was successful
        $alertMsg = "Account Created";
        $alertLevel = 1; // 1 means a success alert
      }
    }
  }

  // Handle login form submission
  if ($_POST['submit'] == 'Login') {
    // Sanitize input data for login
    $username = filter_var($_POST['email'], FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password']; // Password is not sanitized as it is not stored or reflected

    // Check the user's credentials using the login function
    $user_arr = $login($username, $password);

    if ($user_arr) { // If the login is successful (user exists and password matches)
      $_SESSION = array_merge($_SESSION, $user_arr); // Merge the user information with the session data
      csrf_token(null); // Regenerate the CSRF token to protect against CSRF attacks (replacing the old token)
      redirect(); // Redirect the user to the appropriate page based on their role
    } else { // If login fails (wrong credentials)
      $alertLevel = 2; // 2 means an error alert
      $alertMsg = 'Username or Password is incorrect';
    }
  }
}

sqlclose($conn); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/home.css">
  <link rel="stylesheet" href="styles/navbar.css">
  <link rel="stylesheet" href="styles/cursor.css">
  <link rel="icon" href="images/favicon.ico">
</head>

<body <?php echo ($submitted ?? false) ? 'class="no-animation"' : ''; ?>>
  <div class="home-wrapper">
    <?php include 'lib/alerts.php' ?>
    <!-- OVERLAY
  =============================== -->
    <div class="overlay first"></div>
    <div class="overlay second"></div>
    <div class="overlay third"></div>

  <!-- NAVBAR 
  =============================== -->
    <div class="navbar">
      <div class="menu">
        <!-- <ion-icon name="menu-outline"></ion-icon> -->
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
              <li><a href="#">HOME</a></li>
              <!-- <li><a href="#">FAQ</a></li> -->
            </ul>
          </div>
        </div>
      </div>
      <div class="lang">Eng</div>
      <!-- <div class="search">
        <ion-icon name="search-outline"></ion-icon>
      </div> -->
    </div>

    <!-- TEXT 
  =============================== -->
    <div class="text">
      <h1><span class="hide--text">Republic</span></h1>
      <h2>Polytechnic</h2>
      <h3>
        <span class="hide--text">Login<br />Register</span>
      </h3>
      <p>
        <span class="hide--text">
          <!-- Dummy Text -->
        </span>
      </p>
    </div>
    <!-- FORM
  =========================== -->
    <section class="loginWrapper">
      <ul class="form-tabs">
        <li class="active">Login</li>
        <li>Register</li>
      </ul>

      <ul class="form-tab__content">

        <li class="active">
          <div class="content__wrapper">
            <form method="POST" action="">
              <input type="email" name="email" placeholder="email">
              <input type="password" name="password" placeholder="Password">
              <input type="submit" value="Login" name="submit">
            </form>
          </div>
        </li>

        <li>
          <div class="content__wrapper">
            <form method="POST" action="">
              <input type="id" name="student_id" placeholder="Student ID">
              <input type="name" name="name" placeholder="Name">
              <input type="email" name="email" placeholder="Email">
              <input type="password" name="password" placeholder="Password">
              <input type="password" name="repeat_pass" placeholder="Repeat Password">
              <input type="submit" value="register" name="submit">
            </form>
          </div>
        </li>

      </ul>

    </section>
    <!-- school 
  =============================== -->
    <div class="school">
      <p>Republic Polytechnic</p>
    </div>
    <div class="custom-cursor"></div>
  </div>
  <!-- partial -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/3.2.6/gsap.min.js'></script>
  <script src='https://unpkg.com/ionicons@5.0.0/dist/ionicons.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js'></script>
  <script src="styles/home.js"></script>
  <script src="styles/navbar.js"></script>
  <script src="styles/cursor.js"></script>



</body>

</html>