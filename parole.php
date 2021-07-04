<?php

//controllo se l'utente è loggato, se non lo è, lo porto sulla pagina del login
if(!isset($_SESSION["ID_PERSONA"])){
  header("location: login.php");
}

require "action-db/server.php"; //prendo parametri del db
require "utili/funzioni_utili.php"; //carico funzioni utili

if(isset($_POST["aggiungi"])){

  //prendo i parametri di Tipo POST dal file chiamante e li salvo in una variabile
  $EN = $_POST["EN"];
  $IT = $_POST["IT"];
  $tipo = $_POST["Tipo"];
  $ID_PERSONA = $_SESSION["ID_PERSONA"];
  //preparo la query
  $query = "INSERT INTO parole(IT, EN, ID_TIPO, ID_PERSONA, attivo, Data_Inserimento) VALUES ('$IT', '$EN', '$tipo', '$ID_PERSONA', '1', '$date')";
  //eseguo la query
  $result = mysqli_query($database, $query);

}

?>

<div class="header">
  	<h2>Add parola</h2>
  </div>
	 
  <form method="post" action="parole.php">
  	<div class="input-group">
  		<label>In Inglese</label>
  		<input type="text" name="EN" >
  	</div>
    <div class="input-group">
  		<label>In Italiano</label>
  		<input type="text" name="IT" >
  	</div>
  	<div class="input-group">
      <label for="Tipo">Tipo</label>
        <select id="Tipo" name="Tipo">
            <?php echo inseriscitTipi(); ?>
        </select> 
  	</div>
  	<div class="input-group">
  		<button type="submit" class="btn" name="aggiungi">Aggiungi</button>
  	</div>
  </form>


<h1> Le tue parole </h1>

<?php
$ID_PERSONA = $_SESSION["ID_PERSONA"];
$query = "SELECT `IT`,`EN`, `ID_PAROLE` FROM `parole` WHERE ID_PERSONA =$ID_PERSONA";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
$result = mysqli_query($database, $query); //esegue la query e salva su result
//per ogni n riga (== num_domande) esegue, tale che n <= num_domande...
while($row = mysqli_fetch_array($result)){ 
        echo "<br>" . $row["IT"] . "    " .$row["EN"] ;
}   

echo " <hr> Le altre parole disponibili";

$query = "SELECT `IT`,`EN`, `ID_PAROLE` FROM `parole` WHERE NOT ID_PERSONA =$ID_PERSONA";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
$result = mysqli_query($database, $query); //esegue la query e salva su result
//per ogni n riga (== num_domande) esegue, tale che n <= num_domande...
while($row = mysqli_fetch_array($result)){ 
        echo "<br>". $row["IT"] . "  che si traduce in  " .$row["EN"];
}   


?>