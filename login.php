<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<?php
require "utili/navbar.php";
require "action-db/server.php"; //prendo parametri del db
session_start();

if(isset($_SESSION["ID_PERSONA"])){
    echo "<h4> Sei già loggato! </h4>";
} else {
    echo "<h4> Non hai ancora effettuato l'accesso!</h4>";
}
$error = array();
if(isset($_POST["login"])){
    //prendo i parametri di Tipo POST dal file chiamante e li salvo in una variabile
    $usernameEmail = strtolower($_POST["usernameEmail"]);
    $psw = $_POST["password"];

    $password = md5($psw);

    //preparo la query
    $query = "SELECT * FROM persone WHERE (Utente='$usernameEmail' OR Email='$usernameEmail') AND Password='$password' LIMIT 1";
    //eseguo la query
    $results = mysqli_query($database, $query);
    
    if (mysqli_num_rows($results) == 1) {
        $query = "SELECT ID_PERSONA FROM `persone` WHERE `Utente`='$usernameEmail' OR 'Email' ='$usernameEmail'" ;
        //eseguo la query
        $result = mysqli_query($database, $query);
        $row = mysqli_fetch_array($result);
    
        $_SESSION['ID_PERSONA'] = $row["ID_PERSONA"];
        header("location: parole.php");

    } else {
        array_push($error, "Utente o password errata");
    }
}

?>



<div class="header">
  	<h2>Login</h2>
  </div>
  <?php foreach ($error as $e) {echo $e . "<br>";} ?>
  <form method="post" action="login.php">
  	<div class="input-group">
  		<label>Username or Email</label>
  		<input required type="text" name="usernameEmail" >
  	</div>
  	<div class="input-group">
  		<label>Password</label>
  		<input required type="password" name="password">
  	</div>
  	<div class="input-group">
  		<button type="submit" class="btn" name="login">Login</button>
  	</div>
  	<p>
  		Not yet a member? <a href="registrazione.php">Sign up</a>
  	</p>
  </form>

<?php include "utili/footer.php"; ?>