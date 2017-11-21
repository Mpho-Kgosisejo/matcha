<?php
    $GLOBALS['api_config'] = array(
        'server' => array(
            'host' => 'localhost',
            'db_user' => 'root',
            'db_password' => '',
            'db_name' => 'matcha'
        ),
        'app' => array(
            'url' => 'localhost:8383/my_sites/matcha',
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