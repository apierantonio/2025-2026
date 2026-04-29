<?php

    
    if (!isset($_POST['state'])) {
        $state = 0;
    } else {
        $state = $_POST['state'];

    }

    switch ($state) {
        case 0: // emissione form
            echo "<form method='post'>
                    <input type='hidden' name='state' value='1'>
                    <button type='submit'>Aggiungi</button>
                </form>";
            break;
        case 1: // transazione
            echo "transazione";
            break;
    }

?>