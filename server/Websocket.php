<?php

namespace Server;

use Laravel\Lumen\Application;

define('ROOT_PATH', str_replace('server','', __DIR__));

class Websocket {

    public $server = null;

    public function __construct(string $host, int $port)
    {
        $this->server = new \swoole_websocket_server($host, $port);

        $documentRoot = ROOT_PATH. "public";

        $this->server->set([
            'enable_static_handler' => true, //开启静态资源
            'document_root'         => $documentRoot, //静态资源目录
            'worker_num'            => 4, //worker数
            'task_worker_num'       => 4, //task 数
        ]);

        $this->server->on("start", [$this, 'onStart']);
        $this->server->on("open", [$this, 'onOpen']);
        $this->server->on("close", [$this, 'onClose']);
        $this->server->on("message", [$this, 'onMessage']);
        $this->server->on("workerstart", [$this, 'onWorkerStart']);
        $this->server->on("request", [$this, 'onRequest']);
        $this->server->on("task", [$this, 'onTask']);
        $this->server->on("finish", [$this, 'onFinish']);
        $this->server->start();
    }

    public function onStart(\Swoole\WebSocket\Server $server)
    {
        //设置进程名称
        swoole_set_process_name("swoole_lumen");
    }

    public function onOpen($server, $request)
    {
        print_r("open:{$request->fd}");
    }

    public function onClose(\Swoole\WebSocket\Server $server, $fd) {
        print_r("close:{$fd}");
    }

    public function onMessage($server, $frame)
    {

    }

    public function onWorkerStart(\Swoole\WebSocket\Server $server, $workerId)
    {
        //载入框架
        require ROOT_PATH. 'bootstrap/app.php';
    }


    private function init($request, $response)
    {
        //初始化每次请求的数据
        $_GET = $_POST = $_SERVER = $_FILES = [];

        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }

        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }

        $_POST['swoole_server'] = $this->server;

    }


    public function onRequest($request, $response)
    {

        $this->init($request, $response);
        ob_start();

        try {

            //运行lumen框架
            Application::getInstance()->run();

        } catch (\Exception $e) {

            echo $e->getMessage();
        }

        $data = ob_get_contents();
        ob_end_clean();
        $response->end($data);

    }

    public function onTask($server)
    {

    }
    public function onFinish()
    {

    }

}

new Websocket('0.0.0.0', 8888);