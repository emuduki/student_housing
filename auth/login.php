<?php
session_start();
include("../config/db.php");

// Debug: Check if form is being submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Form submitted successfully!<br>";
    echo "Email: " . $_POST["email"] . "<br>";
    echo "Password: " . $_POST["password"] . "<br>";
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            //$_SESSION["role"] = $user["role"];
            $_SESSION["role"] = strtolower(trim($user["role"]));


            // Redirect based on role
            if ($user["role"] == "student") {
                header("Location: ../dashboards/student_dashboard.php");
            } elseif ($user["role"] == "landlord") {
                header("Location: ../dashboards/landlord_dashboard.php");
            } elseif ($user["role"] == "admin") {
                header("Location: ../dashboards/admin_dashboard.php");
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>
