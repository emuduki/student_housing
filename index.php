<?php
// Redirect the site root to the static homepage (index.html)
// This prevents an empty index.php from showing a blank page.
header("Location: index.html");
exit;
