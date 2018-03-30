<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	html::head()->addStylesheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
	html::head()->addStylesheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css');
	html::head()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
	html::head()->addOwnStyle('#main {overflow: scroll;}');
	
	B::ID('main')->_DIV(null,'dever')->in()->_P(null,'devInfo','AlarmField')
		->_BtFieldAlert('error-1', null, 'Alarm! Alarm! Alarm!');
	B::ID('main')->_DIV(null,'dever')->in()->_P(null,'devInfo','InfoField')
		->_BtFieldInfo('error-1', null, 'Info! Info! Info!');
	B::ID('main')->_DIV(null,'dever')->in()->_P(null,'devInfo','WarningField')
		->_BtFieldWarning('error-1', null, 'Warning! -.-'); 
	B::ID('main')->_DIV(null,'dever')->in()->_P(null,'devInfo','Success')
		->_BtFieldSuccess('error-1', null, 'Success! "-.-"'); 		
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','aBootButton')
		->_BtButton(null, 'test', 'Test Button', 'star', 'Star-Label');

	$elemente = array(
		(new htmlTag('p',null, null))->_A(null,null,null,'1. Link'),
		(new htmlTag('p',null, null))->_A(null,null,null,'2. Link'),
		(new htmlTag('p',null, null))->_A(null,null,null,'3. Link'),
		(new htmlTag('p',null, null))->_A(null,null,null,'4. Link')
	);
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','aBootDropdownMenu')
		->_BtDropdownFromArray(null, 'hmmClass', 'Test DropdownField', $elemente);

	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootButtonGroup')
		->_BtButtonGroup(null,null)->in()
			->_BtButton(null, null, 'B1')
			->_BtButton(null, null, 'B2')
			->_BtButton(null, null, 'B3')
			->_BtButton(null, null, 'B4');
		
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootButtonGroupJustified')
		->_BtButtonGroupJustified(null,null)->in()
			->_BtButton(null, null, 'B1j')
			->_BtButton(null, null, 'B2j')
			->_BtButton(null, null, 'B3j')
			->_BtButton(null, null, 'B4j');

	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootButtonInputLeft')
		->_BtForm(null, null)->in()->_BtInputText(null, null,'aLabel:','aPlaceHolder' );

	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootButtonInputRight')
		->_BtForm(null, null)->in()->_BtInputText(null, null,': - right','aPlaceHolder',false);
	
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootBreadCrumb')
		->_BtBreadcrumb()->in()
			->_BtBreadcrumbLinkEntry(null, null, 'Link 1' , 'http://dev.martin-goerner.com')
			->_BtBreadcrumbLinkEntry(null, null, 'Link 2')
			->_BtBreadcrumbLinkEntry(null, null, 'Link 3')
			->_BtBreadcrumbActiveEntry(null, null, 'active One');
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootLabel')
		->_BtLabelPrimary('primary Text')
		->_BtLabelInfo('Info')
		->_BtLabelSuccess('Success');
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','bootBadge')
		->_BtBadge('1337 CS GO');
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','a Progress Bar')
		->_BtProgressBar(null,null,'Alert 25%',25, 'danger')
		->_BtProgressBar(null,null,'Super 50% cOOc',50);
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','the Navbar ...')
		->_BtNavbar('navbarX')->in()->in()
		->_BtNavbarHeader('navbarheadX')->in()->_BtNavbarBrandLink()->in()->setContent('BrandLink');
	
	B::ID('navbarX')->in()
		->_BtNavbarButton(null, null, 'Btn 1')
		->_BtNavbarButton(null, null, 'Btn 2')
		->_BtNavbarButton(null, null, 'Btn 3')
		
		->_BtNavbarForm()->in()->
			_BtFormGroup()->in()->
				_Input(null,['form-control'],'text','navSearch')
				->_BtNavbarButton(null,null,'Submit');
	
	B::ID('navbarX')->in()->_BtNavbarText('Ein Text und so...')->in()->_BtNavbarSetAlignmentRight();
	
	B::ID('main')->_DIV(null, 'dever')->in()->_P(null,'devInfo','Spaß mit Listen...')
		->_DIV()->in()->_UL()->in()->aList('aList2')->_LIText('LI mit Text')->_LILink('dev.martin-goerner.com', 'LinkText :-O')->_LILabelInfo('Info','oh oh ... ');
		
	html::send200();
	
	exit;
?>