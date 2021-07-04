<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ripassa</title>
</head>
<body>

<?php require "utili/funzioni_utili.php";  include "utili/navbar.php";?>
<form method='post' action='ripassa.php'>
    <label for='num_domande'>Numero di domande</label><br>
    <input type='text' id='num_domande' name='num_domande'  value ="4"><br>
    <label for='Descrizione_Sessione'>Nome sessione</label><br>
    <input type='text' id='Descrizione_Sessione' name='Descrizione_Sessione'  value ="Sessione"><br>
    <div class="input-group"> 
      <label for="tipo_parola">Tipo parola</label>
        <select id="tipo_parola" name="tipo_parola">
            <?php echo inseriscitTipi(); ?>
            <option value="mix"> Mix </option>
        </select> 
  	</div>
    <div class="input-group">
      <label for="tipo_esercizio">Esercizio</label>
        <select id="tipo_esercizio" name="tipo_esercizio">
            <option value="EN">EN-IT</option>
            <option value="IT">IT-EN</option>
            <option value="mix"> Mix </option>
        </select> 
  	</div>
    <button type="submit" class=""  name="inizia">Inizia</button>
</form> 
<hr>

<?php 
//controllo se l'utente è loggato, se non lo è, lo porto sulla pagina del login
if(!isset($_SESSION["ID_PERSONA"])){
    header("location: login.php");
}

require "action-db/server.php"; //prendo parametri del db
//sessione gia avviata da funzioni utili
$ID_PERSONA = $_SESSION['ID_PERSONA'];

if(isset($_POST["inizia"])){
    $num_domande = $_POST["num_domande"]; //salvo il numero delle domande da fare
    $tipo_esercizio = $_POST["tipo_esercizio"]; //salvo il tipi di esercizio in una var
    $tipo_parola = $_POST["tipo_parola"];
    //inserisco parametri "nascosti" che ervono per il passaggio di info
    echo "
    <div>
        <form method='post' action='ripassa.php'>
            <textarea hidden name='Descrizione_Sessione'>".$_POST["Descrizione_Sessione"]." </textarea>
            <textarea hidden name='num_domande'>".$num_domande." </textarea>
            <textarea hidden name='tipo_esercizio'>".$tipo_esercizio." </textarea>
        ";
        //Seleziona tutte le righe ordinanodle a caso (e distintamente) dalla tabella parole
        
        //creo un query per selezionare parole che permette di ripassare solo le parole che ci interessano. Aggiungo una INNER JOIN per collegare tipo con parola
        //ci sono due casi: TIPO = MIX --> selezione tutte le parole di mia propietà e le parole presenti in tutte le liste liste pubbliche
        //TIPO != MIX --> seleziono tutte le parole del tipo che mi interessano
        $query = ($tipo_parola!="mix")?
                    "SELECT DISTINCT `IT`,`EN`, `ID_PAROLA` FROM `parole` INNER JOIN tipo_parola ON parole.ID_TIPO=tipo_parola.ID_TIPO WHERE tipo_parola.ID_TIPO='$tipo_parola' ORDER BY RAND()":
                    "SELECT DISTINCT `IT`,`EN`, `ID_PAROLA` FROM `parole` INNER JOIN tipo_parola ON parole.ID_TIPO=tipo_parola.ID_TIPO WHERE tipo_parola.Pubblico=1 OR tipo_parola.ID_PERSONA=$ID_PERSONA ORDER BY RAND()";
        
        $result = mysqli_query($database, $query); //esegue la query e salva su result
        $i = 0;
        //per ogni n riga (== num_domande) esegue, tale che n <= num_domande...
        while($i!=$num_domande && $row = mysqli_fetch_array($result)){ 
            
            $query2 = "SELECT COUNT(sessione_dettaglio.ID_PAROLA) as cont FROM sessione_dettaglio INNER JOIN sessione ON sessione_dettaglio.ID_SESSIONE=sessione.ID_SESSIONE WHERE sessione.ID_PERSONA = $ID_PERSONA AND sessione_dettaglio.ID_PAROLA=".$row["ID_PAROLA"];

            //conto quante volte la domanda è apparsa nella query, se è apparsa >= 10 viene "evitata"
            $cont = mysqli_query($database, $query2); //esegue la query e salva su result
            $conteggio_result = mysqli_fetch_array($cont);
            
            if($conteggio_result["cont"] <= 100){
                //se il tipo di esercizio è "MIX" allora ad ogni ciclo, seleziona casualmente il tipo di parola
                if($_POST["tipo_esercizio"] == "mix"){
                    $tipo_esercizio = rand(0, 1) ? 'EN' : 'IT';
                }
                //crea il form per inserire la risposta
                echo
                    "
                    <textarea hidden name='risposta[".$i."][ID_PAROLA]'>".$row["ID_PAROLA"]." </textarea>
                    <label for='domanda".$i."'>".$row[$tipo_esercizio]."</label><br>
                    <input type='text' id='domanda' name='risposta[".$i."][risposta]'><br>
                    "

                ;
                $i++;
            }
        }   

    //termino il form con button submit
    echo    "<button type='submit'  name='controlla'>Controlla</button>
        </form> 
    </div>"
    ;
}

?>

<?php

if(isset($_POST["controlla"]) && isset($_POST["num_domande"])){
    //carico l'ID_PERSONA
    $ID_PERSONA = $_SESSION['ID_PERSONA'];
    //Aggiungo una sessione e l'associo ad una persona
    $query = "INSERT INTO sessione(Descrizione, ID_PERSONA, Data_Svolgimento) VALUES ('".$_POST["Descrizione_Sessione"]."', $ID_PERSONA,'$date')";
    //eseguo la query
    mysqli_query($database, $query);

    $tipo_esercizio = $_POST["tipo_esercizio"];//salvo il tipo di es in una var
    //prelevo dalla tabella sessione l'ultimo ID_SESSIONE inserito
    $query = "SELECT ID_SESSIONE FROM sessione ORDER BY `ID_SESSIONE` DESC LIMIT 1 " ;
    //eseguo la query
    $result = mysqli_query($database, $query); //salvo in result
    $row = mysqli_fetch_array($result);
    
    //salvo l'ID_SESSIONE in una variabile
    $ID_SESSIONE = $row["ID_SESSIONE"];
    //tramite chiamata POST recupero l'array, se è vuoto allora $risposte sarà null, l'array altrimenti
    $risposte = isset($_POST['risposta'])? $_POST['risposta']:"null";
    $j = 0; 
    $errori = []; //vocaboli errati
    
    while($risposte!="null" && $j != sizeof($risposte)){ //eseguo il ciclo n volte (n==num_rispsoste). Se $risposte == null allora finisco
        $ID_PAROLA = $risposte[$j]["ID_PAROLA"];//salvo l'ID_PAROLA, preso dalle risposte
        $rispostaData = $risposte[$j]["risposta"];//salvo la risposta data dall'utente
        $query = "SELECT `IT`,`EN` FROM `parole` WHERE ID_PAROLA='$ID_PAROLA'"; //carica gli attributi EN e IT della parola, tramite l'ID_PAROLA
        $result = mysqli_query($database, $query);
        $row = mysqli_fetch_array($result); //eseguo la query e la salvo
        
        if ((strtolower($row["IT"])==strtolower($rispostaData) && strtolower($row["EN"])!=strtolower($rispostaData)) or (strtolower($row["EN"])==strtolower($rispostaData) && strtolower($row["IT"])!=strtolower($rispostaData)))        
        { 
            $corretto=1;
        }else{
            $corretto=0; //se la risposta data è uguale a quella salvata su db, allora corretto==1, 0 altrimenti
        }
        $query = "INSERT INTO sessione_dettaglio(ID_SESSIONE, ID_PAROLA, Corretto) VALUES ('$ID_SESSIONE', '$ID_PAROLA', '$corretto')";//salvo tutto in sessione_dettaglio come nuovo record
        mysqli_query($database, $query);
        $j ++;
        if(!$corretto){ //se la risposta è sbagliata salvo in array
            array_push($errori, $row["IT"], $row["EN"], $rispostaData); //L'array degli errori è [errore in IT, errore in EN, risposta data]
        }
    }

    echo "Nell'ultima sessione, hai sbagliato queste parole:<hr>";

    for ($i=0; $i < sizeof($errori); $i=$i+3) { //mostro le soluzioni. L'array degli errori è [errore in IT, errore in EN, risposta data]
        echo $errori[$i]. " == " . $errori[$i+1] . " tu hai scritto: " . $errori[$i+2] . "<br>";
    }
    echo " <hr> Gli errori che hai fatto sono ". sizeof($errori)/3;
}


include "utili/footer.php";

?>