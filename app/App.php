<?php
namespace App;

use App\Exception\ContainerException;

class App
{
    private array $routes = [
        'GET' => [
            "/signin" => [ \App\Controller\UserController:: class, 'signIn'],
            "/NotFound" => [ \App\Controller\UserController:: class, 'getNotFound']

        ],
        'POST' => [

        ]
    ];

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;

    }

    public function run(): void {
        try {

        $handler = $this->route();

        if(is_array($handler)){
            list($obj, $method) = $handler;
            if(!is_object($obj)){

                    $obj = $this->container->get($obj);


            }
            $response = $obj->$method();

        } else{
            $response = $handler();
        }

        list($view,$params) = $response;
        extract($params);


        ob_start();

        include $view;
        $content = ob_get_contents();
        $layout = file_get_contents('./views/layout.html');
        $result = str_replace('{content}', $content, $layout);

        ob_get_clean();

        echo $result;

    } catch (\Throwable $e) {

           $message = "'{$e->getMessage()} in {$e->getFile()} on {$e->getLine()} line' ";

           $logger = $this->container->get(LoggerInterface::class);

           $logger->error($message);

           require_once "./public/views/NotFound.phtml";


        }
}

    private function route(): array|callable {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes[$method] as $pattern => $handler) {

            if (preg_match("#^$pattern#", $uri, $params)) {
                return $handler;
            }
        }

        return [ \App\Controller\UserController:: class, 'getNotFound'];

    }


    public function get(string $route, array|callable $handler): void {
        $this->routes['GET'][$route] = $handler;
    }

    public function post(string $route, array|callable $handler): void {
        $this->routes['POST'][$route] = $handler;

    }

    /*   private function doRouting(string $uri): string {
       if(preg_match("#/(?<route>[A-Za-z0-9-_]+)#", $uri, $matches ) and
           file_exists("./Controller/{$matches['route']}.php")) {

           return "./Controller/{$matches['route']}.php";

       }

       return "./views/NotFound.phtml";

   }
*/


}