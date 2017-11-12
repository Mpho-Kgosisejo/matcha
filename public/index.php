<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    
    require '../vendor/autoload.php';

    require '../src/functions/init.php';
    require '../src/classes/init.php';
    
    $app = new \Slim\App;
    $app->get('/test', function (Request $request, Response $response) {
        try{
            //$db = new Database();

            $res = User::register('kaygo', 'p.k.kaygo@gmail.coms', 'null');
            echo json_encode($res);
        }catch(Exception $exc){
            echo $exc->getMessage();
        }
    });

    $app->run();
?>