<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include the file with the redirect function
include "lib/redirect.php";

// Include the files with database connection and CSRF token functions
include "lib/connection.php";
include "lib/csrf.php";

// Check if the user is not logged in
if (!isset($_SESSION['id'])) {
  redirect(); // Redirect the user to the homepage if not logged in
}

// Check if the form has been submitted
if (filter_has_var(INPUT_POST, 'submit')) {
  $alertLevel = $alertLevel ?? 0;

  // Check the CSRF token to protect against CSRF attacks
  if (!csrf_token($_POST['CSRF'])) {
    redirect(); // Redirect the user to the homepage if CSRF token is incorrect
  }

  // Sanitize the input values from the form
  $type = filter_var($_POST['type'], FILTER_SANITIZE_SPECIAL_CHARS);
  $subject = filter_var($_POST['subject'], FILTER_SANITIZE_SPECIAL_CHARS);
  $desc = filter_var($_POST['desc'], FILTER_SANITIZE_SPECIAL_CHARS);
  $content = null;
  $file_extension = null;
  $mime_type = null;

  // Validate the input values
  if (empty($subject) || empty($desc) || empty($type)) {
    $alertMsg = 'Fields cannot be blank';
    $alertLevel = 2;
  }
  if (strlen($subject) > 50) {
    $alertMsg = 'Subject must be below 50 characters';
    $alertLevel = 2;
  }

  // Check if an attachment has been submitted
  if (!empty($_FILES['attachment']['tmp_name'])) {
    // If an attachment is submitted, check its size and set appropriate variables
    if ($_FILES['attachment']['size'] < 10000000) { // 10 MB
      $content = fopen($_FILES['attachment']['tmp_name'], 'rb');
      $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
      $mime_type = $_FILES['attachment']['type'];
    } else {
      $alertMsg = 'File too large, must be below 10MB';
      $alertLevel = 2;
    }
  }

  // If there are no validation errors, submit the ticket
  if ($alertLevel < 2) {
    $submitTicket($type, $subject, $desc, $content, $_SESSION['id'], $file_extension, $mime_type);
    $alertLevel = 1;
    $alertMsg = 'Ticket Submitted';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Ticket</title>
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
        <span class="hide--text">Submit<br />Tickets</span>
      </h3>
      <p>
        <span class="hide--text">
        </span>
      </p>
    </div>
    <section class="loginWrapper">
      <ul class="form-tab__content">
        <li class="active">
          <div class="content__wrapper">
            <h2>Submit a Ticket</h2>
            <form method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>" enctype="multipart/form-data"> 
              <!-- submitting a form to the same PHP script that the form is displayed on -->
              <input type="text" name="type" placeholder="Ticket Type">
              <input type="text" name="subject" placeholder="Subject">
              <textarea name="desc" placeholder="Description"></textarea>
              <input type="file" id="attachment" name="attachment" accept="image/png, image/jpeg">
              <input type="hidden" id="CSRF" name="CSRF" value="<?php echo $_SESSION['csrf_token']; ?>" />
              <input type="submit" value="Submit Ticket" name="submit">
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