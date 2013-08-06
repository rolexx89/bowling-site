<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class UsersClass {
            private	$tableName	= 'users';
            function __construct($user_id = false) {
                /*    mysql_query("
                            CREATE TABLE IF NOT EXISTS `users` (
                            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                            `name` varchar(64) NOT NULL,
                            `surname` varchar(64) NOT NULL,
                            `nickname` varchar(32) NOT NULL,
                            PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Users Table' AUTO_INCREMENT=1 ;
                    ");  
                 */
                    if(!empty($user_id)) {
                            $this->loadUser($user_id);
                    }
            }
            private $data	= array();
            public function getData() {
                    return $this->data;
            }
            public function isLoaded() {
                    return !empty($this->data);
            }
            public function loadUser($user_id) {
                    $this->data	= @mysql_fetch_array(mysql_query("select
                                    `id` as `user_id`,
                                    `name`,
                                    `surname`,
                                    `nickname` as `nick`
                            from `{$this->tableName}`
                            where `id` = ".abs($user_id)."
                            "));
                    return $this->data;
            }
            public function getAllUsers() {
                    $data	= array();
                    $query	= mysql_query("select
                                    `id` as `user_id`,
                                    `name`,
                                    `surname`,
                                    `nickname` as `nick`
                            from `{$this->tableName}`
                            where 1
                            ");
                    while ($user	= mysql_fetch_array($query, MYSQL_ASSOC))
                            $data[$user['user_id']]	= $user;
                    return $data;
            }
            public function addUser($data) {
                    // name surname nickname
                    if(
                            is_array($data)
                            && !empty($data['name'])
                            && !empty($data['surname'])
                            && !empty($data['nick'])
                    ) {
                            mysql_query("insert into `{$this->tableName}` (`name`,`surname`,`nickname`) values (
                                            0x".bin2hex(''.$data['name'])." ,
                                            0x".bin2hex(''.$data['surname'])." ,
                                            0x".bin2hex(''.$data['nick'])."
                                    )");
                            return mysql_insert_id();
                    }
            }
            public function delUser($user_id) {
                    mysql_query("delete from `{$this->tableName}` where `id` = ".abs($user_id)." ");
            }
    }


?>
