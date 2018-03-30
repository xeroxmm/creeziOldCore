var draggingScale = {
	status: false, 
	obj: '', 
	startX: 0, 
	startY: 0, 
	startWidth: 0, 
	startWidthMid: 0,
	minWidth: 250,
	faktor: 1
	};
$(function() {
	osLoaded();
	
	$('.togglerFullscreen').on('click', function(){
		viewPort.toggleFullscreen('buttonBarTogglerFS');
		console.log('click');
	});
	
	$('.niceScrollY').each(function(){
		$(this).perfectScrollbar();
	});
	$('.scalable').each(function(){
		$(this).css( 'width', $(this).width() );
	});
	$('.scale-button').on('mousedown', function( e ){
		preventHightlighting();
		draggingScale.obj = $(this).parent().parent().attr('id');
		draggingScale.status = true;
		draggingScale.startX = e.clientX;
		draggingScale.startY = e.clientY;
		draggingScale.startWidth = $(this).parent().parent().width();
		draggingScale.startWidthMid = $('#middleWindow').width();
		if(draggingScale.obj == 'rightWindow')
			draggingScale.faktor = -1;
	});
	$('#explorerContainer').on('mouseup', function( e ){
		resetDraggingObjScale();
		allowHighlighting();
	});
	$('#explorerContainer').on('mouseleave', function( e ){
		resetDraggingObjScale();
		allowHighlighting();
	});
	$('#explorerContainer').on('mousemove', function( e ){
		if(draggingScale.status){
			var newW = 0; var f = draggingScale.faktor;
			newW = draggingScale.startWidth - f*(draggingScale.startX - e.clientX);
			newWm = draggingScale.startWidthMid + f*(draggingScale.startX - e.clientX);
			if(newW >= draggingScale.minWidth && newWm >= draggingScale.minWidth){
				$('#'+draggingScale.obj).css('width', newW);
				$('#middleWindow').css('width', newWm);
			}
		}
	});
});
function osLoaded(){
	viewPort.loadFS('buttonBarTogglerFS');
	viewPort.loadDateTime('osBarDate','osBarTime');
	viewPort.bindWindowDrag('window-bar-top','window-container');
	viewPort.bindWindowStretch('stretchbox','window-container');
	viewPort.bindWindowActiveClicks();
	viewPort.bindWindowDeppLinkDoubleClick();
	viewPort.setWindowPosition();

	viewPort.setWindowOnWindowsObject();
	
	setTimeout(osLoadedAnimation,2000);
	return true;
}
function osLoadedAnimation(){
	$('#osLoading').fadeOut(1000);
	$('#head').remove();
	$('#base').removeClass('background-contains');
	$('#osDesktop').removeClass('nonVis');
	/*$('#head').animate({
		backgroundColor: "transparent"
	},2000);*/
}
function resetDraggingObjScale(){
	draggingScale.obj = null;
	draggingScale.status = false;
	draggingScale.startX = 0;
	draggingScale.startY = 0;
	draggingScale.startWidth = 0;
	draggingScale.startWidthMid = 0;
	draggingScale.faktor = 1;
}
function allowHighlighting(){
	$(document).unbind('selectstart');
}
function preventHightlighting(){
	document.getSelection().removeAllRanges();
	$(document).bind('selectstart', function(e) {
		e.preventDefault();
		return false;
	});
}
var viewPort = {
	isFullScreen: false,
	lastMinute: '-1',
	dragAndDropObject: {
		start: {x: 0, y: 0},
		mouse: {x: 0, y: 0},
		width: 0,
		height: 0,
		isMovable: false,
		isStretchable: false, 
		triggerObj: null
	},
	windowProperties: {
		minHeight: 375,
		minWidth: 500
	},
	localDays: {
		en : ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']
	},
	loadFS: function(element){
		// check if it is fullscreen
		if(!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement){
			viewPort.isFullScreen = false;
			$('#'+element).removeClass('color-branding');
		} else{
			viewPort.isFullScreen = true;
			$('#'+element).addClass('color-branding');
		}
	},
	loadDateTime: function(elementDate, elementTime){
		setInterval(function(){ 
			viewPort.updateDateTime(elementDate, elementTime);
		}, 900);
	},
	updateDateTime: function(elementDate, elementTime){
		var d = new Date();
		var min = d.getMinutes(); var hour = d.getHours(); var day = d.getDay(); var dayM = d.getDate(); var month = d.getMonth()+1; var year = d.getFullYear();
		if(min < 10) min = '0'+min;
		if(hour < 10) hour = '0'+hour;
		if(dayM < 10) dayM = '0'+dayM;
		if(month < 10) month = '0'+month;
		if(d.getMinutes() != viewPort.lastMinute){
			$('#'+elementDate).html(viewPort.localDays.en[day]+'<br />'+dayM+'.'+month+'.'+year);
			$('#'+elementTime).text(hour+':'+min);
			viewPort.lastMinute = d.getMinutes();
		}
	},
	bindWindowDeppLinkDoubleClick: function(){
		$('body').on('dblclick','.deepLink',function(){
			windowPort.deepLoad($(this).parents('.window-container').attr('id'), $(this).data('linkhard'));
		});
	},
	bindWindowActiveClicks: function(){
		$('body').on('mousedown','.window-container',function(){
			if(!$(this).hasClass('active')){
				$('.window-container').removeClass('active');
				$(this).addClass('active');
			}
		});
		$('body').on('scroll','.window-container',function(){
			if(!$(this).hasClass('active')){
				e.preventDefault();
				e.stopPropagation();
			}
			console.log('scroll');
		});
		$(document).on('click', '.window-container .minimize', function(){
			$(this).closest('.window-container').removeClass('active');
			$(this).closest('.window-container').addClass('hidden');
		});
		$(document).on('click', '.window-container .maximize', function(){
			$(this).closest('.window-container').toggleClass('fullScreen');
			if(!$(this).closest('.window-container').hasClass('fullScreen')){
				$(this).closest('.window-container').css({
					left: $(this).closest('.window-container').data('pos').l,
					top: $(this).closest('.window-container').data('pos').t, 
				});
			}
		});
	},
	bindWindowDrag: function(triggerClass, windowClass){
		$('body').on('mousedown','.'+triggerClass, function( e ){
			viewPort.deepFunctions.loadObjectDimensions( this , windowClass , e );
			viewPort.dragAndDropObject.isMovable = true;
		});

		$('body').on('mousemove','#osDesktop', function( e ){
			if(viewPort.dragAndDropObject.isMovable){
				var left = (viewPort.dragAndDropObject.start.x - (viewPort.dragAndDropObject.mouse.x - e.clientX));
				var top = (viewPort.dragAndDropObject.start.y - (viewPort.dragAndDropObject.mouse.y - e.clientY));
				
				if(left < 0)
					left = 0;
				if(top < 0)
					top = 0;
				if(left+viewPort.dragAndDropObject.width > $('#osDesktop').width())
					left = $('#osDesktop').width() - viewPort.dragAndDropObject.width;
				if(top+viewPort.dragAndDropObject.height > $('#osDesktop').height())
					top = $('#osDesktop').height() - viewPort.dragAndDropObject.height;
				
				viewPort.dragAndDropObject.object.css({ 
					left: left+'px',
					top: top+'px',
					margin: 0
				});
				var dat = JSON.stringify({l:left, t:top, w:viewPort.dragAndDropObject.width, h:viewPort.dragAndDropObject.height});
				viewPort.dragAndDropObject.object.data('pos', dat);
			} else if(viewPort.dragAndDropObject.isStretchable){
				if((viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-n') > -1){
					// kind of north
						var top = (viewPort.dragAndDropObject.start.y - (viewPort.dragAndDropObject.mouse.y - e.clientY));
						var height = (viewPort.dragAndDropObject.height + (viewPort.dragAndDropObject.mouse.y - e.clientY));
						
						if(viewPort.windowProperties.minHeight < height)
							viewPort.dragAndDropObject.object.css({ 
								top: top+'px',
								margin: 0,
								height: height+'px'
							});
				}
				if((viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-ne') > -1 || (viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-e') > -1 || (viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-se') > -1){
					// north already done, do east
						var width = (viewPort.dragAndDropObject.width - (viewPort.dragAndDropObject.mouse.x - e.clientX));
						
						if(viewPort.windowProperties.minWidth < width)
							viewPort.dragAndDropObject.object.css({ 
								margin: 0,
								width: width+'px'
							});
				}
				if((viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-nw') > -1 || (viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-sw') > -1 || (viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-w') > -1){
					// north already done, do east
						var width = (viewPort.dragAndDropObject.width + (viewPort.dragAndDropObject.mouse.x - e.clientX));
						var left = (viewPort.dragAndDropObject.start.x - (viewPort.dragAndDropObject.mouse.x - e.clientX));
						
						if(viewPort.windowProperties.minWidth < width)
							viewPort.dragAndDropObject.object.css({
								left: left+'px', 
								margin: 0,
								width: width+'px'
							});
				}
				if((viewPort.dragAndDropObject.triggerObj.attr('class')).indexOf('-s') > -1){
					// kind of north
						var height = (viewPort.dragAndDropObject.height - (viewPort.dragAndDropObject.mouse.y - e.clientY));
						
						if(viewPort.windowProperties.minHeight < height)
							viewPort.dragAndDropObject.object.css({ 
								top: top+'px',
								margin: 0,
								height: height+'px'
							});
				}
			}
		});
		$('body').on('mouseup', function(){
			viewPort.dragAndDropObject.isMovable = false;
			viewPort.dragAndDropObject.isStretchable = false;
		});
	},
	setWindowOnWindowsObject: function(){
		$('.window-container').each(function(){
			windowPort.add( this );
		});
	},
	bindWindowStretch: function(triggerClass, windowClass){
		// stretchbox 
		$('body').on('mousedown','.'+triggerClass, function( e ){
			viewPort.deepFunctions.loadObjectDimensions( this , windowClass , e );
			viewPort.dragAndDropObject.isStretchable = true;
		});
	},
	toggleFullscreen : function(element){
		if(viewPort.isFullScreen){
			viewPort.deepFunctions.fullscreenLeave();
			$('#'+element).removeClass('color-branding');
			$('#'+element).parent().removeClass('active color-border-branding');
			viewPort.isFullScreen = false;
		} else {
			viewPort.deepFunctions.fullscreenStart();
			$('#'+element).addClass('color-branding');
			$('#'+element).parent().addClass('active color-border-branding');
			
			viewPort.isFullScreen = true;
			console.log('#'+element);
			console.log($('#'+element));
		}
	},
	deepFunctions : {
		fullscreenLeave: function(){
			if (document.exitFullscreen)
		      	document.exitFullscreen();
		    else if (document.msExitFullscreen)
		      	document.msExitFullscreen();
		    else if (document.mozCancelFullScreen)
		      	document.mozCancelFullScreen();
		    else if (document.webkitExitFullscreen)
		      	document.webkitExitFullscreen();
		},
		fullscreenStart: function(){
			if (document.documentElement.requestFullscreen)
     			document.documentElement.requestFullscreen();
		    else if (document.documentElement.msRequestFullscreen)
		      	document.documentElement.msRequestFullscreen();
		    else if (document.documentElement.mozRequestFullScreen)
		      	document.documentElement.mozRequestFullScreen();
		    else if (document.documentElement.webkitRequestFullscreen)
		      	document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		},
		loadObjectDimensions: function(tthis, windowClass , e ){
			// Set Object
				viewPort.dragAndDropObject.object = $(tthis).closest('.'+windowClass);
				viewPort.dragAndDropObject.triggerObj = $(tthis);
				
			var position = viewPort.dragAndDropObject.object.position();
			console.log(viewPort.dragAndDropObject.object);
			
			// Set mouse to object
				viewPort.dragAndDropObject.mouse.x = e.clientX;
				viewPort.dragAndDropObject.mouse.y = e.clientY;
			// Set NE Corner to Object
				viewPort.dragAndDropObject.start.x = position.left;
				viewPort.dragAndDropObject.start.y = position.top;
			// Set width and height to object
				viewPort.dragAndDropObject.width = viewPort.dragAndDropObject.object.width();
				viewPort.dragAndDropObject.height = viewPort.dragAndDropObject.object.height();
		}
	},
	setWindowPosition: function(){
		$('.window-container').each(function(){
			if($(this).hasClass('posCenter')){
				var top = ($('#osDesktop').height()/2 - $(this).height()/2);
				var left = ($('#osDesktop').width()/2 - $(this).width()/2);
				//console.log(top+' '+left+' '+$('#base').height()+' '+$(this).height());
				$(this).css({
					top: top+'px',
					left: left+'px'
				});
			}
		});
	}
};
var windowPort = {
	elements: {
		list: {},
		stack: {
			conf: [],
			explorer: [],
			contact: [],
			charts: []
		},
		toLoad: []
	},
	add: function( jQueryObj ){
		// object needs id
		console.log($(jQueryObj).attr('id'));
		if(typeof $(jQueryObj).attr('id') != "undefined" && $(jQueryObj).attr('id').length > 1){
			// is in list?
			if( !($(jQueryObj).attr('id') in windowPort.elements.list) ){
				// add to list
					windowPort.elements.list[ $(jQueryObj).attr('id') ] = jQueryObj;
				// is loaded?
				if($(jQueryObj).hasClass('toLoad')){
					windowPort.elements.toLoad.push( jQueryObj );
					// add loading animation
					$(jQueryObj).find('.loading-animation').removeClass('hidden');
					console.log("removed?");
					windowPort.load( $(jQueryObj).attr('id') );
				}
				// add to correct stack
					if(typeof $(jQueryObj).attr('data-windowType') != "undefined" && $(jQueryObj).attr('data-windowType').length > 1 && ($(jQueryObj).attr('data-windowType') in windowPort.elements.stack)){
						windowPort.elements.stack[$(jQueryObj).attr('data-windowType')] = jQueryObj;
					}		
			}
		}
	},
	load: function( keyOfObject ){
		if(typeof windowPort.elements.list[keyOfObject] != 'undefined' && typeof $(windowPort.elements.list[keyOfObject]).find('.isUrlZone').data('linkhard') != 'undefined'){
			console.log($(windowPort.elements.list[keyOfObject]).find('.isUrlZone').data('linkhard'));
			windowPort.ajax($(windowPort.elements.list[keyOfObject]).attr('id'), {eID: 1, type: 100, hardLink: $(windowPort.elements.list[keyOfObject]).find('.isUrlZone').data('linkhard')});
		}
	},
	deepLoad: function(keyOfObject, link){
		console.log( keyOfObject );
		if(typeof windowPort.elements.list[keyOfObject] != 'undefined'){
			$( windowPort.elements.list[keyOfObject] ).find('.window-bar-bottom').find('.info-elements').find('p').text('loading elements\u2026');
			$( windowPort.elements.list[keyOfObject] ).find('.loading-animation').removeClass('hidden');
			windowPort.ajax($(windowPort.elements.list[keyOfObject]).attr('id'), {eID: 1, type: 100, hardLink: link});
		}
		// deepLink
	},
	ajax: function(windowX, sObjectX){
		var url = 'https://creezi.com/ajax';
		var success = false;
		
		sObjectX.userhash = uid;
		sObjectX.timestamp = $.now();
		
		var dataX = "userident="+uid+"&data="+JSON.stringify(sObjectX);
		
		$.ajax({
	        url: url,
	        type: 'POST',
	        data: dataX,
	        cache: false,
	        dataType: 'json',
	        success: function(data, textStatus, jqXHR){
	            if(typeof data.data.contentData !== 'undefined'){// && data.data.contentData.length > 0){
	            	success = true;
	            	
	            	console.log(data.data);
					
					if(typeof data.data.contentData[0].addWindow !== 'undefined' && typeof data.data.contentData[0].addWindow[0] !== 'undefined'){
						$( windowPort.elements.list[windowX] ).find('.loading-animation').addClass('hidden');
						$.each(data.data.contentData[0].addWindow, function(){
							var faString = 'fa-folder';
							$(  windowPort.elements.list[windowX]  ).find('.column-right').append('<div class="deepLink container-item container-'+this.type+'" data-linkHard="'+this.link+'"><div class="icon"><i class="fa '+faString+'"></i></div><p class="name">'+this.text+'</p></div>');
						});
						$(  windowPort.elements.list[windowX]  ).find('.window-bar-bottom').find('.info-elements').find('p').text(data.data.contentData[0].addWindow.length+' elements'); 
						//
					}
	            } else {
	            	console.log("fail");
	            	console.log(dataX);
	            }
	            
	        },
	        error: function(jqXHR, textStatus, errorThrown){
				console.log(textStatus);
	        },
	        complete: function(){

	        }
		});
	}
};
