<?php if(!defined('PLX_ROOT')) exit; ?>
<?php

# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
	$plxPlugin->setParam('timer', $_POST['timer'], 'numeric');
	$plxPlugin->saveParams();
	header('Location: parametres_plugin.php?p=plxMyCapchaImage');
	exit;
}

$timer = $plxPlugin->getParam('timer')=='' ? 0 : $plxPlugin->getParam('timer');

?>

<form class="inline-form" action="parametres_plugin.php?p=plxMyCapchaImage" method="post" id="form_plxMyCapchaImage">
	<fieldset>
		<p>
			<label for="id_timer"><?php $plxPlugin->Lang('L_CONFIG_TIMER') ?> :</label>
			<?php plxUtils::printInput('timer',$timer,'text','4-4') ?>
			<?php $plxPlugin->Lang('L_CONFIG_TIMER_OFF') ?>
		</p>
		<p class="in-action-bar">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="submit" value="Enregistrer" />
		</p>		
	</fieldset>
</form>