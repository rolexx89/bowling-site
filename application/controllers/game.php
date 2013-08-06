<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Game extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/game
	 *	- or -  
	 * 		http://example.com/index.php/game/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/game/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {
		$this->listgames();
	}

	public function listgames() {
		$this->load->library('BowllingClass', array(), 'gameInstance');
		$data['gameInstance'] = $this->gameInstance;

		$this->load->view('game_index',$data);
	}

	public function show($id) {
		$this->load->view('welcome_message');
	}

	public function newgame() {
		$this->load->view('welcome_message');
	}
}

/* End of file game.php */
/* Location: ./application/controllers/game.php */