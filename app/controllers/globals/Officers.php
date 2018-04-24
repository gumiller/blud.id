<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Officers extends CI_Controller {
    private $table          = 'officers';
    private $primary        = 'officerID';
    private $defaultorder   = 'officerName';
    private $structures     = [
        'officerID'         => ['field' => 'officerID', 'default' => 'UNIQ'],
        'agencyID'          => ['field' => 'agencyID', 'default' => ''],
        'secretKey'         => ['field' => 'secretKey', 'default' => 'SECRET'],
        'officerName'       => ['field' => 'officerName', 'default' => ''],
        'officerUsername'   => ['field' => 'officerUsername', 'default' => ''],
        'officerPassword'   => ['field' => 'officerPassword', 'default' => 'PASSWORD'],
        'officerStatus'     => ['field' => 'officerStatus', 'default' => 'Activated']
    ];
    private $dependencies   = [
        ['table' => 'officerlogs', 'field' => 'officerID'],
        ['table' => 'officeraccesses', 'field' => 'officerID']
    ];

    public function __constructGET() {
        parent::__construct();
        $this->core->auth();
    }
    
	public function index() {
        if ($this->core->method() == 'get'):
            $this->core->_get($this->structures, $this->defaultorder, $this->table);
            //For your own function of GET Method use : $this->get();
        elseif ($this->core->method() == 'post'):
            //For your own function of POST Method use : $this->post();
            $this->core->_post($this->structures, $this->table, $this->primary);
        elseif ($this->core->method() == 'put'):
            //For your own function of PUT Method use : $this->put();
            $this->core->_put($this->structures, $this->table, $this->primary);
        elseif ($this->core->method() == 'delete'):
            //For your own function of DELETE Method use : $this->delete();
            $this->core->_delete($this->dependencies, $this->table, $this->primary);
        else:
			$this->core->err_method();
        endif;
    }
    
    private function get() {
        //Here your own GET Method code
        //Use $this->userlogs(); after requested succesfully
    }
    
    private function post() {
        //Here your own POST Method code
        //Use $this->userlogs(); after requested succesfully
    }
    
    private function put() {
        //Here your own PUT Method code
        //Use $this->userlogs(); after requested succesfully
    }
    
    private function delete() {
        //Here your own DELETE Method code
        //Use $this->userlogs(); after requested succesfully
    }
}