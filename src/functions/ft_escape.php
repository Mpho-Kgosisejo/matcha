<?php
    function ft_escape($string){
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}
?>