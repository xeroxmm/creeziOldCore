<?php
	B::ID('wrap')->_DIV('debuger', NULL)->_DIV('scriptArea',NULL);
	html::footer()->addScript('https://creezi.com/ressources/core/js/debugJava.js');
	html::send200();
?>