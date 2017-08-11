<?php


 

 class autojblog{
	private $api;
	private $api_key='';
	private $api_secret='';
	private $last_msg='';
	private $table_name ;
	private $token;

	function __construct(){
		
		global $wpdb;



		 
  		$this->table_name = $wpdb -> prefix.'autoJcray';
		if($wpdb->get_var("SHOW TABLES LIKE '".$this->table_name."'") != $this->table_name) {

				$result = $wpdb -> query("
		 		CREATE TABLE `".$this->table_name."` (
					`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`api_key` VARCHAR( 255 ) NOT NULL ,
					`secret_key` VARCHAR( 255 ) NOT NULL ,
					`stoken` VARCHAR( 255 ) NOT NULL

					
				) ENGINE = MYISAM ;");

				$update_nb = $wpdb -> query("
					 INSERT INTO `".$this->table_name."` VALUES('', '', '', '".md5(uniqid(rand(), true))."')
		   		 ");

		}

		$this-> get_conf();
		if(is_array($_POST) && count($_POST) > 0 && (!isset($_POST['t']) || $_POST['t'] != $this->stoken) ){
			echo '<div class="error" style="padding:10px;"><strong>Une erreur est survenue.</strong><br />Unallowed token.</div>';
			define('jblog_error', true);
			
		}else{
			$this->stoken=md5(uniqid(rand(), true));
			$update_nb = $wpdb -> query("
				 UPDATE `".$this->table_name."` SET stoken = '". $this->stoken ."'
		    ");
			
		}

	}
	
	
	function get_conf(){
		global $wpdb;

		$config = $wpdb->get_results("SELECT * FROM `".$this->table_name."`;");
		

		foreach($config as $c){
			foreach($c as $k=>$v){
				$this->$k=$v;
			}
		}
	}
	function admin(){
		
  		global $wpdb;

		if(isset($_POST['api_key']) && !defined('jblog_error')){
			define('updated_jblog', true);
		    $update_nb = $wpdb -> query("
				 UPDATE `".$this->table_name."` SET api_key = '".$_POST['api_key'] ."'
		    ");
		}
		if(isset($_POST['article']) && !defined('jblog_error')){
			if(!$this->publish($_POST['article'])){
				echo '<div class="error" style="padding:10px;"><strong>Une erreur est survenue.</strong><br />'.$this->last_msg.'</div>';
			}
			else  echo '<div style="padding:10px;" class="updated fade">Message de test jbloggu&eacute; !<br /><br /><em>R&eacute;ponse de Jcray: '.$this->last_msg.'</em></div>';

		}
		if(isset($_POST['secret_key'])  && !defined('jblog_error')){
			define('updated_jblog', true);
	      	    $update_nb = $wpdb -> query("
				 UPDATE `".$this->table_name."` SET secret_key = '".$_POST['secret_key'] ."'
		    ");
		}
		if(defined('updated_jblog'))   echo '<div style="padding:10px;" class="updated fade">Modifications enregistr&eacute;es</div>';
		echo __('<img style="float:left;position:relative;top:14px;left:-11px;" src="../wp-content/plugins/autoJblog/images/pacman.png" /><h2 style="border-bottom:solid 1px #333333;">AutoJblog - <em>Configuration de l\'API</em></h2>');
		$this->get_conf();

		echo '
			<div class="updated fade" style="padding:10px;margin-bottom:10px;">
				Pour pouvoir utiliser ce plugin, vous devez renseigner vos informations API.<br />
				Pour obtenir la clef de votre Jblog, rendez vous dans <a href="http://jcray.com/admin/profil" target="_blank">"Mon profil"</a>, sur votre compte <a href="http://jcray.com" target="_blank">Jcray</a>
			</div>
			<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
			<input type="hidden" name="t" value="'.$this->stoken.'"/>
			Votre pseudo Jcray : <input type="text" name="api_key" value="'.$this->api_key.'" /><br />
			Clef API de votre Jblog : <input type="text" name="secret_key" value="'.$this->secret_key.'" />
			<br /><br />
			<input type="submit" value="Enregistrer" />
			</form>
			<br /><br />
			<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
			<input type="hidden" name="t" value="'.$this->stoken.'"/>
			<img style="float:left;position:relative;top:-3px;left:-11px;" src="../wp-content/plugins/autoJblog/images/pacman.png" /><h2 style="border-bottom:solid 1px #333333;">Tester la configuration</h2>
			<div class="updated fade" style="padding:10px;margin-bottom:10px;">
				Saisissez un texte ci-dessous : le plugin va tenter de le publier sur votre Jblog.
			</div>
			<div style="margin-top:10px;width:90%;margin-left:auto;margin-right:auto;">
			<div id="quicktags">
				<div id="editorcontainer"><textarea style="color:#000000;width:100%;height:100px;" class="theEditor"  name="article" tabindex="2" id="content"></textarea></div>
				<input type="submit" style="cursor:pointer;padding:5px;margin:5px;" value="Publier ce texte sur votre Jblog" />
			</div>
			</div>
			</form>
			<div style="margin-top:20px;font-size:11px;text-align:center;">
				Plugin d&eacute;velopp&eacute; par <a href="http://gregoire-penverne" target="_blank">Gr&eacute;goire Penverne</a>, pour <a href="http://jcray.com" target="_blank">Jcray.com</a>
			</div>
		';
	}

	function curl_get($url, $args){
		 if(defined('jblog_error'))return false;
		$options = array(
	        CURLOPT_RETURNTRANSFER => true,     // return web page
       	 CURLOPT_FOLLOWLOCATION => true,     // follow redirects
	        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
       	 CURLOPT_CONNECTTIMEOUT => 200,      // timeout on connect
	        CURLOPT_TIMEOUT        => 200,      // timeout on response
       	 CURLOPT_MAXREDIRS      => 5,       // stop after 10 redirects
		 CURLOPT_POST		   =>1,
		 CURLOPT_POSTFIELDS	   =>$args 
	    	);

		$ch      = curl_init( $url );
	   	curl_setopt_array( $ch, $options );
	    	$content = curl_exec( $ch );
       	curl_close( $ch );
		return $content;
	}

	function publish($article){
		global $wpdb;

		$config = $wpdb->get_results("SELECT * FROM `".$this->table_name."`;");
		
		
		foreach($config as $c){
			foreach($c as $k=>$v){
				$$k=$v;
			}
		}
		$response = $this->curl_get('http://jcray.com/api/jblog', array('jblog_user'=>$api_key, 'jblog_key'=>$secret_key, 'article'=>$article));
		$this->last_msg = $response;
		if(eregi('erreur', $response))return false;
		return true;
	}
  }

function curl_get($url, $args){
		 if(defined('jblog_error'))return false;
		$options = array(
	        CURLOPT_RETURNTRANSFER => true,     // return web page
       	 CURLOPT_FOLLOWLOCATION => true,     // follow redirects
	        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
       	 CURLOPT_CONNECTTIMEOUT => 200,      // timeout on connect
	        CURLOPT_TIMEOUT        => 200,      // timeout on response
       	 CURLOPT_MAXREDIRS      => 5,       // stop after 10 redirects
		 CURLOPT_POST		   =>1,
		 CURLOPT_POSTFIELDS	   =>$args 
	    	);

		$ch      = curl_init( $url );
	   	curl_setopt_array( $ch, $options );
	    	$content = curl_exec( $ch );
       	curl_close( $ch );
		return $content;
	}

	function jblog_publish($post_ID){

	     global $wpdb;
            $article = nl2br($_POST['content']);
	     $config = $wpdb->get_results("SELECT * FROM `". $wpdb -> prefix.'autoJcray'."`;");
		
		
		foreach($config as $c){
			foreach($c as $k=>$v){
				$$k=$v;
			}
		}
		$response = curl_get('http://jcray.com/api/jblog', array('jblog_user'=>$api_key, 'jblog_key'=>$secret_key, 'article'=>$article));
		

		 return $post_ID;
	}

	while(remove_action('publish_post','jblog_publish')){remove_action('publish_post','jblog_publish');}
	add_action('publish_post','jblog_publish');
	


?>