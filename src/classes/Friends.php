<?php
    class Friends extends Database{
        public function suggestions($user_id){
            $error = 'Could\' not get suggestions at this time, please try again in few minutes';
            $res = Config::get('response_format');
            new Database();

            return (Config::response($res, 'response/message', $error));
        }

        public function search($value){
            $error = 'Could\' not get search results';
            $res = Config::get('response_format');
            $db = new Database();
            $conn = $db->connection();

            //$query = "SELECT * FROM tbl_users WHERE username LIKE :un OR firstname LIKE :fn OR lastname LIKE :ln;";
            $query = "SELECT * FROM tbl_users WHERE username LIKE '%go%' OR firstname LIKE '%go%' OR lastname LIKE '%go%'";
            $stmt = $conn->prepare($query);
            //$stmt->bindparam(':un', "%{$value}%");
            //$stmt->bindparam(':fn', "%{$value}%");
            //$stmt->bindparam(':ln', "%{$value}%");

            if (!$stmt->execute())
                return (Config::response($res, 'response/message', $error));
            $rows = parent::getRows($stmt);
            $res = Config::response($res, 'response/state', 'true');
            $res = Config::response($res, 'response/message', 'ok');
            $res = Config::response($res, 'data', $rows);
            return ($res);
            //print_r($rows);

            return (Config::response($res, 'response/message', $error));
        }
    }
?>