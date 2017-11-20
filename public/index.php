<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    header('Access-Control-Allow-Origin: *');
    /*header('Access-Control-Allow-Methods: *');
    header('Content-Type: application/json');
    */

    require '../vendor/autoload.php';

    require '../config/config.php';
    require '../src/functions/init.php';
    require '../src/classes/init.php';
    
    $app = new \Slim\App;
    $app->get('/test', function (Request $request, Response $response) {
        try{

            //$res = User::confirm_registration('xaBKxSHKO6');
            
            $res = Friends::suggestions(2);
            //$res = User::register('Mpho', 'Kgosisejo', 'mkgosise2', 'smpho.kgosisejo@hotmail.com', '123456');
            echo json_encode($res);
            
            //print_r(Config::get('response_format/response/state'));
            /*$ret = Config::response(Config::response(), 'response/state', 'true');
            print_r(Config::response($ret, 'response/message', 'Ok... test works'));*/
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    $app->get('/profile', function (Request $request, Response $response) {
        $input = $request->getParsedBody();
        //$input['username'] = 'mkgosise';

        if (isset($input['username'])){
            $db = new Database();

            //run raw query "WHERE blocked_user.id != id..."
            if (($data = $db->select('tbl_users', array('username', '=', $input['username']), null, true))){
                if ($data->rowCount){
                    $data = $data->rows;
                    $res = Config::response(Config::response(), 'response/state', 'true');
                    $res = Config::response($res, 'data', $data[0]);
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
            $input = $request->getParsedBody();

            print_r($input);
            //$res = friends::suggestions(2);
            //echo json_encode($res);
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    /*$app->post('/userinfo', function (Request $request, Response $response){
        $input = $request->getParsedBody();
    });*/

    $app->post('/login', function (Request $request, Response $response){
        $input = $request->getParsedBody();

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

    $app->post('/register', function (Request $request, Response $response){
        $input = $request->getParsedBody();

        if (isset($input['fname']) && isset($input['lname']) && isset($input['username']) && isset($input['email']) && isset($input['password'])){
            $res = User::register($input['fname'], $input['lname'], $input['username'], $input['email'], $input['password']);
            //$ret = Config::response(Config::response(), 'response/state', 'true');
            //$res = Config::response($ret, 'response/message', 'Ok... test works');
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->get('/logut', function (Request $request, Response $response){
        $input = $request->getParsedBody();

        if (isset($input['session'])){
            $res = User::logout($input['session']);
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->post('/confirm-registration', function(Request $request, Response $response){
        $input = $request->getParsedBody();
        
        if (isset($input['token'])){
            $res = User::confirm_registration($input['token']);
            //$ret = Config::response(Config::response(), 'response/state', 'true');
           // $res = Config::response($ret, 'response/message', 'Ok... test works');
            echo json_encode($res);
        }else
            echo '{}';
    });

    $app->run();
?>