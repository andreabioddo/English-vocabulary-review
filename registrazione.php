<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
</head>
<body>
<?php
include "utili/navbar.php";
require "action-db/server.php"; //prendo parametri del db
session_start();
$error = array();
if(isset($_POST["register"])){
  //prendo i parametri di Tipo POST dal file chiamante e li salvo in una variabile
  $username = strtolower($_POST["username"]);
  $email = strtolower($_POST["email"]);
  $psw = $_POST["psw"];


  //se la mail esiste
  $query = "SELECT * FROM `persone` WHERE Email = '$email'" ;
  $controllo_mail = mysqli_query($database, $query);
  
  if(mysqli_num_rows($controllo_mail) > 0){//se esiste viene aggiunto l'errore
    array_push($error, "Email già utilizzata");
  }

  $query = "SELECT * FROM `persone` WHERE `Utente`='$username'" ;
  $controllo_username = mysqli_query($database, $query);

  if(mysqli_num_rows($controllo_username) > 0){//se nome utente esiste già allora
    array_push($error, "Nome utente già utilizzato");
  }


  if(sizeof($error) == 0){
    $password = md5($psw);
    //preparo la query
    $query = "INSERT INTO persone(Utente, Email, Password, Data_Inserimento) VALUES ('$username', '$email', '$password', '$date')";
    //eseguo la query
    $result = mysqli_query($database, $query);
    
    
    $query = "SELECT ID_PERSONA FROM `persone` WHERE `Utente`='$username' OR Email ='$email'" ;
    //eseguo la query
    $result = mysqli_query($database, $query);
    $row = mysqli_fetch_array($result);
    $_SESSION['ID_PERSONA'] = $row["ID_PERSONA"];
    
    //header("location: registrazione");
  }
}

?>


<form action="registrazione.php" method="POST">
  <div class="container">
    <h1>Register</h1>
    <p>Please fill in this form to create an account.</p>
    <hr>
    <?php foreach ($error as $e) {echo $e . "<br>";} ?>

    <label for="username"><strong>Username</strong></label>
    <input required type="text" placeholder="Enter Username" name="username" id="username" required>

    <label for="email"><strong>Email</strong></label>
    <input required type="email" placeholder="Enter Email" name="email" id="email" required>

    <label for="psw"><strong>Password</strong></label>
    <input required type="password" placeholder="Enter Password" name="psw" id="psw" required>

    <hr>

    <p>By creating an account you agree to our <a href="#">Terms & Privacy</a>.</p>
    <button type="submit" class="registerbtn" name="register">Register</button>
  </div>

  <div class="container signin">
    <p>Already have an account? <a href="login">Sign in</a>.</p>
  </div>
</form> 

<?php include "utili/footer.php";?>