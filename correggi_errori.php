<?php require "utili/funzioni_utili.php"; ?>
<form method='post' action='correggi_errori.php'>
    <label for='num_errori'>Numero di domande</label><br>
    <input type='text' id='num_errori' name='num_errori' value ="4"><br>
    <div class="input-group">
      <label for="tipo_esercizio">Esercizio</label>
        <select id="ID_SESSIONE" name="ID_SESSIONE">
            <option  disabled value="mix">Mix</option>
            <?php echo sessione_da_correggere() ;?>
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

//metodo per la realizzazione del menu a tendina che permette di scegliere quale sessione correggere
function sessione_da_correggere(){
    require "action-db/server.php"; //prendo parametri del db
    //prendo l'ultimo id sessione della persona
    $ID_PERSONA = $_SESSION["ID_PERSONA"];
    $query = "SELECT ID_SESSIONE FROM sessione WHERE `ID_PERSONA`=$ID_PERSONA ORDER BY `ID_SESSIONE` DESC LIMIT 1 " ;
    //eseguo la query
    $result = mysqli_query($database, $query); //salvo in result
    $row = mysqli_fetch_array($result);
    
    $ID_SESSIONE = $row["ID_SESSIONE"]+1;
    
    while($ID_SESSIONE != -1){ //il ciclo si ferma se non trovo sessioni con errori oppure trovo una sessione co almeno un errore
        $ID_SESSIONE--; //
        $query = "SELECT COUNT(*) AS cont, sessione.Descrizione FROM `sessione_dettaglio` INNER JOIN sessione ON sessione_dettaglio.ID_SESSIONE = sessione.ID_SESSIONE WHERE sessione.ID_PERSONA=$ID_PERSONA AND sessione.ID_SESSIONE=$ID_SESSIONE AND sessione_dettaglio.Corretto=0";
        $cont = mysqli_query($database, $query); //esegue la query e salva su result
        
        $result = mysqli_fetch_array($cont);
        //salvo il risultato in un opzione possibile del menu 
        if($result["cont"]){
            $return = $return . "<option value='$ID_SESSIONE'> ".$result["Descrizione"] . "</option>";
        }
    }
    return $return;
}


require "action-db/server.php"; //prendo parametri del db
$ID_PERSONA = $_SESSION['ID_PERSONA'];

if(isset($_POST["inizia"])){
    $num_errori = $_POST["num_errori"]; //salvo il numero delle domande da fare
    
    $ID_SESSIONE = $_POST["ID_SESSIONE"];
    //cerco l'id della prima sessione con almeno una parola da correggere
    //prendo il valore dell'ultima sessione disposnibile e da li ciclo a salire finche non trovo un errore


    if($ID_SESSIONE != -1){//se la sessione è -1, non sono state trovate sessioni da correggere       
        //inserisco parametri "nascosti" che servono per il passaggio di informazioni di sistema
        echo "
        <form method='post' action='correggi_errori.php'>
            <textarea hidden name='num_errori'>".$num_errori." </textarea>
            <textarea hidden name='ID_SESSIONE'>$ID_SESSIONE</textarea>
        ";
        //prendo i paramtri IT, EN e l?ID_PAROLE della parola che rispetta i criteri, ovvero della sessione, della persoa e che sia stata scritta sbagluata (==CORRETTO = 0)
        $query = "SELECT parole.IT, parole.EN, parole.ID_PAROLE FROM parole INNER JOIN sessione_dettaglio ON sessione_dettaglio.ID_PAROLA = parole.ID_PAROLE INNER JOIN sessione ON sessione.ID_SESSIONE=sessione_dettaglio.ID_SESSIONE WHERE sessione_dettaglio.Corretto=0 AND sessione.ID_PERSONA=$ID_PERSONA AND sessione.ID_SESSIONE=$ID_SESSIONE ORDER BY RAND()";//aggiungere WHERE ID_PERSONA='$ID_PERSONA' per mettere solo parole della persona
        $result = mysqli_query($database, $query); //esegue la query e salva su result
        $i = 0; //inizializzo i
        //per ogni n riga (== num_errori) esegue, tale che n <= num_errori...
        while($i!=$num_errori && $row = mysqli_fetch_array($result)){  
            $tipo_esercizio = rand(0, 1) ? 'EN' : 'IT';
            //crea il form per inserire la risposta
            echo
                "
                <textarea hidden name='risposta[".$i."][ID_PAROLA]'>".$row["ID_PAROLE"]." </textarea>
                <label for='domanda".$i."'>".$row[$tipo_esercizio]."</label><br>
                <input type='text' id='domanda' name='risposta[".$i."][risposta]'><br>
                "
            ;
            $i++;
        }   
        //termino il form con button submit
        echo "<button type='submit'  name='controlla'>Controlla</button>
        </form> ";
    } else {
        echo "Non hai parole da correggere!!";
    }
}

?>

<?php

if(isset($_POST["controlla"]) && isset($_POST["num_errori"])){
    //carico l'ID_PERSONA
    $ID_PERSONA = $_SESSION['ID_PERSONA'];
    //salvo l'ID_SESSIONE in una variabile, il valore è passato dal form
    $ID_SESSIONE = $_POST["ID_SESSIONE"];
    //tramite chiamata POST recupero l'array, se è vuoto allora $risposte sarà null, l'array altrimenti
    $risposte = isset($_POST['risposta'])? $_POST['risposta']:"null";
    $j = 0; 
    $errori = []; //vocaboli errati
    
    while($risposte!="null" && $j != sizeof($risposte)){ //eseguo il ciclo n volte (n==num_rispsoste). Se $risposte == null allora finisco
        $ID_PAROLA = $risposte[$j]["ID_PAROLA"];//salvo l'ID_PAROLA, preso dalle risposte
        $rispostaData = $risposte[$j]["risposta"];//salvo la risposta data dall'utente
        $query = "SELECT `IT`,`EN` FROM `parole` WHERE ID_PAROLE='$ID_PAROLA'"; //carica gli attributi EN e IT della parola, tramite l'ID_PAROLA
        $result = mysqli_query($database, $query);
        $row = mysqli_fetch_array($result); //eseguo la query e la salvo
        
        if(strtolower($row["IT"])==strtolower($rispostaData) || strtolower($row["EN"])==strtolower($rispostaData)) 
            $corretto=1;
        else
            $corretto=0; //se la risposta data è uguale a quella salvata su db, allora corretto==1, 0 altrimenti
        $query = " UPDATE  sessione_dettaglio SET Corretto=$corretto WHERE ID_SESSIONE=$ID_SESSIONE AND ID_PAROLA=$ID_PAROLA";//salvo tutto in sessione_dettaglio come nuovo record
        mysqli_query($database, $query);
        $j ++;
        if(!$corretto) //se la risposta è sbagliata salvo in array
            array_push($errori, $row["IT"], $row["EN"], $rispostaData); //L'array degli errori è [errore in IT, errore in EN, risposta data]
    }

    echo "Nell'ultima sessione, hai sbagliato queste parole:<hr>";

    for ($i=0; $i < sizeof($errori); $i=$i+3) { //mostro le soluzioni. L'array degli errori è [errore in IT, errore in EN, risposta data]
        echo $errori[$i]. " == " . $errori[$i+1] . " tu hai scritto: " . $errori[$i+2] . "<br>";
    }
    echo " <hr> Gli errori che hai fatto sono ". sizeof($errori)/3;
}

?>