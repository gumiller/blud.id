<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* 
 * 100 = Continue
 * 101 = Switching Protocols
 * 102 = Processing
 * 200 = OK
 * 201 = Created
 * 202 = Accepted
 * 203 = Non-Authoritative Information
 * 204 = No Content
 * 205 = Rest Content
 * 206 = Partial Contennt
 * 207 = Multi-Status
 * 208 = Already Reported
 * 300 = Multiple Choices
 * 301 = Moved Permanently
 * 302 = Found
 * 303 = See Other
 * 304 = Not Modified
 * 305 = Use Proxy
 * 306 = Switch Proxy
 * 307 = Temporary Redirect
 * 308 = Permanent Redirect
 * 400 = Bad Request
 * 401 = Unauthorized
 * 402 = Payment Required
 * 403 = Forbidden
 * 404 = Not Found
 * 405 = Method Not Allowed
 * 406 = Not Acceptable
 * 407 = Proxy Authentication Required
 * 408 = Request Timeout
 * 409 = Conflict
 * 410 = Gone
 * 411 = Length Required
 * 412 = Precondition Faild
 * 413 = Request Entity Too Large
 * 414 = Request-URI Too Long
 * 415 = Unsupported Media Type
 * 416 = Requested Range Not Satisfiable
 * 417 = Expectation Failed
 * 418 = I'm a teapot
 * 420 = Enchance Your Calm
 * 421 = Misdirected Request
 * 422 = Unprocessable Entity
 * 423 = Locked
 * 424 = Failed Dependenncy
 * 425 = Reserved for WebDAV
 * 426 = Upgrade Required
 * 428 = Precondition Required
 * 429 = Too Many Requests
 * 431 = Request Header Fields Too Large
 * 451 = Unavailable For Legal Reasons
 * 500 = Internal Server Error
 * 501 = Not Implemented
 * 502 = Bad Gateway
 * 503 = Service Unavailable
 * 504 = Gateway Timeout
 * 505 = HTTP Version Not Supported
 * 506 = Variant Also Negotiates
 * 507 = Insufficient Storage
 * 508 = Loop Detected
 * 510 = Not Extended
 * 511 = Network Authentication Required
 */

class Api extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
    }

	public function index() {
		$this->core->return([
			'application' 	=> api_name,
			'message' 		=> api_welcome,
			'documentation'	=> api_docs
		]);
	}
	
	public function getToken() {
		$header = $this->input->request_headers();
		if ($this->input->get_request_header('Secretkey') !== null && $this->input->get_request_header('Username') !== null && $this->input->get_request_header('Password') !== null):
			$token = $this->core->getToken($this->input->get_request_header('Secretkey'), $this->input->get_request_header('Username'), $this->input->get_request_header('Password'));
			if ($token !== false):
				$this->core->return(['status' => 'success', 'token' => $token]);
			else:
				$this->core->error('Invalid Argument Get Token');
			endif;
		else:
			$this->core->error('Invalid Argument Get Token');
		endif;
	}

	public function checkToken(){
        $this->core->return(['status' => 'success', 'valid' => (($this->core->auth())?true:false)]);
	}

	public function err404() {
		$this->core->return([
			'application' 	=> api_name,
			'message' 		=> '404 - Not Found',
			'documentation'	=> api_docs
        ]);
	}
	
	public function globals() {
        $this->core->return([
			'application' 	=> api_name,
			'message' 		=> api_welcome . ' - Globals',
			'format uri'	=> '{{SERVER}}/globals/[segment]',
			'documentation'	=> api_docs . '/globals'
        ]);
	}
	
	public function budgeting() {
        $this->core->return([
			'application' 	=> api_name,
			'message' 		=> api_welcome . ' - Budgeting',
			'format uri'	=> '{{SERVER}}/budgeting/[segment]',
			'documentation'	=> api_docs . '/budgeting'
        ]);
	}
	
	public function transactions() {
        $this->core->return([
			'application' 	=> api_name,
			'message' 		=> api_welcome . ' - Transactions',
			'format uri'	=> '{{SERVER}}/transactions/[segment]',
			'documentation'	=> api_docs . '/transactions'
        ]);
	}
	
	public function accounting() {
        $this->core->return([
			'application' 	=> api_name,
			'message' 		=> api_welcome . ' - Accounting',
			'format uri'	=> '{{SERVER}}/accounting/[segment]',
			'documentation'	=> api_docs . '/accounting'
        ]);
	}
}