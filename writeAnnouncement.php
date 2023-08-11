<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the file with the redirect function
include "lib/redirect.php";

// Include the files with database connection and CSRF token functions
include "lib/connection.php";
include "lib/csrf.php";

// Check if the user is not privileged (level 1 or higher)
if (1 > $_SESSION['role'] ?? -1) { // Must be level 1
    redirect(); // Redirect the user to the homepage if not privileged
}

// Check if the "submit" button is clicked to publish the announcement
if (filter_has_var(INPUT_POST, 'submit')){
    $alertLevel = $alertLevel ?? 0;
    if (!csrf_token($_POST['CSRF'])){
        redirect(); // Redirect if CSRF token is incorrect
    }
    // Sanitize and validate the subject and description of the announcement
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_SPECIAL_CHARS);
    $desc = filter_var($_POST['desc'], FILTER_SANITIZE_SPECIAL_CHARS);
    if (empty($subject) || empty($desc)){
        $alertMsg = 'Fields cannot be blank';
        $alertLevel = 2;
    }
    if (strlen($subject) > 50){
        $alertMsg = 'Subject must be below 50 characters';
        $alertLevel = 2;
    }
    if ($alertLevel < 2){
        // If all checks passed, insert the announcement into the database
        $stmt = $conn->prepare("INSERT INTO announcement (subject, description, author_id) VALUES (:subject, :description, :author_id)");
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':description', $desc);
        $stmt->bindParam(':author_id', $_SESSION['id']);
        $stmt->execute();
        $alertMsg = 'Announcement Published successfully';
        $alertLevel = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Announcement</title>
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
              <?php if (($_SESSION['role']??-1)>=2) { ?>
                <li><a href="viewLOA.php">VIEW LOA</a></li>
              <?php } ?>
              <?php if (($_SESSION['role']??-1) >= 1) { ?>
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
        <span class="hide--text">Write<br/>Announcement</span>
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
            <h2>Write a Announcement</h2>
            <form method="POST" action="">
              <input type="text" name="subject" placeholder="Subject">
              <textarea name="desc" placeholder="Description"></textarea>
              <input type="hidden" id="CSRF" name="CSRF" value="<?php echo $_SESSION['csrf_token'];?>"/>
              <input type="submit" value="Publish" name="submit">
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