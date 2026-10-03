<?php
require('nav.php');

// AUTH CHECK (MANAGER ONLY) 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 1) {
  header("Location: logIn.php");
  exit;
}
$username = $_SESSION['username'];
$admin = $username;

// VIEW 
$view = isset($_GET['view']) ? $_GET['view'] : 'open';

if ($view !== 'open' && $view !== 'closed') $view = 'open';

// SEARCH 
$search = '';
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
  $search = trim($_GET['q']);
}

// SELECTED 
$user = isset($_GET['u']) ? $_GET['u'] : '';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

//LIST 
if ($view === 'open') {
  if ($search !== '') {
    if (is_numeric($search)) {
      $listQ = mysqli_execute_query($conn, "SELECT id, username, `date` FROM contact WHERE id = ? OR username LIKE ?
                OR chat LIKE ? ORDER BY date DESC", [$search, "%$search%", "%$search%"]);
    } else {
      $listQ = mysqli_execute_query($conn, "SELECT id, username, `date` FROM contact WHERE username LIKE ?
                OR chat LIKE ? ORDER BY date DESC", ["%$search%", "%$search%"]);
    }
  } else {
    $listQ = mysqli_execute_query($conn, "SELECT id, username, `date` FROM contact ORDER BY date DESC");
  }
} else {
  if ($search !== '') {
    if (is_numeric($search)) {
      $listQ = mysqli_execute_query($conn, "SELECT id, username, `date` FROM contacth WHERE id = ? OR username LIKE ?
                OR chat LIKE ? ORDER BY date DESC", [$search, "%$search%", "%$search%"]);
    } else {
      $listQ = mysqli_execute_query($conn, "SELECT id, username, `date` FROM contacth WHERE username LIKE ?
                OR chat LIKE ? ORDER BY date DESC", ["%$search%", "%$search%"]);
    }
  } else {
    $listQ = mysqli_execute_query($conn, "SELECT id, username, `date` FROM contacth ORDER BY date DESC");
  }
}

// LOAD TICKET 
$ticket = null;

if ($view === 'open' && $user) {
  // System design: one open ticket per user
  $q = mysqli_execute_query($conn, "SELECT * FROM contact WHERE username = ?", [$user]);
  $ticket = mysqli_fetch_assoc($q);
}

if ($view === 'closed' && $id) {
  $q = mysqli_execute_query($conn, "SELECT * FROM contacth WHERE id = ?", [$id]);
  $ticket = mysqli_fetch_assoc($q);
}

// REPLY 
if (isset($_POST['reply']) && $view === 'open' && isset($ticket['username'])) {
  $msg = trim($_POST['message']);
  if ($msg !== '') {
    $line = "[" . date("d.m.y H:i") . "] $admin: $msg";
    $chat = $ticket['chat'] ? $ticket['chat'] . "\n" . $line : $line;

    $userTicket = $ticket['username'];
    mysqli_execute_query($conn, "UPDATE contact SET chat = ?, date = NOW() WHERE username = ?", [$chat, $userTicket]);

    $emailQ = mysqli_execute_query($conn, "SELECT email FROM users WHERE username = ?", [$userTicket]);
    $emailRow = mysqli_fetch_assoc($emailQ);
    if ($emailRow && $emailRow['email'] != '') {

      $to = $emailRow['email'];
      $subject = "Support reply from the site";

      $mailBody  = "<h3>Hello $userTicket</h3>";
      $mailBody .= "<p>The administrator has replied to your request.</p>";
      $mailBody .= "<p><b>Conversation history:</b></p>";
      $mailBody .= "<pre style='background:#f5f5f5;padding:10px;border-radius:8px;white-space:pre-wrap;'>";
      $mailBody .= $chat;
      $mailBody .= "</pre>";
      $mailBody .= "<p>Please reply only through the website.</p>";

      $headers  = "From: RevZone <" . MAIL_FROM . ">\r\n";
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

      mail($to, $subject, $mailBody, $headers);
    }

    // reload
    $q = mysqli_execute_query($conn, "SELECT * FROM contact WHERE username = ?", [$userTicket]);
    $ticket = mysqli_fetch_assoc($q);
  }
}

// CLOSE
if (isset($_POST['close']) && $ticket && $view === 'open') {
  $userTicket = $ticket['username'];

  $closeLine = "[" . date("d.m.y H:i") . "] $admin: (Ticket closed)";
  $chat = $ticket['chat'] . "\n" . $closeLine;

  mysqli_execute_query($conn, "INSERT INTO contacth (username, chat, `date`) VALUES (?, ?, NOW())", [$ticket['username'], $chat]);

  mysqli_execute_query($conn, "DELETE FROM contact WHERE username = ?", [$userTicket]);

  header("Location: contactManager.php?view=open");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Contact Manager</title>
  <link rel="stylesheet" href="contactManagerStyle.css">
</head>

<body>
  <?php require('header.php'); ?>
  <div class="wrap">
    <div class="header">
      <div>
        <h1>Contact Manager</h1>
        <p>Manage open tickets and review history</p>
      </div>

      <div class="mini">Admin</div>
    </div>

    <div class="tabs-row">
      <div class="tabs">
        <a class="tab <?php echo ($view == 'open') ? 'active' : '' ?>" href="?view=open">Open</a>
        <a class="tab <?php echo ($view == 'closed') ? 'active' : '' ?>" href="?view=closed">Closed</a>
      </div>

      <form method="get" class="search">
        <input type="hidden" name="view" value="<?php echo $view; ?>">
        <input type="text" name="q" placeholder="Search by username or ticket ID"
          value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn" type="submit">Search</button>
        <button class="btn" type="button" onclick="window.location.href='contactManager.php?view=<?php echo $view; ?>'">
          Clear
        </button>
      </form>
    </div>

    <div class="layout">
      <!-- LIST -->
      <div class="card">
        <?php $sectionTitle = ($view == 'open') ? 'Open Tickets' : 'Closed Tickets'; ?>

        <div class="sectionTitle">
          <?php echo $sectionTitle; ?>
        </div>

        <div class="ticketList">
          <?php
          $has = false;
          while ($r = mysqli_fetch_assoc($listQ)) {
            $has = true;
            if ($view == 'open') {
              $activeClass = ($r['username'] == $user) ? 'active' : '';
              $href = "?view=open&u=" . $r['username'];
              $title = "User: " . $r['username'];
              $sub   = "Updated: " . $r['date'];
            } else {
              $activeClass = ($r['id'] == $id) ? 'active' : '';
              $href = "?view=closed&id=" . $r['id'];
              $title = "Ticket #" . $r['id'] . " – " . $r['username'];
              $sub   = "Closed: " . $r['date'];
            }
          ?>
            <a class="ticket <?php echo $activeClass ?>" href="<?php echo htmlspecialchars($href) ?>">
              <div class="mini"><?php echo htmlspecialchars($title) ?></div>
              <div class="mini"><?php echo htmlspecialchars($sub) ?></div>
            </a>
          <?php } ?>

          <?php if (!$has) { ?>
            <div class="note">No tickets found for this view.</div>
          <?php } ?>
        </div>
      </div>

      <!-- CHAT -->
      <div class="card">
        <?php if (!$ticket) { ?>
          <div class="note">Select a ticket.</div>
        <?php } else { ?>
          <div class="chatHeader">
            <div>
              <?php $titleText = ($view == 'open') ? "Open — " . $ticket['username'] : "Closed #" . $ticket['id'] . " — " . $ticket['username']; ?>
              <p class="title"><?php echo htmlspecialchars($titleText); ?></p>
              <p class="sub"><?php echo strtoupper($view) ?></p>
            </div>

            <div class="rightMeta">
              <div>
                <?php echo ($ticket['date'] != '') ? date("d.m.y H:i", strtotime($ticket['date'])) : '-'; ?>
              </div>
            </div>
          </div>

          <div class="chatArea">
            <div class="transcript"><?php echo htmlspecialchars($ticket['chat']); ?></div>
          </div>

          <?php if ($view == 'open') { ?>
            <form method="post">
              <textarea name="message" placeholder="Reply..."></textarea>
              <div class="btnRow">
                <button class="btn" name="reply">Reply</button>
                <button class="btnDanger" name="close" onclick="return confirm('Are you sure you want to close this ticket?')">
                  Close Ticket
                </button>
              </div>
            </form>
          <?php } else { ?>
            <div class="note">Closed ticket – read only.</div>
          <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</body>

</html>