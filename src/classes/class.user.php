<?php
    class User extends Database{

        public function login($login, $password){
            
        }

        public function register($username, $email, $password){
            $res = $GLOBALS['api_config']['response_format'];
            new Database();

            if (($stmt = parent::select('tbl_users', array('username', '=', $username)))){
                if (parent::getCount($stmt) > 0){
                    $res['response']['message'] = 'username already taken';
                    return ($res);
                }
            }
            if (($stmt = parent::select('tbl_users', array('email', '=', $email)))){
                if (parent::getCount($stmt) > 0){
                    $res['response']['message'] = 'email already has an account';
                    return ($res);
                }
            }

            if (($stmt = parent::select('tbl_user_registrations', array('username', '=', $username)))){
                if (parent::getCount($stmt) > 0){
                    $res['response']['message'] = 'username already registered';
                    return ($res);
                }
            }
            if (($stmt = parent::select('tbl_user_registrations', array('email', '=', $email)))){
                if (parent::getCount($stmt) > 0){
                    $res['response']['message'] = 'email already registered';
                    return ($res);
                }
            }
            return ($res);
        }
    }
?>