<?php 
// Check if a session is not already started and start it if not
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
include "lib/redirect.php"; // Redirect functions
include "lib/connection.php"; // Database connection
include "lib/csrf.php"; // Cross-Site Request Forgery protection

// Check if the user is not privileged (not logged in or not authorized)
if (1 > $_SESSION['role']??-1) { // if not privileged, redirect to homepage
    redirect();
}

// Check if the form has been submitted
if (filter_has_var(INPUT_POST, 'submit')) {
    // Initialize variables with default values
    $alertLevel = $alertLevel??0;
    $content = null;
    $file_extension = null;
    $mime_type = null;

    // Verify CSRF token for protection against cross-site request forgery attacks
    if (!csrf_token($_POST['CSRF'])) {
        redirect(); // If CSRF token is incorrect, redirect to homepage
    }

    // Trim and get start and end dates from the submitted form
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);

    // Check if start and end dates are empty
    if (empty($start_date) || empty($end_date)) {
        $alertMsg = 'Date cannot be blank'; // Set alert message
        $alertLevel = 2; // Set alert level to 2 (error)
    }

    // Check if a file is submitted
    if (!empty($_FILES['attachment']['tmp_name'])) {
        // If file size is less than 10 MB, process the file
        if ($_FILES['attachment']['size'] < 10000000) { // 10 MB (in bytes)
            $content = fopen($_FILES['attachment']['tmp_name'], 'rb'); // Open and read the file as binary
            $file_name = $_FILES['attachment']['name']; // Get the filename
            $mime_type = $_FILES['attachment']['type']; // Get the MIME type of the file
        } else {
            $alertMsg = 'File too large, must be below 10mb'; // Set alert message
            $alertLevel = 2; // Set alert level to 2 (error)
        }
    } else {
        $alertMsg = 'Must include supporting documents'; // Set alert message
        $alertLevel = 2; // Set alert level to 2 (error)
    }

    // If there are no errors (alertLevel < 2), insert the data into the database
    if ($alertLevel < 2) {
        // Prepare and execute an SQL statement to insert the data into the "loa" table
        $stmt = $conn->prepare("INSERT INTO loa (start_date, end_date, attachment, filename, mime_type, apply_id)
                               VALUES (:start_date, :end_date, :attachment, :filename, :mime_type, :apply_id)");
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':attachment', $content, PDO::PARAM_LOB); // Bind the file content as a large object (LOB)
        $stmt->bindParam(':filename', $file_name);
        $stmt->bindParam(':mime_type', $mime_type);
        $stmt->bindParam(':apply_id', $id); // Assuming $id is defined somewhere else
        $stmt->execute(); // Execute the SQL statement to insert the data into the database
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply LOA</title>
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
        <span class="hide--text">Apply<br/>Leave Of Absence</span>
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
            <h2>Apply for Leave Of Absence</h2>
            <form method="POST" action="" enctype="multipart/form-data" >
                <br/>
                <h4>Start Date:</h4>
                <input type="date" min="<?php echo date('Y-m-d'); ?>" name="start_date" value="<?php echo ($_POST['date']??"")?>"><br/>
                <h4>End Date:<h4>
                <input type="date"min="<?php echo date('Y-m-d'); ?>" name="end_date" value="<?php echo ($_POST['date']??"")?>"><br/>
                <input type="hidden" id="CSRF" name="CSRF" value="<?php echo $_SESSION['csrf_token'];?>"/>
                <h4>Submit Supporting documents:</h4>
                <input type="file" id="attachment" name="attachment" accept="image/png, image/jpeg" required>
                <input type="submit" value="Apply" name="submit">
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