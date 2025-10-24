<?php
// Temporary debug helper: check session persistence and values.
// Place this file in the project root and open in your browser after logging in.

session_start();
header('Content-Type: text/plain; charset=utf-8');

echo "SESSION DEBUG\n";
echo "=================\n\n";

echo "session_id(): " . session_id() . "\n\n";

echo "Cookie info (from \\$_COOKIE):\n";
print_r($_COOKIE);

echo "\n\n";
echo "_SESSION values:\n";
print_r($_SESSION);

echo "\n\n";
echo "Server time: " . date('Y-m-d H:i:s') . "\n\n";

echo "Helpful links:\n";
echo "- Login page: /login.html\n";
echo "- Logout: /auth/logout.php\n";
echo "- Student dashboard: /dashboards/student_dashboard.php\n\n";

echo "Instructions:\n";
echo "1) Log in as a user in one tab.\n";
echo "2) Open this check_session.php in another tab and note session_id() and \\\$_SESSION['role'] (this line prints literally).\n";
echo "3) Navigate your app (browse, open pages, reload).\n";
echo "4) Re-open this page and confirm session_id() and session values remain the same.\n";

?>