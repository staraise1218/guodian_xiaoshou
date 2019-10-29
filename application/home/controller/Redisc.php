<?php

namespace app\home\controller;
use think\Db;
use Resque;
use app\queue\DemoJob;


class Redisc extends Base {
    
    public function _initialize() {
        parent::_initialize();
    }

	public function redis(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        echo "Server is running: " . $redis->ping();
    }

    public function info(){
        echo phpinfo();
    }

public function pushjob(){
        Resque::setBackend('localhost:6379');
        $args = array(
            'name' => 'hanmeimei',
        );
        echo Resque::enqueue('default', DemoJob::class, $args);
    }
public function settrie(){
        $arrWord = array('word1', 'word2', 'word3');

        $resTrie = new \trie_filter_new();
        foreach ($arrWord as $k => $v) {
            trie_filter_store($resTrie, $v);
        }
        trie_filter_save($resTrie, __DIR__ . '/blackword.tree');
    }
}