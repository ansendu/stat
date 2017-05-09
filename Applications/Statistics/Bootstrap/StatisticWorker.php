<?php
namespace Bootstrap;

use Workerman\Worker;

class StatisticWorker extends Worker
{

    public function __construct($socket_name)
    {
        parent::__construct($socket_name);
        $this->onWorkerStart = array($this, 'onStart');
        $this->onMessage = array($this, 'onMessage');
        $this->onWorkerStop = array($this, 'onStop');
    }

    public function onMessage($connection, $data)
    {
        $data['extra'] = json_decode($data['extra'], true);

        if (!isset($data['extra']['_module'])) {
            return;
        }

        if ($data['extra']['_module'] == 'api') {
            unset($data['extra']['_module']);
            Api::onMessage($connection, $data);
            return;
        }
        return;
    }

    protected function onStart()
    {
        Api::onStart();
    }

    protected function onStop()
    {
        Api::onStop();

    }
} 
