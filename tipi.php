<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tipi</title>
</head>
<body>

<?php require "utili/funzioni_utili.php"; include "utili/navbar.php"; ?>
<form method='post' action='tipi'>
    <label for='tipi'>Aggiungi tipo</label><br>
    <input type='text' id='tipi' name='tipi'><br>
    <input type="checkbox" id="pubblico" name="pubblico" value="1">
    <label for="pubblico">Pubblico</label><br>
    <button type="submit" name="aggiungi_tipo">Aggiungi</button>
</form> 
<hr>

<?php 

//controllo se l'utente è loggato, se non lo è, lo porto sulla pagina del login
if(!isset($_SESSION["ID_PERSONA"])){
    header("location: login");
}
  
require "action-db/server.php"; //prendo parametri del db
//Se si clicca aggiunge tipo e tipo è non nullo
if(isset($_POST["aggiungi_tipo"]) && $_POST["tipi"]!=""){ 
    $ID_PERSONA = $_SESSION["ID_PERSONA"]; //asalvo id persona
    $tipo_nuovo = $_POST["tipi"]; //salvo il nuovo tipo
    $pubblico = isset($_POST["pubblico"])?$_POST["pubblico"]:"0"; //setto lo stato del nuovo tipo
    //query per aggiunta del tipo su db
    $query = "INSERT INTO tipo_parola(Descrizione, ID_PERSONA, Pubblico, Data_Inserimento) VALUES ('$tipo_nuovo', '$ID_PERSONA', '$pubblico', '$date')";
    mysqli_query($database, $query);
}

//se viene cliccato aggiorna, vengono aggiornate le preferenze di ogni singolo tipo
if(isset($_POST["aggiorna"])){
    $risposte = isset($_POST['tipi'])? $_POST['tipi']:array(); //si salva l'array di dati passato dal form
    for($i = 0; $i < sizeof($risposte) ; $i++){ //per ogni riga di dati in arrivo del form
        $ID_TIPO = $risposte[$i]["ID_TIPO"];//salvo l'ID_PAROLA, preso dalle risposte
        $stato = isset($risposte[$i]["stato"])? "1":"0"; //setta lo stato 
        $elimina = isset($risposte[$i]["elimina"])? "1":"0"; //setta valore di elimina
        
        //aggiorna gli stati di ogni tipo
        $query = "UPDATE tipo_parola SET Pubblico=$stato WHERE ID_TIPO=$ID_TIPO";
        mysqli_query($database, $query);

        if($elimina == 1){//se elimina è 1, allora ogni parola del tipo viene eliminata
            $query="DELETE FROM parole WHERE `ID_TIPO`=$ID_TIPO";
            mysqli_query($database, $query);
            //elimino il tipo
            $query="DELETE FROM tipo_parola WHERE ID_TIPO=$ID_TIPO";
            mysqli_query($database, $query);
        }
    }
}

//scrivo a video tutti  i tipi miei personali, il loro stato (pubblico o privato) e la possibiltà di elimninare
$ID_PERSONA = $_SESSION["ID_PERSONA"];
$query = "SELECT `Descrizione`, `Pubblico`, `ID_TIPO` FROM `tipo_parola` WHERE ID_PERSONA =$ID_PERSONA";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
$result = mysqli_query($database, $query); //esegue la query e salva su result

echo"
    <form method='post' action='tipi'>
    ";

$i = 0;
while($row = mysqli_fetch_array($result)){ //scrivo tutte le righe, creo il form per poter aggiornare / eliminare un tipo
    $attivo = ($row["Pubblico"]==1)?"checked":"";
    echo  "<textarea hidden name='tipi[".$i."][ID_TIPO]'>".$row["ID_TIPO"]." </textarea>" .
    "<label>". $row["Descrizione"] . " </label> <br>   " .
    "<input type='checkbox' id='pubblico' name='tipi[$i][stato]' value='1'".$attivo.">
    <label for='pubblico'>Pubblico</label><br>".
    "<input type='checkbox' id='elimina' name='tipi[$i][elimina]' value='1'>".
    "<label for='elimina'>Elimina</label><br><hr>";
    $i++;
}   

echo " 
        <button type='submit'  name='aggiorna'>Aggiorna</button>
    </form>
    <hr> <h3> Gli altri tipi disponibili </h3>
    ";

//scrivo a video ogni riga presente nel db come tipo pubblico non della persona (tutti gli altri diversi dai suoi)
$query = "SELECT `Descrizione` FROM `tipo_parola` WHERE NOT ID_PERSONA =$ID_PERSONA AND Pubblico=1 ";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
$result = mysqli_query($database, $query); //esegue la query e salva su result
while($row = mysqli_fetch_array($result)){ 
    echo "<br>" . $row["Descrizione"] . "    ";
}   


include "utili/footer.php";

?>

