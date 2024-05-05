<?php

include 'config.php';

session_start();

if(isset($_POST['login'])){

   $email = $_POST['email'];
   $pass = $_POST['pass'];

   // Prepare the SQL statement with named placeholders
   $select_user = $conn->prepare("SELECT * FROM `user` WHERE email = :email AND password = :password");

   // Bind the parameters
   $select_user->bindParam(':email', $email);
   $select_user->bindParam(':password', $pass);

   // Execute the statement
   $select_user->execute();

   // Fetch the row
   $row = $select_user->fetch(PDO::FETCH_ASSOC);

   // Check if a row was found
   if($row){
      $_SESSION['user_id'] = $row['id'];
      header('location:index.php');
      exit; // Ensure script stops execution after redirect
   } else {
      // Redirect if user not found
      header('location:index.php');
      exit; // Ensure script stops execution after redirect
   }

}

?>
