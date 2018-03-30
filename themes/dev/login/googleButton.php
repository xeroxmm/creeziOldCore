<?php
html::head()->selectMeta()->setGooglePlusKeys(googlePlusKey);
html::head()->loadBootstrapDependencies();

B::ID('content')
	->_DIV(NULL,['flexCenteredFull'])->in()
	->_BtColumnSize_4(NULL,['centered'])->in()
		->_BtPanel()->in()
			->_BtPanelHeader(null, null, 'Login or Sign Up')
			->_BtPanelBody(null, null, '<p class="txtCentered">Login and feel free...<br />expect the unexpected...<br/>Take ... Your ... Time<br />:-)</p>')
			->_BtPanelBody(null, null)->setCenter()->in()
			//->addElement(snippet::loginButtonGoogle())
			//->addElement(snippet::loginButtonFacebook())
			->addElement(snippet::loginButtonSteam());

html::send200();
exit();
?>