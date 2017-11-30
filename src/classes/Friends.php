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
            $query = "SELECT * FROM tbl_users";
            //$query = "SELECT * FROM tbl_users WHERE username LIKE '%go%' OR firstname LIKE '%go%' OR lastname LIKE '%go%'";
            $stmt = $conn->prepare($query);
            //$stmt->bindparam(':un', "%{$value}%");
            //$stmt->bindparam(':fn', "%{$value}%");
            //$stmt->bindparam(':ln', "%{$value}%");

            if (!$stmt->execute())
                return (Config::response($res, 'response/message', $error));
            $rows = parent::getRows($stmt);
            $res = Config::response($res, 'response/state', 'true');
            $res = Config::response($res, 'response/message', 'Search success');
            $res = Config::response($res, 'data', $rows);
            return ($res);
            //print_r($rows);

            return (Config::response($res, 'response/message', $error));
        }

        public function invite($user_session, $to_id){
            $error = 'Could not invite user.';
            $res = Config::get('response_format');
            new Database();

            $user_from_info = User::info(array('token' => $user_session));
            if ($user_from_info['response']['state'] === 'true'){
                $user_from_info = (object)$user_from_info['data'];
                $where = array(
                    'user_id_from', '=', $user_from_info->id,
                    'AND',
                    'user_id_to', '=', $to_id 
                );
                if (($data = parent::select('tbl_user_connections', $where, null, true))){
                    $inputHistory = array(
                        'user_id_from' => $user_from_info->id,
                        'user_id_to' => $to_id
                    );

                    if ($data->rowCount == 0){
                        //Users have no connection, add it
                        $input = array(
                            'user_id_from' => $user_from_info->id,
                            'user_id_to' => $to_id
                        );

                        if (parent::insert('tbl_user_connections', $input)){
                            $inputHistory['action'] = 'connect';
                            parent::insert('tbl_user_history', $inputHistory);

                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'connected');
                            return ($res);
                        }
                    }else{
                        //Users have connection... so unconnect them
                        $users_friendship = (object)$data->rows[0];
                        
                        if ($users_friendship->status == 0){
                            //Remove it connection...
                            //echo 'Delete<br><br>';
                            if (parent::delete('tbl_user_connections', $where)){
                                $inputHistory['action'] = 'unconnect';
                                parent::insert('tbl_user_history', $inputHistory);

                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'unconnected');
                                return ($res);
                            }else
                                return (Config::response($res, 'response/message', "Could not unconnect with user"));
                        }else{
                            //Update connection status...
                            //echo 'Update<br><br>';
                            $updates = array(
                                'status' => 0
                            );

                            if (parent::update('tbl_user_connections', $updates, $where)){
                                $inputHistory['action'] = 'unconnect';
                                parent::insert('tbl_user_history', $inputHistory);

                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'unconnected');
                                return ($res);
                            }else
                                return (Config::response($res, 'response/message', "Could not unconnect with user"));
                        }
                    }
                }
            }else
                return (Config::response($res, 'response/message', $error.' Incorrect user session.'));

            return (Config::response($res, 'response/message', $error));
        }

        public function accept_invite($user_session, $from_id){
            $error = 'Could not accept invite';
            $res = Config::get('response_format');
            new Database();

            $user_from_info = User::info(array('token' => $user_session));
            if ($user_from_info['response']['state'] === 'true'){
                $user_from_info = (object)$user_from_info['data'];
                $query = "SELECT * FROM tbl_user_connections WHERE (user_id_from = $user_from_info->id AND user_id_to = $from_id) OR (user_id_from = $from_id AND user_id_to = $user_from_info->id);";

                //if (($data = parent::select('tbl_user_connections', $where, null, true))){
                if (($data = parent::rawQuery($query, true))){
                    $data = (object)$data;
                    if ($data->rowCount > 0){
                        $data = (object)$data->rows[0];
                        $where = array(
                            'id', '=', $data->id
                        );
                        
                        if ($data->status == 0){
                            //No connection... make connection
                            $input = array(
                                'status' => 1
                            );

                            if (parent::update('tbl_user_connections', $input, $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'connected');
                                return ($res);
                            }
                        }else{
                            //Connection... remove connection
                            $input = array(
                                'status' => 0
                            );

                            if (parent::delete('tbl_user_connections', $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'unconnected');
                                return ($res);
                            }
                        }
                    }else
                        return (Config::response($res, 'response/message', 'User has not sent you a connection'));
                }
            }

            return (Config::response($res, 'response/message', $error));
        }

        public function _list($user_session){
            $error = 'Could not get connetion list.';
            $res = Config::get('response_format');
            new Database();

            $user_from_info = User::info(array('token' => $user_session));
            if ($user_from_info['response']['state'] === 'true'){
                $user_from_info = (object)$user_from_info['data'];

                $query = "SELECT *, CAST(tbl_users.id AS UNSIGNED) AS 'user_id' 
                            FROM tbl_users, tbl_user_connections 
                            WHERE (user_id_from = tbl_users.id || user_id_to = tbl_users.id) AND (user_id_from = $user_from_info->id || user_id_to = $user_from_info->id) ORDER BY username;";
                //FROM tbl_user_images WHERR tbl_user_images.id = tbl_users.id AND tbl_user_images.code = 1 AND
                if (($data = parent::rawQuery($query, true))){
                    $data = (object)$data;
                    if ($data->rowCount > 0){
                        $new_data = array();

                        foreach ($data->rows as $d){     
                            $query = "SELECT url FROM tbl_user_images, tbl_users WHERE tbl_user_images.user_id = tbl_users.id AND tbl_users.id = ". $d['user_id'] ." AND tbl_user_images.code = 1;";
                            if (($data_imgs = parent::rawQuery($query, true))){
                                $data_imgs = (object)$data_imgs;
                                if ($data_imgs->rowCount > 0){
                                    $data_imgs = (object)$data_imgs->rows[0];
                                    $d['profile_url'] = $data_imgs->url;
                                }
                            }
                            $new_data[] = $d;
                        }

                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', $data->rowCount);
                        $res = Config::response($res, 'data', $new_data);
                        return ($res);
                    }else
                        return (Config::response($res, 'response/message', 'No connections yet'));
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function block($session, $id){
            $error = 'Could\' not block user, please try again in few minutes';
            $res = Config::get('response_format');

            if (($data = User::info(array('token' => $session)))){
                print_r($data);
                echo $session.' - '.$id;
                $where = array(
                );
            }
            return (Config::response($res, 'response/message', $error));
        }
    }
?>