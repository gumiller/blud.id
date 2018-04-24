<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Core {
    protected $CI;
    
    public function __construct() {
		$this->CI =& get_instance();
    }
    
	private function response($code, $response) {
		$this->CI->output
			->set_status_header($code)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			->_display();
		exit;
	}
	
	public function method() {
		return strtolower($this->CI->input->server('REQUEST_METHOD'));;
	}
	
    public function _get($tbstructures, $defaultorder, $table) {
        $headers    = $this->CI->input->request_headers();
        $conditions = array();
        $structures = array();
        foreach ($tbstructures as $structure):
            array_push($structures, $structure);
        endforeach;
        foreach ($headers as $key => $val):
            if (in_array($key, $structures)):
                $conditions[$key] = $val;
            endif;
        endforeach;
        if ($this->CI->input->get_request_header('Page') == null):
            if (count($conditions) == 0):
                $results    = $this->CI->db->select('*')
                            ->order_by($defaultorder, 'asc')
                            ->get($table)->result();
            else:
                $results    = $this->CI->db->select('*')
                            ->where($conditions)
                            ->order_by($defaultorder, 'asc')
                            ->get($table)->result();
            endif;
            $response   = $results;
        else:
            $offset     = $this->CI->input->get_request_header('Limit');
            $page       = ((int)$offset !== 0)?((int)$offset - 1):0;
            $limit      = ($page !== null && (int)$page !== 0)?($page * $offset):10;
            if (count($conditions) == 0):
                $rows       = $this->CI->db->select('*')
                            ->order_by($defaultorder, 'asc')
                            ->get($table)->result();
                $results    = $this->CI->db->select('*')
                            ->order_by($defaultorder, 'asc')
                            ->limit($limit, $page)
                            ->get($table)->result();
            else:
                $rows       = $this->CI->db->select('*')
                            ->where($conditions)
                            ->order_by($defaultorder, 'asc')
                            ->get($table)->result();
							$this->response(200, $values);
                $results    = $this->CI->db->select('*')
                            ->where($conditions)
                            ->order_by($defaultorder, 'asc')
                            ->limit($limit, $page)
                            ->get($table)->result();
            endif;
            $response   = [
                'page'  => (int)$page,
                'limit' => (int)$limit,
                'total' => count($rows),
                'rows'  => $results
            ];
        endif;
		$this->userlogs();
        $this->return(['status' => 'success', 'results' => $response]);
    }
    
    public function _post($structures, $table, $primary) {
        $this->CI->db->insert($table, $this->trimData($structures, $primary));
        if($this->CI->db->affected_rows() > 0):
			$this->userlogs();
            $this->created();
        else:
            $this->error($this->CI->db->error());
        endif;
    }
    
    public function _put($structures, $table, $primary) {
		foreach ($this->trimData($structures, $primary) as $key => $val):
            $this->CI->db->set($key, $val);
        endforeach;
        $this->CI->db->where($primary, $this->CI->input->input_stream($primary));
        $this->CI->db->update($table);
        if($this->CI->db->affected_rows() > 0):
			$this->userlogs();
            $this->created();
        else:
            $this->error($this->CI->db->error());
        endif;
    }
    
    public function _delete($dependencies, $table, $primary) {
        if ($this->dependencies($dependencies, $this->CI->input->input_stream($this->primary))):
            $this->CI->db->where($this->primary, $this->CI->input->input_stream($this->primary));
            $this->CI->db->delete($table);
			if($this->CI->db->affected_rows() > 0):
				$this->userlogs();
                $this->deleted();
            else:
                $this->error($this->CI->db->error());
            endif;
        else:
            $this->err_dependence();
        endif;
    }
	
	public function getToken($secretkey, $username, $password) {
		$results    = $this->CI->db->select('agencies.agencyID as agencyID, officers.officerID as officerID, officers.officerPassword as password, PASSWORD(CONCAT("'.$password.'")) as usersecret')
					->from('officers')
					->join('agencies', 'agencies.agencyID = officers.agencyID')
					->where('officers.officerUsername', $username)
					->where('agencies.secretkey', $secretkey)
					->get()->result();
		if (count($results) > 0 && $results[0]->usersecret == $results[0]->password):
			$token 		= base64_encode(md5(date('Y-m-d').$results[0]->agencyID.$username.$results[0]->usersecret));
			$this->CI->db->set('secretkey', $token)
				->where('officerID', $results[0]->officerID)
				->update('officers');
			return '?'.base64_encode($token);
		else:
			return false;
		endif;
	}

	public function auth() {
		if ($this->CI->input->get_request_header('Token') !== null):
			$secretkey	= base64_decode(substr($this->CI->input->get_request_header('Token'), 1, -1));
			$results    = $this->CI->db->select('*')
						->where('secretkey', $secretkey)
						->get('officers')->result();
			if (count($results) > 0):
				if ($secretkey === base64_encode(md5(date('Y-m-d').$results[0]->agencyID.$results[0]->officerUsername.$results[0]->officerPassword))):
					return true;
				else:
					$this->response(401, ['status' => 'unauthorized', 'results' => 'Invalid Token']);
				endif;
			else:
				$this->response(401, ['status' => 'unauthorized', 'results' => 'Invalid Token']);
			endif;
		else:
			$this->response(401, ['status' => 'unauthorized', 'results' => 'Invalid Token']);
		endif;
	}

	public function userlogs() {
		$secretkey	= base64_decode(substr($this->CI->input->get_request_header('Token'), 1, -1));
		$results    = $this->CI->db->select('*')
					->where('secretkey', $secretkey)
					->get('officers')->result();
        $this->CI->db->insert('officerlogs', [
			'officerlogID' 		=> md5(rand(0, 100).microtime()),
			'officerID'			=> $results[0]->officerID,
			'officerlogDate' 	=> date("Y-m-d"),
			'officerlogUri'		=> '{{SERVER}}'.$this->CI->uri->uri_string(),
			'officerlogQuery' 	=>  trim(str_replace(PHP_EOL, ' ', $this->CI->db->last_query()))
		]);
	}

	public function encryptPassword($password) {
		$results = $this->CI->db->select('password("'.$password.'") as password')->get()->result();
		return $results[0]->password;
	}

	public function trimData($structures, $primary) {
		$values = array();
		$structure = array_keys($structures);
		foreach ($this->CI->input->post(NULL, TRUE) as $key => $val):
			$index = $structure[array_search($key, array_column($structures, 'field'))];
			if ($structures[$index]['default'] == 'UNIQ'):
				$value = md5(rand(0, 100).microtime());
			elseif ($structures[$index]['default'] == 'DATE'):
				$value = date("Y-m-d", strtotime($this->input->post($key)));
			elseif ($structures[$index]['default'] == 'SECRET'):
				$value = base64_encode(md5('newuser'));
			elseif ($structures[$index]['default'] == 'PASSWORD'):
				$value = $this->encryptPassword(($this->CI->input->post($key) !== NULL)?$this->CI->input->post($key):'password');
			else:
				$value = ($this->CI->input->post($key))?$this->CI->input->post($key):$structures[$index]['default'];
			endif;
			$values[$index] = $value;
		endforeach;
		foreach ($this->CI->input->input_stream(NULL, TRUE) as $key => $val):
			$index = $structure[array_search($key, array_column($structures, 'field'))];
			if ($structures[$index]['default'] == 'UNIQ'):
				$value = md5(rand(0, 100).microtime());
			elseif ($structures[$index]['default'] == 'DATE'):
				$value = date("Y-m-d", strtotime($this->input->input_stream($key)));
			elseif ($structures[$index]['default'] == 'SECRET'):
				$value = base64_encode(md5('newuser'));
			elseif ($structures[$index]['default'] == 'PASSWORD'):
				$value = $this->encryptPassword(($this->CI->input->input_stream($key) !== NULL)?$this->CI->input->input_stream($key):'password');
			else:
				$value = ($this->CI->input->input_stream($key))?$this->CI->input->input_stream($key):$structures[$index]['default'];
			endif;
			$values[$index] = $value;
		endforeach;
		return $values;
	}
	
	public function dependencies($dependencies, $value)
	{
		$query = array();
        foreach ($dependencies as $dependence):
			array_push($query, 'SELECT '.$dependence['field'].' FROM '.$dependence['table'].' WHERE '.$dependence['field'].' = "'.$value.'"');
		endforeach;
        if (count($query) !== 0):
            $rows = $this->CI->db->query('SELECT COUNT(*) as total FROM ('.implode(' UNION ', $query).') rows')->row();
			return ((int)$rows->total > 0)?false:true;
		else:
			return true;
		endif;
	}
	
	public function err_method() {
		$this->response(405, ['status' => 'failure', 'results' => 'Method Not Allowed']);
	}
	
	public function err_dependence() {
		$this->response(424, ['status' => 'failure', 'results' => 'Please check your tables dependencies']);
	}
	
	public function error($response) {
		$this->response(500, ['status' => 'failure', 'results' => $response]);
	}
	
	public function created() {
		$this->response(201, ['status' => 'success', 'results' => 'Successfully']);
	}
	
	public function deleted() {
		$this->response(204, ['status' => 'success', 'results' => 'Successfully Deleted']);
	}
	
	public function return($response) {
		$this->response(200, $response);
	}
}