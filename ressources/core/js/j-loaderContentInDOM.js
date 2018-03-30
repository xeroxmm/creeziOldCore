$(function(){//'#uploading-area-hook'
	$('a.loadInDOM').loadContentInDOM( true );
});
(function ( $ ){
	var theXCSelector = '';
	
	$.fn.loadContentInDOM = function( useParent ){
		theXCSelector = this.selector;
		if(typeof useParent !== 'undefined' && useParent === true){
			inDOMLoader.useOnParent = true;
		}
		
		$( document ).on('click', this.selector , function( event ){
			(event.preventDefault) ? event.preventDefault() : event.returnValue = false;
			
			inDOMLoader.open( $(this) );
		});
	    
	    $(document).on('scroll', function(){
	    	if($('h1.globalheadline').length){
	    		var scrollPos = ($('h1.globalheadline').offset()).top - $('#head').outerHeight();
	    		// if lower than h1 * 0.5
		    		if($(document).scrollTop() > scrollPos + 0.5*$('h1.globalheadline').height()){
		    	// if div nox exist create
		    			if(!$('.siteInfoBoxBroadcaster .centered .info').length)
		    				$('.siteInfoBoxBroadcaster .centered').html('<div class="goLeft clickable hidden w2 isInline element"><i class="fa fa-arrow-circle-o-left"></i></div><div class="info isInline element"></div><div class="goRight clickable hidden w2 isInline element"><i class="fa fa-arrow-circle-o-right"></i></div>');
		    	// show headline in broadcast box
		    			$('.siteInfoBoxBroadcaster .centered .info').text( $('h1.globalheadline').text() );		
		    	// show not in boxc
		    		} else if($(document).scrollTop()){
		    			if(typeof $('.grid-main-content').first().attr('class') === 'undefined'){
		    				$('.siteInfoBoxBroadcaster .centered .info').text('');
		    				$('.siteInfoBoxBroadcaster .centered .goLeft').addClass('hidden');
		    				$('.siteInfoBoxBroadcaster .centered .goRight').addClass('hidden');
		    			} else {
		    				$('.siteInfoBoxBroadcaster .centered .info').text('flip through');
		    			}
		    		}
		    		if(typeof $('.grid-main-content').first().attr('class') !== 'undefined' && $('.siteInfoBoxBroadcaster .centered .goLeft').hasClass('hidden')){
		    			$('.siteInfoBoxBroadcaster .centered .goLeft').removeClass('hidden');
	    				$('.siteInfoBoxBroadcaster .centered .goRight').removeClass('hidden');
		    		}
	    	}
	    	
	    	$('h1.globalheadline').prop('scrollHeight');
	    });
	
		$( document ).on('click', '.siteInfoBoxBroadcaster .centered .element', function(){
			var nextElem = 0;
			if( $(this).hasClass('goLeft') ){
		// go one element to the left
				nextElem = -1;	
			} else if( $(this).hasClass('goRight') ){
				// go one element to the left
				nextElem = 1;	
			}
			if( nextElem === 0 ){
				return;
			}
			$('.grid-main-content').each(function(){
				var Obj = null;
				if($(this).hasClass('hidden')){
					return true;
				}
				if(nextElem == -1)	{
					var prevObj = $(this).prev().prevAll('.grid-main-item').first().find(theXCSelector);
					//console.log($(this));
					//console.log(theXCSelector);

					if( typeof prevObj.attr('href') === 'undefined'){
				// get last element
						prevObj = $('#grid-main .grid-main-item '+theXCSelector).last();
					}
					Obj = prevObj;
				} else {
					var nextObj = $(this).next('.grid-main-item').find(theXCSelector);

					if( typeof nextObj.attr('href') === 'undefined' ){
				// get next element
						console.log('change item');
						nextObj = $('#grid-main .grid-main-item '+theXCSelector).first();
					}
					Obj = nextObj;
				}
				inDOMLoader.open( Obj );
				return false;
			});
		});
	};
	$.fn.goTo = function() {
        // console.log("S "+($(this).offset().top + 150)+" --> "+$(document).scrollTop());
        var offset = Math.abs( ($(this).offset().top + 150) - $(document).scrollTop() );
        if(offset < 0){
	        $('body').animate({
	            scrollTop: ($(this).offset().top + 150) + 'px'
	        }, 'fast');
        }
        return this; // for chaining...
    };
})(jQuery);
var inDOMLoader = {
	isOpen: false,
	isOpenID: '',
	openObjects: [],
	useOnParent: false,
	ajaxURL: ajax.url,
	
	open: function( jQueryObject ){
		var url = jQueryObject.attr('href');
		if(url.length){
			var uniq = strhash(url);
			//jQueryObject.parent('div').first().goTo();
			
			if( !inDOMLoader.isElementLoaded( uniq ) ){
				inDOMLoader.addContainerToDOM( jQueryObject , uniq );
				
				var data = {};
				data.shake = {
					uniq: uniq,
					url: url
				};
				data.do = {
					type: 200,
					subType: 1,
					info: {
						url: url
					}
				};
				data.timestamp = Math.floor(Date.now() / 1000);
				
				var str = {
					userident: (typeof uid !== 'undefined') ? uid : 0,
					data: JSON.stringify(data),
					shake: JSON.stringify(data.shake)
				};
				ajax.getData( str ).done(function( data ){
					if(typeof data.data.content.info !== 'undefined'){
						var html = $.parseJSON(data.data.content.info);
						inDOMLoader.openObjects[ uniq ].html = html.html;
						inDOMLoader.openObjects[ uniq ].loaded = true;
						
						inDOMLoader.openObjects[ uniq ].info.title = "this title";
						inDOMLoader.openObjects[ uniq ].info.url = data.shake.url+'?post';
												
						$('#xcDOML_'+uniq).html( html.html );
						inDOMLoader.openObjects[ uniq ].binded = true;
						inDOMLoader.openObjects[ uniq ].show();
					}
				});
			} else {
				inDOMLoader.openObjects[ uniq ].show( jQueryObject );
			}
		}
	},
	addContainerToDOM: function( jQueryObject , uniq ){
		if(!inDOMLoader.useOnParent)
			jQueryObject.after('<div class="grid-main-content hidden" id="xcDOML_'+uniq+'"></div>');
		else
			jQueryObject.parent().first().after('<div class="grid-main-content hidden" id="xcDOML_'+uniq+'"></div>');
			
		inDOMLoader.openObjects[ uniq ] = {
			loading: true,
			loaded: false,
			binded: false,
			html: false,
			info: {
				url: '',
				title: ''
			},
			visible: false,
			uniq: uniq,
			show: function( jQueryObject ){				
				var theOne = 'xcDOML_'+this.uniq;
				if(!$('#'+theOne).hasClass('hidden') && $('#'+theOne).length){
					$('#'+theOne).goTo();
					return;
				}
				var alreadyUp = false, isEach = false;
				var alreadyObj = null;
				
				$('#'+theOne).removeClass('hidden');
				
				$('.grid-main-content').each(function(){
					if( ($(this).attr('id')) == theOne ){
						alreadyObj = $(this);
						alreadyObj.removeClass('hidden'); 
						//alreadyObj.css({height:'0px'});
						alreadyUp = true;
						
					} else if(!$(this).hasClass('hidden')){
						isEach = true;
						//$(this).animate({height: "0px"}, 0, function(){
						$(this).addClass('hidden');
							if(alreadyUp){
								alreadyUp = false;
								var img = alreadyObj.find('img.single-image').first();
								if (img.prop('complete')) {
									var exp = alreadyObj;
								    	exp.css('visibility', 'hidden');
								    	exp.css('height', 'auto');
								    var newHeight = exp.height();
										exp.css({height: '0px'});
										exp.css('visibility', 'visible');
										
									alreadyObj.animate({height: newHeight+"px"}, 0, function(){
										//$('#'+theOne).goTo();
									});
								} else {
							  		img.load(function(){
									  	var exp = alreadyObj;
									    	exp.css('visibility', 'hidden');
									    	exp.css('height', 'auto');
									    var newHeight = exp.height();
											exp.css({height: '0px'});
											exp.css('visibility', 'visible');
											
										alreadyObj.animate({height: newHeight+"px"}, 0, function(){
											//$('#'+theOne).goTo();
										});
							  		});
								}
							}
						//});
					}
				});
				
				if(!isEach){
					alreadyUp = false;
					var img = alreadyObj.find('img.single-image').first();
					if (img.prop('complete')) {
						var exp = alreadyObj;
					    	exp.css('visibility', 'hidden');
					    	exp.css('height', 'auto');
					    var newHeight = exp.height();
							exp.css({height: '0px'});
							exp.css('visibility', 'visible');
							
						alreadyObj.animate({height: newHeight+"px"}, 0, function(){
							//$('#'+theOne).goTo();
							});
					} else {
				  		img.load(function(){
						  	var exp = alreadyObj;
						    	exp.css('visibility', 'hidden');
						    	exp.css('height', 'auto');
						    var newHeight = exp.height();
								exp.css({height: '0px'});
								exp.css('visibility', 'visible');
								
							alreadyObj.animate({height: newHeight+"px"}, 0, function(){
								//$('#'+theOne).goTo();
								});
				  		});
					}
					
				}
				if(!$('#'+theOne).length && this.html !== false){
					if(!inDOMLoader.useOnParent)
						jQueryObject.after('<div class="grid-main-content" id="'+theOne+'">'+this.html+'</div>');
					else
						jQueryObject.parent().first().after('<div class="grid-main-content" id="'+theOne+'">'+this.html+'</div>');
					$('#'+theOne).goTo();	
				}
				inDOMLoader.isOpenID = this.uniq;
				inDOMLoader.openObjects[ this.uniq ].changeInfo( this.info.title, this.info.url );
				
			},
			hide: function(){
				$('#xcDOML_'+this.uniq).animate(
					{height: "0px"}, 750, function(){
						$(this).addClass('hidden');
					});
			},
			changeInfo: function(title, url){
				var stateObj = { url: url, innerhtml: document.body.innerHTML };
				window.history.pushState(
		            stateObj,
		            title, 
		            url
		        );
			}
		};
	},
	isElementLoaded: function( uniq ){
		return (typeof inDOMLoader.openObjects[ uniq ] !== 'undefined');
	}
};
window.addEventListener("popstate", function(e) {

	// URL location
		var location = document.location;
	
	// state
		var state = e.state;
		
	// return to last state
		if(state!== null && typeof state.innerhtml !== 'undefined')
	    	document.body.innerHTML = state.innerhtml;
		else if(state == null){
			location.reload();
		} 
		//	window.location = document.location;
});