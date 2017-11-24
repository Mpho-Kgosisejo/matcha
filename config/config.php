<?php
    $GLOBALS['api_config'] = array(
        'server' => array(
            'host' => 'localhost',
            'db_user' => 'root',
            'db_password' => '123456',
            'db_name' => 'matcha'
        ),
        'app' => array(
            //NB*! url without "http://"
            'url' => '127.0.0.1:8080/matcha',
            'name' => 'Matcha',
            'email' => '',
            'email_password' => '',
            'salt' => '8cd8aa091d721adbdc',
            'author' => 'Mpho Kgosisejo'
        ),
        'paths' => array(
            'profile_uploads' => 'uploads/profiles'
        ),
        'response_format' => array(
            "response" => array(
                "state" => "false",
                "message" => ""
            ),
            "data" => ""
        )
    );
?>