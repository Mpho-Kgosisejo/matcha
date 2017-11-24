<?php
    class User extends Database{

        public function info($target){
            $error = 'Could not get user\'s information';
            $res = Config::get('response_format');
            new Database();

            if (!is_array($target) || empty($target))
                return (array());
            
            $target_key = array_keys($target)[0];
            $target_value = $target[$target_key];
            $session = null;

            if ($target_key !== 'id' && $target_key !== 'token')
                return (array());
            if (empty($target_value))
                return (array());
            if ($target_key === 'token'){
                if (($stmt = parent::select('tbl_login_session', array('session', '=', $target_value)))){
                    if (parent::getCount($stmt) == 1){
                        $session = parent::getRows($stmt)[0];
                    }
                }
            }

            if ($session)
                $target_value = $session->user_id;
            
            if (($stmt = parent::select('tbl_users', array('id', '=', $target_value)))){
                if (parent::getCount($stmt) == 1){
                    $_data = parent::getRows($stmt, 0)[0];

                    if ($session){
                        $_data['session'] = $session->session;
                    }

                    if (($images = parent::select('tbl_user_images', array('user_id', '=', $_data['id']), null, true))){
                        if ($images->rowCount > 0){
                            
                            foreach ($images->rows as $image){
                                if ($image['code'] == 1)
                                    $_data['img'.$image['code']] = $image;
                            }
                        }
                    }

                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'success');
                    $res = Config::response($res, 'data', $_data);
                    return ($res);
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function login($login, $password){
            $error = 'Could not log you in at this time, please wait 2 minutes or so and try again.';
            $res = Config::get('response_format');
            new Database();

            $where = array(
                'username', '=', strtolower($login),
                '||',
                'email', '=', strtolower($login)
            );

            if (($stmt = parent::select('tbl_users', $where))){
                if (parent::getCount($stmt) == 1){
                    $user = parent::getRows($stmt, 1)[0];
                    if (Hash::make($password, $user->salt) === $user->password){
                        if (($stmt = parent::select('tbl_login_session', array('user_id', '=', $user->id)))){
                            //Check if user has a login session, if not insert new one else update old one
                            $token = Hash::unique_key(84);

                            if (!parent::getCount($stmt)){
                                $input = array(
                                    'user_id' => $user->id,
                                    'session' => $token
                                );

                                if ((!parent::insert('tbl_login_session', $input)))
                                    return (Config::response($res, 'response/message', $error));
                            }else{
                                $where = array('user_id' ,'=', $user->id);
                                $input = array(
                                    'user_id' => $user->id,
                                    'session' => $token
                                );

                                if ((!parent::update('tbl_login_session', $input, $where)))
                                    return (Config::response($res, 'response/message', $error));
                            }

                            $inputHistory = array(
                                'user_id_from' => $user->id,
                                'user_id_to' => -1,
                                'action' => 'login'
                            );
                            parent::insert('tbl_user_history', $inputHistory);

                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'login success');
                            $data = (object)self::info(array('token' => $token));
                            $res = Config::response($res, 'data', $data->data);
                            return ($res);
                        }else
                            return (Config::response($res, 'response/message', $error));
                    }
                }
                return (Config::response($res, 'response/message', 'Username or password is incorrect.'));
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function register($fn, $ln, $username, $email, $password){
            $res = Config::get('response_format');
            new Database();

            //Check if user already has an account...
            if (($stmt = parent::select('tbl_users', array('username', '=', $username)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Username already taken.'));
                }
            }
            if (($stmt = parent::select('tbl_users', array('email', '=', $email)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Email already has an accoun.'));
                }
            }

            //Check if user already registered, and is yet to confirm registration...
            if (($stmt = parent::select('tbl_user_registrations', array('username', '=', $username)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Username already registered.'));
                }
            }
            if (($stmt = parent::select('tbl_user_registrations', array('email', '=', $email)))){
                if (parent::getCount($stmt) > 0){
                    return (Config::response($res, 'response/message', 'Email already registered.'));
                }
            }

            $token = Hash::unique_key(6);
            
            if (!ft_sendmail($email, ucwords($fn . ' ' . $ln), Config::get('app/name') . " - Registration Confirmation", ft_ms_register(ucwords($fn . ' ' . $ln), $token))){
                return (Config::response($res, 'response/message', 'could not email registration confirmation, please try again'));
            }

            $salt = Hash::salt(15);
            
            $input = array(
                'username' => strtolower($username),
                'email' => strtolower($email),
                'password' => Hash::make($password, $salt),
                'firstname' => ucwords($fn),
                'lastname' => ucwords($ln),
                'salt' => $salt,
                'token' => $token
            );

            if ((parent::insert('tbl_user_registrations', $input))){
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'You have successfully registered to '. Config::get('app/name') .'. Please check your email to confirm your registration.');
                return ($res);
            }
            return (Config::response($res, 'response/message', 'Could not register you at this time, please wait 5 minutes or so and try again.'));
        }

        public function logout($session){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($stmt = parent::select('tbl_login_session', $where))){
                if (parent::getCount($stmt) > 0){
                    if (parent::delete('tbl_login_session', $where)){
                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'logout success');
                        return ($res);
                    }
                }else
                    return (Config::response($res, 'response/message', 'Login session was not found'));
            }
            return (Config::response($res, 'response/message', 'Could not log you out'));
        }

        public function is_logged($session){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($stmt = parent::select('tbl_login_session', $where))){
                if (parent::getCount($stmt) > 0){
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'Logged in');
                    return ($res);
                }else
                    return (Config::response($res, 'response/message', 'Not logged in'));
            }
            return (Config::response($res, 'response/message', 'Could not check if you logged in'));
        }

        public function changepassword($username, $old_pass, $new_pass){
            $res = Config::get('response_format');
            new Database();

            $where = array('username', '=', $username);
            if (($data = parent::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    
                    if (Hash::make($old_pass, $user->salt) === $user->password){
                        $salt = Hash::salt(15);
                        $input = array(
                            'password' => Hash::make($new_pass, $salt),
                            'salt' => $salt
                        );

                        if (parent::update('tbl_users', $input, $where)){
                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'Password changed successfully');
                            return ($res);
                        }
                    }else
                        return (Config::response($res, 'response/message', 'Passwords do not match'));
                }else
                    return (Config::response($res, 'response/message', 'Username not found'));
            }
            return (Config::response($res, 'response/message', 'Could not change your password'));
        }

        public function resetpassword($username, $password, $token){
            $res = Config::get('response_format');
            new Database();

            $where = array('username', '=', $username);
            if (($data = parent::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    
                    if ($user->token === $token){
                        $salt = Hash::salt(15);
                        $input = array(
                            'password' => Hash::make($password, $salt),
                            'salt' => $salt
                        );

                        if (parent::update('tbl_users', $input, $where)){
                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'Reset successful');
                            parent::update('tbl_users', array('token' => ''), $where);
                            return ($res);
                        }
                    }
                }
                return (Config::response($res, 'response/message', 'Incorrect key'));
            }
            return (Config::response($res, 'response/message', 'Could not reset your password'));
        }

        public function confirm_registration($token){
            $res = Config::get('response_format');
            new Database();

            
            $where = array('token', '=', $token);
            if (($data = parent::select('tbl_user_registrations', $where, null, true))){
                if ($data->rowCount > 0){
                    $reg = (object)$data->rows[0];
                    $input = array(
                        'firstname' => $reg->firstname,
                        'lastname' => $reg->lastname,
                        'username' => $reg->username,
                        'email' => $reg->email,
                        'password' => $reg->password,
                        'salt' => $reg->salt
                    );

                    if (!parent::insert('tbl_users', $input))
                        return (Config::response($res, 'response/message', 'Could not confirm registration at this time, please try later'));
                    
                    parent::delete('tbl_user_registrations', $where);
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'Confirmation successful');
                    return ($res);
                }
                return (Config::response($res, 'response/message', 'Incorrect code'));
            }
            return (Config::response($res, 'response/message', 'Could not confirm registration'));
        }

        public function update_profile($session, $fn, $ln, $gender, $dob, $sexual_preference, $bio){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($data = parent::select('tbl_login_session', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_data = (object)$data->rows[0];

                    $where = array('id', '=', $user_data->user_id);
                    $input = array(
                        'firstname' => $fn,
                        'lastname' => $ln,
                        'gender' => $gender,
                        'date_of_birth' => $dob,
                        'sexual_preference' => $sexual_preference,
                        'biography' => $bio
                    );
                    if (parent::update('tbl_users', $input, $where)){
                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'Profile successfully updated');
                        return ($res);
                    }
                }
            }

            return (Config::response($res, 'response/message', 'Could not update your profile, try again.'));
        }

        public function upload_profile($session, $image, $code){
            $res = Config::get('response_format');
            new Database();

            $where = array('session', '=', $session);
            if (($data = parent::select('tbl_login_session', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_data = (object)$data->rows[0];

                    $where = array(
                        'user_id', '=', $user_data->user_id,
                        'AND',
                        'code', '=', $code
                    );
                    if (($data = parent::select('tbl_user_images', $where, null, true))){
                        $url =  'http://'.Config::get('app/url') .'/'. ft_save_profile_image($image);

                        $input = array(
                            'user_id' => $user_data->user_id,
                            'code' => $code,
                            'url' => $url
                        );

                        if ($data->rowCount > 0){
                            //Update...
                            $data = (object)$data->rows[0];
                            $file = '../'.$data->url;
                            if (file_exists($file))
                                unlink($file);

                            if (parent::update('tbl_user_images', $input, $where)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', 'Image upload success');
                                $res = Config::response($res, 'data', array('url' => $url));
                                return ($res);
                            }
                        }else{
                            //Insert...
                            if (parent::insert('tbl_user_images', $input)){
                                $res = Config::response($res, 'response/state', 'true');
                                $res = Config::response($res, 'response/message', array('url' => $url));
                                return ($res);
                            }
                        }
                    }
                }
            }

            return (Config::response($res, 'response/message', 'Could not upload image, try again.'));
        }
    }
?>