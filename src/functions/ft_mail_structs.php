<?php
    function ft_ms_register($username, $token){
        $ret = '<html>'.
            '<body style="font-family: Tahoma, Geneva, Verdana, sans-serif; color: #525252;">'.
            '<p><b>Matcha - Registration Code</b></p>'.
            '<br>'.
            '<p>Hello <b>'. $username .'</b>.</p>'.
            '<p>Your code is:</p>'.
            '<div style="padding: 10px 15px; display: inline-block; background: #dfdff0; color: #3e839a; border: 1px solid #3e839a;">'.
            '<h2 style="font-weight: 100; margin: 0;">'. $token .'</h2>'.
            '</div>'.
            '<br><br><br>'.
            '<small style="color: #8f8f8f">If this was a mistake, just ignore this email and nothing will happen, thank you.</small>'.
            '</body>'.
            '</htm>';
        return ($ret);
    }

    function ft_ms_verify_token($username, $token){
        $ret = '<html>'.
            '<body style="font-family: Tahoma, Geneva, Verdana, sans-serif; color: #525252;">'.
            '<p><b>Matcha - Verify Token</b></p>'.
            '<br>'.
            '<p>Hello <b>'. $username .'</b>.</p>'.
            '<p>Your token is:</p>'.
            '<div style="padding: 10px 15px; display: inline-block; background: #dfdff0; color: #3e839a; border: 1px solid #3e839a;">'.
            '<h2 style="font-weight: 100; margin: 0;">'. $token .'</h2>'.
            '</div>'.
            '<br><br><br>'.
            '<small style="color: #8f8f8f">If this was a mistake, just ignore this email and nothing will happen, thank you.</small>'.
            '</body>'.
            '</htm>';
        return ($ret);
    }

    function ft_ms_report_user($username, $reporter){
        $ret = '<html>'.
            '<body style="font-family: Tahoma, Geneva, Verdana, sans-serif; color: #525252;">'.
            '<p><b>Matcha - User Report</b></p>'.
            '<br>'.
            '<p>Hello <b>'. $username .'</b>.</p>'.
            $reporter.' has reported you on some kind of case, so we advise you to stop doing whatever you\'ve been doing towards these victim.'.
            '<br><br><br>'.
            '<small style="color: #8f8f8f">If this was a mistake, just ignore this email and nothing will happen, thank you.</small>'.
            '</body>'.
            '</htm>';
        return ($ret);
    }
?>