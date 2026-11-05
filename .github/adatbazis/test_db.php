<?php
    $messeage="";

    require_once("dbconnect.php");
    if(isset($dbConn)){
        $messeage="Sikerült csatlakozni az adatbázishoz!";
        echo "<p>  $messeage </p>";

        $adatbazisEll=$dbConn->query("SHOW TABLES");
        $tablak=$adatbazisEll->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Táblák:</p>";
        echo "<ul>";
            foreach($tablak as $t){
                echo "<li>" . htmlspecialchars($t) . "</li>";
            }
        echo "</ul>";
    }
    else{
        $messeage="Nem sikerült csatlakozni az adatbázishoz!";
        echo "<p>  $messeage </p>";
    }
?>