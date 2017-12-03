<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    header('Access-Control-Allow-Origin: *');
    /* *
    header('Access-Control-Allow-Methods: *');
    header('Content-Type: application/json');
    /* */

    require '../vendor/autoload.php';

    require '../config/config.php';
    require '../src/functions/init.php';
    require '../src/classes/init.php';
    
    $app = new \Slim\App;
    $app->get('/test', function (Request $request, Response $response) {
        try{
            $res = Friends::invite('qRSBY1Y6xYojnoyjvXq8aevu5qhLqTiRLasJcrQJpf2MEB69ywhILMdQduCDoUu95XThoJ1NZ3ADFHMdd4WZ', 2);
            echo json_encode($res);
            
            //print_r(Config::get('response_format/response'));
            /*$ret = Config::response(Config::response(), 'response/state', 'true');
            print_r(Config::response($ret, 'response/message', 'Ok... test works'));*/
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    $app->get('/profile', function (Request $request, Response $response) {
        $input = ft_escape_array($request->getParsedBody());
        //$input['username'] = 'mkgosisejo';
        //$input['session'] = '0i2ljuJrrJPRSOeo1mJNQzvZg35scXPdgRzAli1M1QEUFTiHf1u6BZ5S3akf89to02YmlZ9nQNhwHAdWCH3d';

        if (isset($input['username'])){
            $db = new Database();

            //run raw query "WHERE blocked_user.id != id..."
            if (($data = $db->select('tbl_users', array('username', '=', $input['username']), null, true))){
                if ($data->rowCount){
                    $data = $data->rows[0];
                    //$images_data = ''; //#Remove...!

                    //Appending users Photos on user's info...
                    //echo $query = "SELECT * FROM tbl_user_images WHERE user_id = ".$data['id'].";";
                    if (($images = $db->select('tbl_user_images', array('user_id', '=', $data['id']), null, true))){
                        if ($images->rowCount > 0){
                            $data['images'] = $images->rows;
                            
                            foreach ($images->rows as $image){
                                $data['img'.$image['code']] = $image;
                            }
                        }
                    }

                    //Appending Friendship info of logged user with viewed user if not him/her self (logged user)...
                    if (isset($input['session'])){
                        if ($logged_user = User::info(array('token' => $input['session']))){
                            $logged_user = (object)$logged_user['data'];
                            $viewed_user = (object)$data;

                            if ($logged_user->id !== $viewed_user->id){
                                $query = "SELECT * FROM tbl_user_connections WHERE (user_id_from = $logged_user->id AND user_id_to = $viewed_user->id) OR (user_id_from = $viewed_user->id AND user_id_to = $logged_user->id);";

                                if (($conn_data = Database::rawQuery($query, true))){
                                    $conn_data = (object)$conn_data;
                                    if ($conn_data->rowCount > 0){
                                        $conn_data = (object)$conn_data->rows[0];
                                        $relationship['status'] =  $conn_data->status;
                                        $relationship['user_id_from'] = $conn_data->user_id_from;
                                        $relationship['user_id_to'] = $conn_data->user_id_to;
                                        
                                        $data['relationship'] = $relationship;
                                    }
                                }
                            }
                        }
                    }

                    $res = Config::response(Config::response(), 'response/state', 'true');
                    $res = Config::response($res, 'data', $data);
                    echo json_encode($res);
                    return ;
                }
                echo json_encode(Config::response(Config::response(), 'response/message', $input['username'] .' was not found.'));
                return ;
            }
        }
        echo '{}';
    });

    $app->get('/suggestions', function (Request $request, Response $response) {
        try{
            $input = ft_escape_array($request->getParsedBody());

            print_r($input);
            //$res = friends::suggestions(2);
            //echo json_encode($res);
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    $app->get('/info', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['session'])){
            $res = User::info(array('token' => $input['session']));
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/update-profile', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['session']) && isset($input['fname']) && isset($input['lname']) && isset($input['gender']) &&
                isset($input['dob']) && isset($input['sexual_preference']) && isset($input['bio'])){

            $res = User::update_profile($input['session'], $input['fname'], $input['lname'], $input['gender'], $input['dob'], $input['sexual_preference'], $input['bio'], $input['address']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/login', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (!isset($input['isSession']))
            echo '{}';
        if ($input['isSession'] == 1){
            if (isset($input['session'])){
                $res = User::info(array('token' => $input['session']));
                echo json_encode($res);
            }
        }
        else {
            if (isset($input['login']) && isset($input['password'])){
                $res = User::login($input['login'], $input['password']);
                echo json_encode($res);
            }
        }
    });

    $app->post('/profile-images', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        
        if (isset($input['session']) && isset($input['image']) && isset($input['code'])){
            $res = User::upload_profile($input['session'], $input['image'], $input['code']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/register', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['fname']) && isset($input['lname']) && isset($input['username']) && isset($input['email']) && isset($input['password'])){
            $res = User::register($input['fname'], $input['lname'], $input['username'], $input['email'], $input['password']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->get('/logut', function (Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());

        if (isset($input['session'])){
            $res = User::logout($input['session']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/confirm-registration', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        
        if (isset($input['token'])){
            $res = User::confirm_registration($input['token']);
            //$ret = Config::response(Config::response(), 'response/state', 'true');
           // $res = Config::response($ret, 'response/message', 'Ok... test works');
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/search', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['search_value'] = 'luiez';
        
        if (isset($input['search_value'])){
            $res = Friends::search($input['search_value']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/invite', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
        //$input['session'] = 'OeWPVBOI1SfqgEp9UYQjOg4C1hBKeBQ2QMSMoHvqAKRRpg0jeQC26HF8YgSdSIgJv9vUQ0krLciasiuG97Jg';
        //$input['username'] = 'pkaygo';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_to = (object)$data->rows[0];
                    $res = Friends::invite($input['session'], $user_to->id);
                    echo json_encode($res);
                    return ;
                }
            }
            echo json_encode(Config::response(Config::response(), 'response/message', 'Selected user was not found.'));
        }else
            echo '{}';
    });

    $app->post('/accept-invite', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        new Database();
        //$input['session'] = '0i2ljuJrrJPRSOeo1mJNQzvZg35scXPdgRzAli1M1QEUFTiHf1u6BZ5S3akf89to02YmlZ9nQNhwHAdWCH3d';
        //$input['username'] = 'mkgosisejo';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user_to = (object)$data->rows[0];
                    $res = Friends::accept_invite($input['session'], $user_to->id);
                    echo json_encode($res);
                    return ;
                }
            }
            echo json_encode(Config::response(Config::response(), 'response/message', 'Selected user was not found.'));
        }else
            echo '{}';
    });

    $app->get('/friend-list', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        //$input['session'] = '0i2ljuJrrJPRSOeo1mJNQzvZg35scXPdgRzAli1M1QEUFTiHf1u6BZ5S3akf89to02YmlZ9nQNhwHAdWCH3d';

        if (isset($input['session'])){
            $res = Friends::_list($input['session']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->get('/block-user', function(Request $request, Response $response){
        //$input = ft_escape_array($request->getParsedBody());
        new Database();
        $input['session'] = 'XMq6SH7pkf3zUZzT8KdlC0D2jvhfklLi7bo0TvpdLtNFsnyB7MWJMzM653lqo6Iwi6xbCXpnqd3TACuIorrb';
        $input['username'] = 'mkgosise';

        if (isset($input['session']) && isset($input['username'])){
            $where = array(
                'username', '=', $input['username']
            );
            if (($data = Database::select('tbl_users', $where, null, true))){
                if ($data->rowCount > 0){
                    $user = (object)$data->rows[0];
                    $res = Friends::block($input['session'], $user->id);
                    echo json_encode($res);
                }
            }
        }else
            echo '{}';
    });

    $app->post('/get-chat', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $db = new Database();
        $res = Config::get('response_format');
        $conn = $db->connection();
         
        if (isset($input['other_id']) && isset($input['user_id'])){
            $query = "SELECT * FROM tbl_user_messages WHERE (user_id_from = :from || user_id_to = :from) AND (user_id_from = :to || user_id_to = :to) ORDER BY date_created DESC;";
            $stmt = $conn->prepare($query);
            $stmt->bindparam(':from', $input['other_id']);
            $stmt->bindparam(':to', $input['user_id']);

            if ($stmt->execute()){
                if ($db->getCount($stmt) > 0){
                    $res = Config::response($res, 'response/state', 'true');
                    $res = Config::response($res, 'response/message', 'records:'.$db->getCount($stmt));
                    echo json_encode(Config::response($res, 'data', $db->getRows($stmt)));
                    return ;
                }
            }
            echo json_encode(Config::response($res, 'response/message', 'records:0'));
        }else
            echo '{}';
    });

    $app->post('/send-message', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $db = new Database();
        $res = Config::get('response_format');
        $conn = $db->connection();
         
        if (isset($input['from']) && isset($input['to']) && isset($input['mssg'])){
            $input = array(
                'user_id_from' => $input['from'],
                'user_id_to' => $input['to'],
                'message' => $input['mssg']
            );
            
            if ($db->insert('tbl_user_messages', $input)){
                $res = Config::response($res, 'response/state', 'true');
                $res = Config::response($res, 'response/message', 'success');
                echo json_encode($res);
                return ;
            }
            echo json_encode(Config::response($res, 'response/message', 'error'));
        }else
            echo '{}';
    });

    $app->post('/add-tag', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
         
        if (isset($input['tag']) && isset($input['user'])){
            $res = User::add_tag($input['tag'], $input['user']);
            $tags = User::tags($input['user']);
            if ($res['response']['state'] == 'true' && $tags['response']['state'] == 'true')
                $res['data'] = $tags['data'];
            echo json_encode($res);
        }else
            echo '{}';
    });

    /*
    $app->post('/get-tags', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
         
        if (isset($input['user'])){
            $res = User::tags($input['user']);
            echo json_encode($res);
        }else
            echo '{}';
    });
    */

    $app->post('/delete-tag', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        
        $res = Config::get('response_format');
        new Database();
         
        $where = array(
            'interest_id', '=', $input['id'],
            'AND',
            'user_id', '=', $input['userid']
        );
        if (isset($input['userid']) && isset($input['id'])){
            if (($data = Database::select('tbl_user_interests', $where, null, true))){
                if ($data->rowCount > 0){
                    if (Database::delete('tbl_user_interests', $where)){
                        //Removing tag...
                        $tag = (object)$data->rows[0];
                        $where = array(
                            'interest_id', '=', $tag->interest_id
                        );
                        if (($data = Database::select('tbl_user_interests', $where, null, true))){
                            if ($data->rowCount == 0){
                                $where = array(
                                    'id', '=', $tag->interest_id
                                );
                                Database::delete('tbl_interests', $where);
                            }
                        }

                        $res = Config::response($res, 'response/state', 'true');
                        $res = Config::response($res, 'response/message', 'success');

                        $tags = User::tags($input['userid']);
                        if ($res['response']['state'] == 'true' && $tags['response']['state'] == 'true')
                            $res['data'] = $tags['data'];
                        echo json_encode($res);
                        return ;
                    }
                }
                echo json_encode(Config::response($res, 'response/message', 'Tag not found'));
            }
        }else
            echo '{}';
    });

    $app->post('/track-user', function(Request $request, Response $response){
        $input = ft_escape_array($request->getParsedBody());
        $res = Config::get('response_format');
        //$input['session'] = 'nIh7CcwjIxb3rbr4tk269mT6WXlMceUzWcGzodS6L39cfBhJWjQ5FJcCHyoTMjsJv9jTnc08gakwLBfHV5NB';
        //$input['location'] = "Maf";
        
        if (isset($input['session']) && isset($input['location'])){
            if (($user = (object)User::info(array('token' => $input['session'])))){
                if ($user->response['state'] == 'true'){
                    $user = (object)$user->data;
                    $res = User::track($user->id, $input['location']);
                    echo json_encode($res);
                    return ;
                }
            }
            echo json_encode(Config::response($res, 'response/message', 'Could not track user'));
        }else
            echo '{}';
    });

    $app->run();
?>