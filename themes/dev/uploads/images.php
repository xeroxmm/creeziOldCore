<?php

$userLevelEnum = userLevelEnum::NORMAL;

#print_r(user::getUserObject());die();
security::loadUserLevel(userLevelEnum::NORMAL);

if(upload::isPOSTRequest()){
	api::forceOn();
	api::loadUploadTemplate();
	if(upload::checkAndDoUploadRequest(1)){
		api::getTemplateObject()
			->setStatus( TRUE )
			->setType('image')
			->setUrl( upload::getLastUpload()->getLinkURL() )
			->setThumb( upload::getLastUpload()->getThumbURL() )
			->setImageID( upload::getLastUpload()->getLastIDContentLibrary() )
			->setConfirmedInfoString( upload::getLastUpdateInfoString() );
		if(!empty(upload::getLastErrorArray()))
			api::getTemplateObject()->setErrorAsInfo( implode(',',upload::getLastErrorArray()) );
	} else {
		api::getTemplateObject()->setStatus( FALSE );
		api::getTemplateObject()->setErrorOnUpload( implode(',',upload::getLastErrorArray()) );
	}
	api::send200();
	exit;
}

html::footer()->addScript('../ressources/core/js/dropzone.js');
html::footer()->addScript('../ressources/core/js/dragNdrop.js');
B::ID('content')
	->_FORM('drag-and-drop-zone','dropzone uploadBox')
		->in()->setAttr('enctype','multipart/form-data')->setAttr('action','./uploads?do')
			#->_DIV(NULL,'uploadBox')->in()
				->_Input(NULL, NULL, 'file', NULL, 'files[]')
				->_Input(NULL, NULL, 'text', "testing chamber 101", 'title')
				->_Input(NULL, NULL, 'hidden', 'number', NULL, 'aUpload')
				->_Input(NULL, NULL, 'hidden', 'text', NULL, 'folderName')
				->_Input(NULL, NULL, 'hidden', 'text', NULL, 'category');
		#->in()->_DIV()->in()->_P(null,null,'Drag and Drop')->setContent();
B::ID('content')
	->_DIV('demo-files')->in()
		->_P(NULL, NULL, 'Test');
B::ID('content')
	->_DIV('demo-debug')->in()
		->_P(NULL, NULL, 'DEBUG');

B::ID('content')
	->_DIV('upload-category')->in()
		->_DIV('tags-for-pic','add-items')
		->_DIV('category-list','flex-row')
		->_DIV('popular-tags-by-category','flex-row')
		->_DIV('popular-tags-by-you','flex-row');

B::ID('category-list')
	->_H(6,NULL,['one-tag'])->in()->setContent('Choose a category')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Animals</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Games</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Girls</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Memes</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Movies</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Music</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>Star Trek</p>');
	
B::ID('popular-tags-by-category')
	->_H(6,NULL,['one-tag'])->in()->setContent('Choose out of the popular tags')->outer()
	->_DIV('to-cat-animals', 'to-cat')->in()->setContent('<p>cat</p><p>dog</p>')->outer()
	->_DIV('to-cat-games', 'to-cat')->in()->setContent('<p>cs:go</p><p>CoD</p>')->outer()
	->_DIV('to-cat-girls', 'to-cat')->in()->setContent('<p>boobs</p><p>pranks</p>')->outer()
	->_DIV('to-cat-memes', 'to-cat')->in()->setContent('<p>russian guy</p><p>yes!</p>')->outer()
	->_DIV('to-cat-movies', 'to-cat')->in()->setContent('<p>inception</p><p>matrix</p>')->outer()
	->_DIV('to-cat-music', 'to-cat')->in()->setContent('<p>hardstyle</p><p>rock</p>')->outer()
	->_DIV('to-cat-startrek', 'to-cat')->in()->setContent('<p>really?</p><p>why!</p>');

B::ID('popular-tags-by-you')
	->_H(6,NULL,['one-tag'])->in()->setContent('Your popular tags')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>baby</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>balls</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>beach</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>picard</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>pool</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>sexy</p>')->outer()
	->_DIV(NULL,'an-item')->in()->setContent('<p>sugar</p>');
	
html::send200();
	
exit;

?>