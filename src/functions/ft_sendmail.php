<?php
    function ft_sendmail($to, $subject, $message){
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers = implode("\r\n", $headers);

        return (mail($to, $subject, $message, $headers));
    }
?>