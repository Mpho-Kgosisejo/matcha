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

            if ($target_key !== 'id' && $target_key !== 'token' && empty($target_value))
                return (array());
            if ($target_key === 'token'){
                if (($stmt = parent::select('tbl_login_session', array('token', '=', $target_value)))){
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
                        $_data['session'] = $session->token;
                    }
                    unset($_data['salt']);
                    
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'success');
                    $res = Config::response($res, 'data', $_data);
                    return ($res);
                }
            }
            return (Config::response($res, 'response/message', $error));
        }

        public function login($login, $password){
            $error = 'Could not register you at this time, please wait 5 minutes or so and try again.';
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
                                    'token' => $token
                                );

                                if ((!parent::insert('tbl_login_session', $input)))
                                    return (Config::response($res, 'response/message', $error));
                            }
                            else{
                                $where = array('user_id' ,'=', $user->id);
                                $input = array(
                                    'user_id' => $user->id,
                                    'token' => $token
                                );

                                if ((!parent::update('tbl_login_session', $input, $where)))
                                    return (Config::response($res, 'response/message', $error));
                            }

                            $res = Config::response($res, 'response/state', 'true');
                            $res = Config::response($res, 'response/message', 'login success');
                            $res = Config::response($res, 'data', self::info(array('token' => $token)));
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

            $token = Hash::unique_key();

            /*if (!ft_sendmail('', '', '')){
                return (Config::response($res, 'response/message', 'could not email registration confirmation, please try again'));
            }*/

            $salt = Hash::salt(15);
            $input = array(
                'username' => strtolower($username),
                'email' => strtolower($email),
                'password' => Hash::make($password, $salt),
                'firstname' => $fn,
                'lastname' => $ln,
                'salt' => $salt,
                'token' =>$token
            );

            if ((parent::insert('tbl_user_registrations', $input))){
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'You have successfully registered to '. Config::get('app/name') .'. Please check your email to confirm your registration.');
                return ($res);
            }
            return (Config::response($res, 'response/message', 'Could not register you at this time, please wait 5 minutes or so and try again.'));
        }
    }
?>