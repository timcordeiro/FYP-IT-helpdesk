<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the file with the redirect function
include "lib/redirect.php";

// Include the files with database connection and CSRF token functions
include "lib/connection.php";
include "lib/csrf.php";

// Check if the user is not privileged (role less than 1)
if (1 > $_SESSION['role'] ?? -1) {
    redirect(); // Redirect the user to the homepage if not privileged
}

// Check the CSRF token to protect against CSRF attacks
if (!csrf_token($_POST['CSRF'])) {
    redirect(); // Redirect the user to the homepage if CSRF token is incorrect
}

// Get the ticket ID from the POST data
$id = filter_var($_POST['id'], FILTER_SANITIZE_SPECIAL_CHARS);
if (empty($id)) {
    redirect(); // Redirect the user to the homepage if the ID is empty
}

// Fetch the ticket details from the database
$stmt = $conn->prepare("SELECT t.id, t.subject, t.type, t.description, t.timestamp, t.reply,
    u1.student_id AS student_id,
    u2.name AS assignee_name,
    t.assign_id AS assignee_id
    FROM tickets t
    INNER JOIN users u1 ON t.user_id = u1.id
    LEFT JOIN users u2 ON t.assign_id = u2.id
    WHERE t.id = :id
");
$stmt->bindParam(':id', $id);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

// Extract ticket details from the fetched result
$subject = $result['subject'];
$type = $result['type'];
$description = $result['description'];
$timestamp = $result['timestamp'];
$assignee_name = $result['assignee_name'];
$student_id = $result['student_id'];
$reply = $result['reply'];

// Fetch the list of users with role greater than or equal to 1
$stmt = $conn->prepare("SELECT u.id, u.name FROM users u WHERE role >= 1;");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the form has been submitted
if (filter_has_var(INPUT_POST, 'submit')) {
    // If the form submission is for replying to the ticket
    if ($_POST['submit'] == 'Reply') {
        $newReply = filter_var($_POST['reply'], FILTER_SANITIZE_SPECIAL_CHARS);
        if ($newReply != $reply) {
            // Update the ticket's reply in the database
            $stmt = $conn->prepare("UPDATE tickets SET reply = :reply WHERE id = :id;");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':reply', $newReply);
            $stmt->execute();
            $reply = $newReply;
        }
        // If the form submission is for assigning the ticket
    } elseif ($_POST['submit'] == 'Assign') {
        // Check if the user is privileged (role is 2, usually representing an admin or supervisor)
        if ($_SESSION['role'] == 2) {
            $assignment = filter_var($_POST['__assignment'], FILTER_SANITIZE_SPECIAL_CHARS);
            if ($assignment == "null") {
                // If "None" is selected, remove the assignment by setting assign_id to NULL
                $stmt = $conn->prepare("UPDATE tickets SET assign_id = NULL WHERE id = :id");
            } else {
                // Update the ticket's assign_id to the selected user's ID
                $stmt = $conn->prepare("UPDATE tickets SET assign_id = :user_id WHERE id = :id");
                $stmt->bindParam(':user_id', $assignment);
            }
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }
    }
}

sqlclose($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply</title>
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
                <span class="hide--text">Reply<br />Tickets</span>
            </h3>
            <p>
                <span class="hide--text">
                </span>
            </p>
        </div>

        <section class="loginWrapper">
            <ul class="form-tabs">
                <li class="active">Reply</li>
                <li>Details</li>
            </ul>

            <ul class="form-tab__content">

                <li class="active">
                    <div class="content__wrapper">
                        <form method="POST" action="">
                            <?php if ($result['assignee_id'] != $_SESSION['id']) {
                                echo "WARNING: You are not the assigned to this issue.";
                            } ?>
                            <h1>
                                <?php echo $subject; ?>
                            </h1>
                            Student:
                            <?php echo $student_id; ?><br />
                            Assigned to:
                            <?php echo $assignee_name; ?><br />
                            Type:
                            <?php echo $type; ?><br />
                            Description:
                            <?php echo $description; ?>
                            <textarea name="reply" placeholder="Reply"><?php echo $reply; ?></textarea>
                            <input type="hidden" name="id" value="<?php echo $result['id']; ?>" />
                            <input type="hidden" name="CSRF" value="<?php echo $_SESSION['csrf_token']; ?>" />
                            <input type="submit" value="Reply" name="submit">

                        </form>
                    </div>
                </li>
                <li>
                    <div class="content__wrapper">
                        <form method="POST" action="">
                            Currently Assigned to:
                            <select name="__assignment">
                                <option value="null">None</option> <!-- default if no assignment -->
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php if ($user['id'] == $result['assignee_id']) {
                                           echo 'selected';
                                       } ?>>
                                        <?php echo $user['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="id" value="<?php echo $result['id']; ?>" />
                            <input type="hidden" name="CSRF" value="<?php echo $_SESSION['csrf_token']; ?>" />
                            <input type="submit" value="Assign" name="submit" <?php echo (($_SESSION['role']==2)?"":'disabled="disabled"'); ?>>

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