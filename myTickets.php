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

// Check if the 'search' parameter is present in the URL (GET request)
if (filter_has_var(INPUT_GET, 'search')) {
    // Check the CSRF token to protect against CSRF attacks
    if (!csrf_token($_POST['CSRF'])) {
        redirect(); // Redirect the user to the homepage if CSRF token is incorrect
    }
}

// Prepare and execute a SQL query to select tickets for the current user
$stmt = $conn->prepare("SELECT id, subject, type, description, reply, attachment, timestamp, user_id, assign_id FROM tickets WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->execute();

// Fetch all rows as an associative array
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

sqlclose($conn); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link rel="stylesheet" href="styles/navbar.css">
    <link rel="stylesheet" href="styles/home.css">
    <link rel="stylesheet" href="styles/table.css">

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
                <span class="hide--text">My<br />Tickets</span>
            </h3>
            <p>
                <span class="hide--text">
                </span>
            </p>
        </div>
        <div class="table-users">
            <div class="header">Tickets</div>
            <div class="table">
                <?php if ($stmt->rowCount() === 0) { ?>
                    <div style="text-align:center;">You have no Tickets</div>
                <?php } else { ?>
                    <table cellspacing="0">
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>status</th>
                            <th>attachment</th>
                            <th width="210">Description</th>
                            <th width="230">Reply</th>
                        </tr>
                        <?php foreach ($result as $row) {
                            $subject = $row['subject'];
                            $type = $row['type'];
                            $description = $row['description'];
                            $attachment = $row['attachment'];
                            $reply = $row['reply'];
                            $timestamp = $row['timestamp'];
                            $user_id = $row['user_id'];
                            $assign_id = $row['assign_id'];
                            ?>
                            <tr>
                                <td>
                                    <?php echo $timestamp ?>
                                </td>
                                <td>
                                    <?php echo $type ?>
                                </td>
                                <td>
                                    <?php echo $subject ?>
                                </td>
                                <td style="color:<?php echo ((bool) $reply ? "green" : "red") ?>"><?php echo ((bool) $reply ? "Resolved" : "Pending") ?></td>
                                <td>
                                    <form action="download.php" method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                        <input type="hidden" name="table" value="tickets" />
                                        <input type="submit" value="Attachment" name="attachment">
                                    </form>
                                </td>
                                <td>
                                    <?php echo $description ?>
                                </td>
                                <td>
                                    <?php echo $reply ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </div>
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