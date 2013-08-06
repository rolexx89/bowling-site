<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	DB	table structure
	Table [BowllingGame]
	game_id		round	user	try_n	val
*/
class BowllingClass	{
	private	$game_id	= false;
	private	$tableName	= 'bowlling-game';

	function __construct($game_id = true) {
		// mysql_query("
		// 	CREATE TABLE IF NOT EXISTS `bowlling-game` (
		// 		`game_id` bigint(20) unsigned NOT NULL,
		// 		`round` int(10) unsigned NOT NULL,
		// 		`user` bigint(20) unsigned NOT NULL,
		// 		`try_n` int(10) unsigned NOT NULL,
		// 		`val` int(11) NOT NULL,
		// 		UNIQUE KEY `identify_data` (`game_id`,`round`,`user`,`try_n`)
		// 	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Bowlling Game » Data Table';
		// ");
		// $this->setGameId($game_id);
	}

	public	function setGameId($game_id) {
		if($game_id	=== true) {
			$row	= mysql_fetch_row(mysql_query("select max(`game_id`)+1 from `{$this->tableName}` where 1 "));
			$this->game_id	= max(1,$row[0]);
		} else {
			$this->game_id	= $game_id;
		}
	}
	public	function getGameId() {
		return $this->game_id;
	}
	public	function getData() {
		if(!$this->game_id)	return false;
		$data	= array();
		$query	= mysql_query("
			select 
				`game_id`,
				`round`,
				`user` as `user_id`,
				`try_n`,
				`val` as `value`
			from
				`{$this->tableName}`
			where
				`game_id` = \"{$this->game_id}\"
			order by
				`round` ASC,
				`user` ASC,
				`try_n` ASC
				");
		while($row	= mysql_fetch_array($query,MYSQL_ASSOC))
			$data[]	= $row;
		return $data;
	}
	// daca ->pushData() -> returnneaza daca e terminat jocul
	public	function pushData($val = false,$player = false) {
		// get data about game score
		$data	= $this->getData();
		if(!is_array($data))
			$data	= array();
		// detect round
		$currentRound	= 1;
		if(count($data)) {
			$currentRound	= $data[count($data)-1]['round'];
		}

		// daca tot utilizatorii au completat roundul curent
		// si $player deja exista atunci
		// if($currentRound < 10) $currentRound += 1;

		// check if current round is completed
		$completed_check	= function($data,$currentRound,&$players) {
			$players_all	= array();
			foreach ($data as $row) {
				$players_all[$row['user_id']]	= true;
				}
			$players	= array();
			$roundHaveData	= false;
			foreach ($data as $row)
				if( $row['round'] == $currentRound ) {
					$roundHaveData	= true;
					if(!isset($players[$row['user_id']]))
						$players[$row['user_id']]	= array(
								'sum'	=> 0,
								'try'	=> 0
							);
					$players[$row['user_id']]['sum']	+= $row['value'];
					$players[$row['user_id']]['try']	+= 1;
				}
			// check if playes completed the round
			$completed	= true;
			foreach ($players as $user_id => $score )
				if(
					( ( $score['try'] < 2 && $currentRound < 10 ) || ( $score['try'] < 3 && $currentRound == 10 ) )
						&&
					( $score['sum'] < 10 )
				) {

					$completed	= false;
				}
			return $completed && $roundHaveData && count($players) == count($players_all);
		};
		// check if round completed
		$players_array	= array();
		$round_justCompleted	= false;
		if($completed_check($data,$currentRound,$players_array)) {
			if($currentRound < 10) {
				$currentRound += 1;
				$round_justCompleted	= true;
			} else {
				return array(
						'status'	=> 'completed',
						'players'	=> $players_array,
						'allowed-new'	=> false
					);
			}
		}

		$allowed_newUser	= (
							$currentRound == 1
							||
							(
								$currentRound == 2
								&&
								$round_justCompleted
							)
						);

		if($val === false && $player === false) {
			return array(
					'status'	=> 'not-completed',
					'round'		=> $currentRound,
					'players'	=> $players_array,
					'allowed-new'	=> $allowed_newUser
				);
		}

		// val <= 12
		if($val > 10)
			return false;
		// check if user exists
		if(empty($player))
			return false;
		$user_object	= new UsersClass($player);
		if(!$user_object->isLoaded())
			return false;

		$check_f	= function($data,$key,$value) {
			$match_n	= 0;
			foreach($data as $row)
				if( $row[$key]	== $value ) {
					$match_n++;
				}
			return $match_n;
		};
		// detect if player already used
		if($check_f($data,'user_id',$player) == 0) {
			// allow new player if round 1
			if($allowed_newUser && $currentRound == 2) {
				$currentRound = 1;
			}
			if($currentRound > 1)
				// player can't be pushed because round already more than 1
				return false;
		}

		$data_check	= function($data,$player, $currentRound){
			$r	= array(
					'sum'	=> 0,
					'try'	=> 0
				);
			foreach ($data as $row)
				if($row['round'] == $currentRound && $row['user_id'] == $player) {
					$r['sum']	+= $row['value'];
					$r['try']	+= 1;
				}
			return $r;
		};
		// check the user can push data in current round
			// 		if(	( ( sum = 0 && val <= 12 ) || ( sum > 0 && sum + val <= 10 ) )
			// 			&&
			// 			(
			// 				( currentRound < 10 && count_try < 2 )
			// 					||
			// 				( currentRound = 10 && count_try < 3 )
			// 			)
			// 		) {
			// 			....
			//		}
		$data_player	= $data_check($data,$player,$currentRound);
		$sum		= $data_player['sum'];
		$count_try	= $data_player['try'];

		if($count_try == 0 && $val == 10)
			$val = 12;
		if(	( ( $sum == 0 && $val <= 12 ) || ( $sum > 0 && $sum + $val <= 10 ) )
				&&
				(
					( $currentRound < 10 && $count_try < 2 )
						||
					( $currentRound == 10 && $count_try < 3 )
				)
			) {
				mysql_query("insert into `{$this->tableName}` ( `game_id`,`round`,`user`,`try_n`,`val` ) values (
						".abs($this->game_id).",
						".abs($currentRound).",
						".abs($player).",
						".abs($count_try+1).",
						".abs($val)."
					) ");
				return true;
			}
	}

	public function getAllGamesId() {
		$data	= array();
		$query	= mysql_query("
			select 
				`game_id`
			from
				`{$this->tableName}`
			where
				1
			group by `game_id` asc 
				");
		while($row	= mysql_fetch_array($query,MYSQL_ASSOC))
			$data[]	= $row['game_id'];
		return $data;
	}
};


?>