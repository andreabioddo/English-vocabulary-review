<?php
session_start();
function inseriscitTipi($default){
    require "action-db/server.php"; //prendo parametri del db
    //seleziono tutti i tipi presenti che sono di mia propietà o pubblici
    $query = "SELECT * FROM `tipo_parola` WHERE Pubblico=1 OR ID_PERSONA=".$_SESSION["ID_PERSONA"]; 
    $result = mysqli_query($database, $query);
    $return = "";
    
    if(mysqli_num_rows($result) > 0){ //se ci sono piu di n righe, allora creo la tendina
        while($row = mysqli_fetch_array($result))   { 
            if($default == $row["ID_TIPO"]){
                $return = $return . "<option selected value=".$row["ID_TIPO"]."> " . $row["Descrizione"]  . "</option>";
            } else {
                $return = $return . "<option value=".$row["ID_TIPO"]."> " . $row["Descrizione"]  . "</option>";
            }
            
        }
    }
    
    return $return;
}
?> 

