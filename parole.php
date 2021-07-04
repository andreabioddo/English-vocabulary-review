<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parole</title>
</head>
<body>
<?php
include "utili/navbar.php";
require "action-db/server.php"; //prendo parametri del db
require "utili/funzioni_utili.php"; //carico funzioni utili

//controllo se l'utente è loggato, se non lo è, lo porto sulla pagina del login
if(!isset($_SESSION["ID_PERSONA"])){
  header("location: login");
}

if(isset($_POST["aggiungi"])){

  //prendo i parametri di Tipo POST dal file chiamante e li salvo in una variabile
  $EN = $_POST["EN"];
  $IT = $_POST["IT"];
  $tipo = $_POST["Tipo"];
  $ID_PERSONA = $_SESSION["ID_PERSONA"];
  //preparo la query
  $query = "INSERT INTO parole(IT, EN, ID_TIPO, ID_PERSONA, Data_Inserimento) VALUES ('$IT', '$EN', '$tipo', '$ID_PERSONA', '$date')";
  //eseguo la query
  $result = mysqli_query($database, $query);

}

?>

<div class="header">
  	<h2>Aggiungi parola</h2>
  </div>
	 
  <form method="post" action="parole">
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

echo"
  <form method='post' action='parole'>
";
$i = 0;

$ID_PERSONA = $_SESSION["ID_PERSONA"];
$query = "SELECT parole.ID_PAROLA, parole.IT, parole.EN, tipo_parola.Descrizione FROM `parole` INNER JOIN tipo_parola ON parole.ID_TIPO = tipo_parola.ID_TIPO WHERE parole.ID_PERSONA=$ID_PERSONA";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
$result = mysqli_query($database, $query); //esegue la query e salva su result
//per ogni n riga (== num_domande) esegue, tale che n <= num_domande...
while($row = mysqli_fetch_array($result)){ 
        echo "
        <textarea hidden name='parole[".$i."][ID_PAROLA]'>".$row["ID_PAROLA"]." </textarea>
        <br> <strong>" . $row["IT"] . " </strong> che si traduce in <strong> " .$row["EN"] . " </strong>      "."  (lista =".$row["Descrizione"].")".
        "<input type='checkbox' id='elimina' name='parole[$i][elimina]' value='1'>".
        "<label for='elimina'>Elimina</label>";
        $i++;
}   

echo " <br>
    <button type='submit' name='aggiorna'>Aggiorna</button>
</form>
";
echo " <hr> <h3> Le altre parole disponibili </h3>";

$query = "SELECT parole.ID_PAROLA, parole.IT, parole.EN, tipo_parola.Descrizione FROM `parole` INNER JOIN tipo_parola ON parole.ID_TIPO = tipo_parola.ID_TIPO WHERE NOT parole.ID_PERSONA=$ID_PERSONA ";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
$result = mysqli_query($database, $query); //esegue la query e salva su result
//per ogni n riga (== num_domande) esegue, tale che n <= num_domande...
while($row = mysqli_fetch_array($result)){ 
        echo "<br><strong>". $row["IT"] . "</strong>  che si traduce in  <strong>" .$row["EN"] ."</strong>"."  (lista =".$row["Descrizione"].")";
}   


if(isset($_POST["aggiorna"])){
  $risposte = isset($_POST['parole'])? $_POST['parole']:array(); //si salva l'array di dati passato dal form
  for($i = 0; $i < sizeof($risposte) ; $i++){ //per ogni riga di dati in arrivo del form
    $ID_PAROLA = $risposte[$i]["ID_PAROLA"];//salvo l'ID_PAROLA, preso dalle risposte
    $elimina = isset($risposte[$i]["elimina"])? "1":"0"; //setta valore di elimina
    if($elimina == 1){//se elimina è 1, allora ogni parola del tipo viene eliminata
      $query="DELETE FROM parole WHERE `ID_PAROLA`=$ID_PAROLA";
      mysqli_query($database, $query);
    }
  }
}


include "utili/footer.php";

?>