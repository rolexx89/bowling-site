<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class users extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/users
	 *	- or -  
	 * 		http://example.com/index.php/users/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/game/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	public function add() {
		$this->load->library('UsersClass', array(), 'usersInstance');
                //$data['usrsInstance'] = $this->usersInstance;
                $data['usersLists'] = $this->usersInstance->getAllUsers();    

		$this->load->view('users_index',$data);
	}

	public function delete($id) {
		$this->load->delete('welcome_message');
	}

	public function update() {
		$this->load->view('welcome_message');
	}
      
}

/* End of file users.php */
/* Location: ./application/controllers/users.php */