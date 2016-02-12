<?php

    function h($value){
        return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
    }
    function mescape($db, $value){
        mysqli_real_escape_string($db, $value);
    }

?>
