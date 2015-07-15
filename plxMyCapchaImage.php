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

		# Ajouts des hooks
		$this->addHook('plxShowCapchaQ', 'plxShowCapchaQ');
		$this->addHook('plxShowCapchaR', 'plxShowCapchaR');
		$this->addHook('plxMotorNewCommentaire', 'plxMotorNewCommentaire');
		$this->addHook('IndexEnd', 'IndexEnd');

	}

	/**
	 * Méthode qui affiche l'image du capcha
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxShowCapchaQ() {
		$_SESSION['capcha']=$this->getCode(5);
		echo '<img src="'.PLX_PLUGINS.'plxMyCapchaImage/capcha.php" alt="Capcha" id="capcha" /><br />';
		$this->lang('L_MESSAGE');
		echo '<?php return true; ?>'; # pour interrompre la fonction CapchaQ de plxShow
	}

	/**
	 * Méthode qui encode le capcha en sha1 pour comparaison
	 *
	 * @return	stdio
	 * @author	Stéphane F.
	 **/
	public function plxMotorNewCommentaire() {
		echo '<?php $_SESSION["capcha"]=sha1($_SESSION["capcha"]); ?>';
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
			if($o = preg_match("/<input\s+.*?name=[\'\"]rep[\'\"].*?>/is", $output, $m)) {
				$o = preg_replace("/maxlength=[\'\"][0-9]+[\'\"]/is", "maxlength=\"5\"", $m[0]);
				$o = preg_replace("/size=[\'\"][0-9]+[\'\"]/is", "size=\"5\"", $o);
				$output = str_replace($m[0], $o, $output);
			}
		?>';
	}
}
?>