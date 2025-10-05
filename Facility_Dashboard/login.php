<?php
session_start();
include "conn.php";

$emailWarning = "";
$passWarning = "";

if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $Password = $_POST["password"];


    if($email =="admin" && $Password == "admin123"){
        header("Location: index.php");
    }
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $users = mysqli_fetch_array($result, MYSQLI_ASSOC);

    
    if ($users) {
        if (password_verify($Password, $users["password"])) {
            $_SESSION["id"] = $users["id"];
            header("Location: index.php");
            die();
        } else {
            $passWarning = "Password does not match";
        }
    } else {
        $emailWarning = "email does not exists";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../login.css">
    
</head>

<body>
    <nav>
        <div class="logo">
           <a href="homepage.php"><img src="https://th.bing.com/th/id/OIP.lW_QFgbFGyPHNU8DZawY0AHaHa?w=144&h=180&c=7&r=0&o=7&pid=1.7&rm=3" alt=""></a>
           
        </div>


        <!-- <div class="auth-buttons">
            <a href="" class="btn btn-signin">Sign In</a>
            <a href="signup.php" class="btn btn-signup">Sign Up</a>
        </div> -->
    </nav>

    <div class="container">
        <div class="header">
            <h1><img src="https://th.bing.com/th/id/OIP.lW_QFgbFGyPHNU8DZawY0AHaHa?w=144&h=180&c=7&r=0&o=7&pid=1.7&rm=3" alt=""></i>Lyceum Of Alabang</h1>
        </div>

        <div class="form-container">
            <form id="loginForm" action="login.php" method="POST">
                <div class="form-control">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="Enter your email" required>
                    <span class="warning"><?php echo $emailWarning; ?></span>
                    <i class="fas fa-envelope"></i>
                </div>

                <div class="form-control">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span class="warning"> <?php echo $passWarning; ?></span>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                </div>

                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgotpassword.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn" name="login">Login</button>
            </form>


            <div class="switch-form">
                Don't have an account? <a href="signup.php">Sign up</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.password-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>