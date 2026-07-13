<?php
// Redirect to root detail.php
$game = isset($_GET['game']) ? $_GET['game'] : '';
header("Location: ../detail.php?game=" . urlencode($game));
exit;
?>