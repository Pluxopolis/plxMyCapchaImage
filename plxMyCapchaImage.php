<?php
/**
 * Classe plxMyCapchaImage
 *
 **/
class plxMyCapchaImage extends plxPlugin {

	/**
	 * Constructeur de la classe
	 *
	 * @return	null
	 * @author	Stéphane F.
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# Droits pour accèder à la page config.php du plugin
		$this->setConfigProfil(PROFIL_ADMIN);

		# Ajouts des hooks
		$this->addHook('plxShowCapchaQ', 'plxShowCapchaQ');
		$this->addHook('plxShowCapchaR', 'plxShowCapchaR');
		$this->addHook('plxMotorNewCommentaire', 'plxMotorNewCommentaire');
		$this->addHook('plxMotorDemarrageCommentSessionMessage', 'plxMotorDemarrageCommentSessionMessage');
		$this->addHook('ThemeEndHead', 'ThemeEndHead');
		$this->addHook('IndexEnd', 'IndexEnd');

	}

	/**
	 * Méthode qui affiche l'image du capcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxShowCapchaQ() { //'.PLX_PLUGINS.'

		$plxMotor = plxMotor::getInstance();

		$token = md5($plxMotor->aConf['clef']);
		$_SESSION['CAPCHAIMAGE_token'] = $token;
		$_SESSION['CAPCHAIMAGE_token_time'] = time();

		$root = $plxMotor->urlRewrite(str_replace('./', '', PLX_PLUGINS).'plxMyCapchaImage/capcha.php');

		echo '<img src="'.$root.'" alt="Capcha" id="capcha" />';
		echo '<a id="capcha-reload" href="javascript:void(0)" onclick="document.getElementById(\'capcha\').src=\''.$root.'&\' + Math.random(); return false;"><img src="'.PLX_PLUGINS.'plxMyCapchaImage/reload.png" title="" /></a><br />';
		$this->lang('L_MESSAGE');
		echo '<input type="hidden" name="CAPCHAIMAGE_token" value="'.$token.'" />';
		echo '<?php return true; ?>'; # pour interrompre la fonction CapchaQ de plxShow
	}

	/**
	 * Méthode qui encode le capcha en sha1 pour comparaison
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxMotorNewCommentaire() {

		$plxMotor = plxMotor::getInstance();

		$CapchaImageMessage="";

		if($CapchaImageMessage=="") {
			// vérification token securité
			if(!isset($_SESSION["CAPCHAIMAGE_token"]) OR !isset($_SESSION["CAPCHAIMAGE_token_time"]) OR $_SESSION["CAPCHAIMAGE_token"]!=md5($plxMotor->aConf["clef"])) {
				$CapchaImageMessage = "SPAM SECURITY NOT VALID";
			}
		}
		if($CapchaImageMessage=="") {
			// vérfication du délai attente
			$timer = $this->getParam('timer')=='' ? 0 : $this->getParam('timer');
			$token_age = time() - $_SESSION["CAPCHAIMAGE_token_time"];
			if($token_age < intval($timer)) {
				$CapchaImageMessage = sprintf($this->getLang('L_WAIT'),$this->getParam('timer'));
			}
		}
		if($CapchaImageMessage=="") {
			// vérification du capcha
			if(!isset($_SESSION["capcha"]) OR empty($_SESSION["capcha"]) OR !isset($_POST["rep"]) OR empty($_POST["rep"]) OR $_SESSION["capcha"]!=$_POST["rep"]) {
				$CapchaImageMessage = L_NEWCOMMENT_ERR_ANTISPAM;
			}
		}
		echo '<?php
			$this->CapchaImageMessage = "'.$CapchaImageMessage.'";
			$this->aConf["capcha"] = '.($CapchaImageMessage=="" ? 0 : 1).';
		?>';
	}

	/**
	 * Méthode qui renvoie le message adapté si la durée de vie du token est incorrecte
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxMotorDemarrageCommentSessionMessage() {
		echo '<?php
			if(!empty($this->CapchaImageMessage)) {
				$_SESSION["msgcom"] = $this->CapchaImageMessage;
			}
		?>';
	}

	/**
	 * Méthode qui retourne la réponse du capcha // obsolète
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxShowCapchaR() {
		echo '<?php return true; ?>';  # pour interrompre la fonction CapchaR de plxShow
	}

	/**
	 * Méthode qui génère le code du capcha
	 *
	 * @return	string		code du capcha
	 * @author	Stéphane F.
	 **/
	private function getCode($length) {
		$chars = '23456789abcdefghjklmnpqrstuvwxyz'; // Certains caractères ont été enlevés car ils prêtent à confusion
		$rand_str = '';
		for ($i=0; $i<$length; $i++) {
			$rand_str .= $chars{ mt_rand( 0, strlen($chars)-1 ) };
		}
		return strtolower($rand_str);
	}

	/**
	 * Méthode qui modifie la taille et le nombre maximum de caractères autorisés dans la zone de saisie du capcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function IndexEnd() {
		echo '<?php
			if(preg_match("/<input(?:.*?)name=[\'\"]rep[\'\"](?:.*)maxlength=([\'\"])([^\'\"]+).*>/i", $output, $m)) {
				$o = str_replace("maxlength=".$m[1].$m[2], "maxlength=".$m[1]."5", $m[0]);
				$output = str_replace($m[0], $o, $output);
			}
		?>';
	}

	/**
	 * Méthode qui applique un effet css sur le bouton de rechargement du captcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function ThemeEndHead() {
		echo "\n\t<style>#capcha-reload:hover{opacity: 0.7; filter: alpha(opacity=70);}</style>\n";
	}
}
?>