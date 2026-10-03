<?php
require('nav.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 0) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];

// View specific closed ticket 
$viewClosed = null;
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $closedQ = mysqli_execute_query($conn, "SELECT * FROM contacth WHERE id = ? AND username = ?", [$viewId, $username]);
    $viewClosed = mysqli_fetch_assoc($closedQ);
}

// load open ticket 
$q = mysqli_execute_query($conn, "SELECT * FROM contact WHERE username = ?", [$username]);
$open = mysqli_fetch_assoc($q);

// send message 
if (isset($_POST['send']) && $open) {
    $msg = trim($_POST['message']);

    if ($msg != '') {
        $time = date("d.m.y H:i");
        $line = "[$time] $username: $msg";

        $newChat = $open['chat'] ? ($open['chat'] . "\n" . $line) : $line;

        mysqli_execute_query($conn, "UPDATE contact SET chat = ?, date=NOW() WHERE id = ?", [$newChat, (int)$open['id']]);

        // Reload open ticket
        $q = mysqli_execute_query($conn, "SELECT * FROM contact WHERE username = ?", [$username]);
        $open = mysqli_fetch_assoc($q);
    }
}

// load closed tickets 
$closedQ = mysqli_execute_query($conn, "SELECT id, `date` FROM contacth WHERE username = ? ORDER BY id DESC", [$username]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support</title>
    <link rel="stylesheet" href="contactUserStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <div class="wrap">
        <!-- HEADER -->
        <div class="header">
            <div>
                <h1>Contact Support</h1>
                <p>View your open request and previous closed requests</p>
            </div>
            <div class="mini">User panel</div>
        </div>

        <div class="layout">
            <!-- LEFT PANEL -->
            <div class="card">
                <div class="sectionTitle">
                    <span>Open Ticket</span>
                    <?php if ($open): ?>
                        <span class="badge open">OPEN</span>
                    <?php else: ?>
                        <span class="badge closed">NONE</span>
                    <?php endif; ?>
                </div>

                <?php if ($open): ?>
                    <a href="contactUser.php" class="ticket <?php echo !$viewClosed ? 'active' : ''; ?>">
                        <div class="ticketHeader">
                            <span class="mini">Ticket #<?php echo $open['id']; ?></span>
                            <span class="badge open">OPEN</span>
                        </div>
                        <div class="mini">Subject: <?php echo htmlspecialchars($open['subject']); ?></div>
                        <div class="mini">Last update: <?php echo date("d.m.y H:i", strtotime($open['date'])); ?></div>
                    </a>
                <?php else: ?>
                    <div class="note">
                        No open request.<br>
                        Open a new one from the Contact page.
                    </div>
                <?php endif; ?>

                <hr style="margin:16px 0;border-color:#222">

                <div class="sectionTitle">
                    <span>Closed Tickets</span>
                    <span class="mini">history</span>
                </div>

                <div class="ticketList">
                    <?php
                    $hasClosed = false;
                    while ($row = mysqli_fetch_assoc($closedQ)):
                        $hasClosed = true;
                        $isActive = $viewClosed && $viewClosed['id'] == $row['id'];
                    ?>
                        <a href="?view=<?php echo $row['id']; ?>" class="ticket <?php echo $isActive ? 'active' : ''; ?>">
                            <div class="mini">Ticket #<?php echo $row['id']; ?></div>
                            <div class="mini">Closed: <?php echo date("d.m.y H:i", strtotime($row['date'])); ?></div>
                        </a>
                    <?php endwhile; ?>

                    <?php if (!$hasClosed): ?>
                        <div class="note">No closed tickets yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="card">
                <?php if ($viewClosed): ?>
                    <!-- VIEWING CLOSED TICKET -->
                    <div class="chatHeader">
                        <div>
                            <p class="title">Closed Ticket #<?php echo $viewClosed['id']; ?></p>
                            <p class="sub">
                                Status: <span class="badge closed">CLOSED</span>
                            </p>
                        </div>
                        <div class="rightMeta">
                            <div>Closed: <?php echo date("d.m.y H:i", strtotime($viewClosed['date'])); ?></div>
                            <a href="contactUser.php" class="backLink">← Back to open ticket</a>
                        </div>
                    </div>

                    <div class="chatArea" id="chatArea">
                        <?php if (trim($viewClosed['chat']) == ''): ?>
                            <div class="mini">No messages in this ticket.</div>
                        <?php else: ?>
                            <div class="transcript"><?php echo htmlspecialchars($viewClosed['chat']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="note">
                        This ticket is closed. To open a new request, visit the Contact page.
                    </div>

                <?php else: ?>
                    <!-- VIEWING OPEN TICKET -->
                    <div class="chatHeader">
                        <div>
                            <p class="title">Open Conversation</p>
                            <p class="sub">
                                Status:
                                <?php if ($open): ?>
                                    <span class="badge open">OPEN</span>
                                <?php else: ?>
                                    <span class="badge closed">CLOSED</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="rightMeta">
                            <?php if ($open): ?>
                                <div>Last update: <?php echo date("d.m.y H:i", strtotime($open['date'])); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="chatArea" id="chatArea">
                        <?php if (!$open || trim($open['chat']) == ''): ?>
                            <div class="mini">No messages yet.</div>
                        <?php else: ?>
                            <div class="transcript"><?php echo htmlspecialchars($open['chat']); ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($open): ?>
                        <form method="post">
                            <div class="btnRow">
                                <textarea name="message" placeholder="Write your message..." required></textarea>
                            </div>
                            <div class="btnRow">
                                <button class="btn" name="send">Send</button>
                                <button class="btn btnGhost" type="reset">Clear</button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="note">
                        If the manager replies, you will receive the response by email.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll chat area to bottom
        window.addEventListener('load', function() {
            const chatArea = document.getElementById('chatArea');
            if (chatArea) {
                chatArea.scrollTop = chatArea.scrollHeight;
            }
        });
    </script>
</body>

</html>