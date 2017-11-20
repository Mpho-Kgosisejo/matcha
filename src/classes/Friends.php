<?php
    class Friends extends Database{
        public function suggestions($user_id){
            $error = 'Could\' not get suggestions at this time, please try again in few minutes';
            $res = Config::get('response_format');
            new Database();

            return (Config::response($res, 'response/message', $error));
        }
    }
?>