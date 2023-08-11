<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the file with the redirect function
include "lib/redirect.php";

// Include the files with database connection and CSRF token functions
include "lib/connection.php";
include "lib/csrf.php";

// Check if the user is not privileged (level 2 or higher)
if (1 >= $_SESSION['role'] ?? -1) { // Must be level 2
    redirect(); // Redirect the user to the homepage if not privileged
}

// Set the timezone to Asia/Singapore
date_default_timezone_set('Asia/Singapore');

// Get the current date in Singapore
$currentDateInSingapore = date('Y-m-d');

// Prepare and execute a query to get LOAs
$stmt = $conn->prepare("SELECT *, (end_date < :current_date) AS is_date_passed FROM loa");
$stmt->bindParam(':current_date', $currentDateInSingapore);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Close the database connection
sqlclose($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View LOA</title>
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
                <span class="hide--text">View<br />Leave Of Absence</span>
            </h3>
            <p>
                <span class="hide--text">
                </span>
            </p>
        </div>
        <div class="table-users">
            <div class="header">LOA</div>
            <div class="table">
                <?php if ($stmt->rowCount() === 0) { ?>
                    <div style="text-align:center;">You have no Tickets</div>
                <?php } else { ?>
                    <table cellspacing="0">
                        <tr>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Documents</th>
                            <th>Status</th>
                        </tr>
                        <?php foreach ($result as $row) {
                            $start_date = htmlentities($row['start_date']);
                            $end_date = htmlentities($row['end_date']);
                            $status = $row['is_date_passed']
                                ?>
                            <tr>
                                <td>
                                    <?php echo $start_date ?>
                                </td>
                                <td>
                                    <?php echo $end_date ?>
                                </td>
                                <td>
                                    <form action="download.php" method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                        <input type="hidden" name="table" value="loa" />
                                        <input type="submit" value="Attachment" name="attachment" class="hide">
                                    </form>
                                </td>
                                <td style="color:<?php echo $status ? "green" : "red" ?>;text-align:center;"><?php echo $status ? "Ended" : "Ongoing" ?></td>
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