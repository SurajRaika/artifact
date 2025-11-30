<?php

class User
{
    private $link;

    public function __construct($link)
    {
        $this->link = $link;
    }

    public function check_email($email)
    {
        $result = ['email' => '', 'email_err' => ''];

        if (empty(trim($email))) {
            $result['email_err'] = "Please enter an email address.";
            return $result;
        }

        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result['email_err'] = "Please enter a valid email address.";
            return $result;
        }

        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($this->link, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                $result['email_err'] = "This email is already registered.";
                return $result;
            }

            mysqli_stmt_close($stmt);
        }

        $result['email'] = $email;
        return $result;
    }

    public function register($username, $email, $password)
    {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->link, $sql);

        if ($stmt) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                $this->redirect('./login.php',"Registration successful. Please log in.");
            }
        }

        $this->redirectWithError('./register.php');
    }

    public function login($email, $password)
    {
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
        $stmt = mysqli_prepare($this->link, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $email, $hashed_password);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashed_password)) {
                    session_regenerate_id(true);
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["email"] = $email;

                    
                    $_SESSION["loggedin"] = true;
                    setcookie('JustLogged', true, time() + 600, '/');
                    $this->redirect('./',"Login successful.");
                } else {
                    return "The email or password you entered is incorrect.";
                }
            } else {
                return "The email is not registered.";
            }
        }

        $this->redirectWithError('./login.php');
    }

    private function redirect($location,$message)
    {
        //store message in session so that when  user go to new location there we can show that message notification
        setcookie('message', $message, time() + 600, '/');
        echo "<script>window.location.href='$location';</script>";
        exit;
    }

    private function redirectWithError($location)
    {
        echo "<script>alert('Oops! Something went wrong. Please try again later.');</script>";
        echo "<script>window.location.href='$location';</script>";
        exit;
    }
}
