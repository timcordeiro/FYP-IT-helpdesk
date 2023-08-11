<?php
// Check if a session is not already started and start it if not
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Include necessary files
include "lib/redirect.php"; // Redirect functions
include "lib/connection.php"; // Database connection
include "lib/csrf.php"; // Cross-Site Request Forgery protection

// Check if the user is not logged in, and redirect to homepage if not logged in
if (!isset($_SESSION['id'])) {
  redirect();
}

// Fetch frequently asked questions (FAQs) from the database
$stmt = $conn->prepare("SELECT question, answer FROM faq");
$stmt->execute();
$faq = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT a.id, a.timestamp, a.subject, a.description, u.name AS author
                       FROM announcement a
                       JOIN users u ON a.author_id = u.id
                       ORDER BY a.timestamp DESC");
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Close the database connection
sqlclose($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link rel="stylesheet" href="styles/navbar.css">
  <link rel="stylesheet" href="styles/home.css">
  <link rel="stylesheet" href="styles/faq.css">
  <link rel="stylesheet" href="styles/table.css">
  <link rel="icon" href="images/favicon.ico">
</head>

<body class="no-animation">
  <div class="home-wrapper" style="overflow-y: visible">

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
        <span class="hide--text">IT HELPDESK<br />SYSTEM</span>
      </h3>
      <p>
        <span class="hide--text">
        </span>
      </p>
    </div>
    <div class="table-users">
      <div class="header">announcement</div>
      <div class="table">
        <?php if ($stmt->rowCount() === 0) { ?>
          <div style="text-align:center;">There are no Announcement</div>
        <?php } else { ?>
          <table cellspacing="0">
            <tr>
              <th width="70">Time</th>
              <th width="70">Subject</th>
              <th width="230">Description</th>
            </tr>
            <?php foreach ($announcements as $row) {
              $timestamp = $row['timestamp'];
              $subject = $row['subject'];
              $description = $row['description'];
              $author = $row['author'];
              ?>
              <tr>
                <td>
                  <?php echo $timestamp ?>
                </td>
                <td>
                  <?php echo $subject ?>
                </td>
                <td>
                  <?php echo $description ?><br /><br />
                  Author:
                  <?php echo $author ?>
                </td>
              </tr>
            <?php } ?>
          </table>
        <?php } ?>
      </div>
    </div>
    <div class="FAQ">
      <h2>Frequently Asked Questions</h2>
      <div style="visibility: hidden; position: absolute; width: 0px; height: 0px;">
        <svg xmlns="http://www.w3.org/2000/svg">
          <symbol viewBox="0 0 24 24" id="expand-more">
            <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z" />
            <path d="M0 0h24v24H0z" fill="none" />
          </symbol>
          <symbol viewBox="0 0 24 24" id="close">
            <path
              d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
            <path d="M0 0h24v24H0z" fill="none" />
          </symbol>
        </svg>
      </div>
      <?php foreach ($faq as $key => $row) { ?>
        <details <?php if ($key === array_key_first($faq)) {
          echo "open";
        } ?>>
          <summary>
            <?php echo $row['question'] ?>
            <svg class="control-icon control-icon-expand" width="24" height="24" role="presentation">
              <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" />
            </svg>
            <svg class="control-icon control-icon-close" width="24" height="24" role="presentation">
              <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" />
            </svg>
          </summary>
          <p>
            <?php echo $row['answer'] ?>
          </p>
        </details>
      <?php } ?>
    </div>
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