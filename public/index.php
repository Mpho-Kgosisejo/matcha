<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    
    require '../vendor/autoload.php';

    require '../config/config.php';
    require '../src/functions/init.php';
    require '../src/classes/init.php';
    
    $app = new \Slim\App;
    $app->get('/test', function (Request $request, Response $response) {
        try{
            
            $res = User::login('mkgosise', 'password');
            echo json_encode($res);

            //print_r(Config::get('response_format/response/state'));
            //print_r(Config::response(Config::response(), 'response/state', 'kjkjk'));
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    $app->run();
?>