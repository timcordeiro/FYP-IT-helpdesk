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

// Get the search description from the URL query parameter "search"
$description = $_GET['search'] ?? '%'; //not reflected nor stored
if (empty($description)) {
    $description = '%';
} else if ($description != '%') {
    $description = '%' . $description . '%';
}

if ($_SESSION['role'] == 1) {
    // Prepare and execute a query to get tickets based on the description
    $stmt = $conn->prepare("SELECT t.id, t.subject, t.type, t.description, t.timestamp, t.reply,
            u1.student_id AS student_id,
            u2.name AS assignee_name,
            t.assign_id AS assignee_id,
            IF(t.reply IS NULL, 1, 0) AS has_reply
            FROM tickets t
            INNER JOIN users u1 ON t.user_id = u1.id
            LEFT JOIN users u2 ON t.assign_id = u2.id
            WHERE t.description LIKE :description AND t.assign_id LIKE :user_id
            ORDER BY 
                CASE
                    WHEN t.reply IS NOT NULL THEN t.timestamp  -- Order rows without replies by timestamp
                    ELSE 1  -- All rows with replies will be grouped below
                END, 
            t.timestamp;  -- Within each group, order by timestamp
    ");
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':user_id', $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sqlclose($conn);
} else if (($_SESSION['role'] == 2)) {
        // Prepare and execute a query to get tickets based on the description
    $stmt = $conn->prepare("SELECT t.id, t.subject, t.type, t.description, t.timestamp, t.reply,
            u1.student_id AS student_id,
            u2.name AS assignee_name,
            t.assign_id AS assignee_id,
            IF(t.reply IS NULL, 1, 0) AS has_reply
            FROM tickets t
            INNER JOIN users u1 ON t.user_id = u1.id
            LEFT JOIN users u2 ON t.assign_id = u2.id
            WHERE t.description LIKE :description
            ORDER BY 
                CASE
                    WHEN t.reply IS NOT NULL THEN t.timestamp  -- Order rows without replies by timestamp
                    ELSE 1  -- All rows with replies will be grouped below
                END, 
            t.timestamp;  -- Within each group, order by timestamp
    ");
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sqlclose($conn);
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tickets</title>
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
                <span class="hide--text">View<br />Tickets</span>
            </h3>
            <p>
                <span class="hide--text">
                </span>
            </p>
        </div>
        <div class="table-users" style="width:1200px"> <!-- more things, need to be longer -->
            <div class="header">Tickets</div>
            <div class="table">
                <?php if ($stmt->rowCount() === 0) { ?>
                    <div style="text-align:center;">You have no Tickets</div>
                <?php } else { ?>
                    <table cellspacing="0">
                        <tr>
                            <th>Time</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Attachment</th>
                            <th width="210">Description</th>
                            <th width="230">Reply</th>
                            <th>Assigned</th>
                            <th>Edit</th>
                        </tr>
                        <?php foreach ($result as $row) {
                            $subject = $row['subject'];
                            $type = $row['type'];
                            $description = $row['description'];
                            $reply = $row['reply'];
                            $timestamp = $row['timestamp'];
                            $assignee_name = $row['assignee_name'];
                            $student_id = $row['student_id'];
                            ?>
                            <tr>
                                <td>
                                    <?php echo $timestamp ?>
                                </td>
                                <td>
                                    <?php echo $student_id ?>
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
                                <td style="<?php echo $assignee_name ? "" : "color:Red" ?>"><?php echo $assignee_name ?? "No Assignment" ?></td>
                                <td>
                                    <form action="reply.php" method="post">
                                        <input type="hidden" id="CSRF" name="CSRF"
                                            value="<?php echo $_SESSION['csrf_token']; ?>" />
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                        <input type="submit" value="✏️" name="more">
                                    </form>
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