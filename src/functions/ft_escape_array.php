<?php
    function ft_escape_array($str){
        return (filter_var_array($str, FILTER_SANITIZE_STRING));
    }
?>