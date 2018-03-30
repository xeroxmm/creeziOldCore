var tagFocus = {index: 0};
$(function(){
	$(document).on('click','.editable-delete', function(e){
		if(!$(this).data('liveIndex')){
			$(this).data('liveIndex' , inputTracingLive.getNextIndex());
			$(this).addClass('isTracing-'+$(this).data('liveIndex'));
			$(this).data('contentid', $(this).closest('.container-editID').data('contentid') );
		}
		if($(this).hasClass('editable-delete-tag')){
			$(this).data('type',4);
			if( $(this).closest('a').find('.new').first().text().length ){
		    	inputTracingLive.setTimeout( $(this).data('liveIndex'), $(this).closest('a').find('.new').first().text() , $(this).data('type'), $(this).data('contentid'));
		    }
		    $(this).closest('a').remove();
   		}
	});
	$(document).on('keyup','.editable', function(e){
		if(!$(this).data('liveIndex')){
			$(this).data('liveIndex' , inputTracingLive.getNextIndex());
			$(this).addClass('isTracing-'+$(this).data('liveIndex'));
			$(this).data('contentid',$(this).closest('.container-editID').data('contentid'));
			
			if($(this).hasClass('editable-title'))
				$(this).data('type',1);
			else if($(this).hasClass('editable-tags'))
				$(this).data('type',2);
			else if($(this).hasClass('editable-descr'))
				$(this).data('type',3);
			else if($(this).hasClass('editable-delete-tag'))
				$(this).data('type',4);	
			else
				return;
		}
		if(!$(this).hasClass('editable-tags')){
			inputTracingLive.clearTimeout( $(this).data('liveIndex') );
		    if ($(this).val()) {
		        inputTracingLive.setTimeout( $(this).data('liveIndex'), $(this).val() , $(this).data('type'), $(this).data('contentid'));
		    }
		} else {
			// only send tag values after pressing enter, komma, semicolion or hashtag
			console.log(e);
			if( true ){
				var keycheck = /[^A-Za-z0-9\- ]/gi;
				var keychar = (e.key+'');
			    var old = $( this ).data('oldinput');

		    	$( this ).val( ($( this ).val() ).toLowerCase() );
		    	if(keychar.toLowerCase() == 'arrowdown' && !$('.admin-selectList').hasClass('hidden')){
		    		if(tagFocus.index <= 0)
		    			$( this ).data('oldinput', $( this ).val());
		    		
		    		tagFocus.focus = true;
		    			
		    		if(tagFocus.index < $('#admin-edit-taginput-container .admin-selectList p').length){
		    			$('#admin-edit-taginput-container .admin-selectList p').removeClass('hover');
		    			tagFocus.index++;
		    			$('#admin-edit-taginput-container .admin-selectList p:nth-child('+tagFocus.index+')').addClass('hover');
		    			$( this ).val( $('#admin-edit-taginput-container .admin-selectList p:nth-child('+tagFocus.index+')').text() );
		    		}
	
		    		return;
		    	} else if(keychar.toLowerCase() == 'arrowup' && !$('.admin-selectList').hasClass('hidden')){
		    		if(tagFocus.index <= 0)
		    			return;
		    			
	    			$('#admin-edit-taginput-container .admin-selectList p').removeClass('hover');
	    			tagFocus.index--;
	    			$('#admin-edit-taginput-container .admin-selectList p:nth-child('+tagFocus.index+')').addClass('hover');
	    			$( this ).val( $('#admin-edit-taginput-container .admin-selectList p:nth-child('+tagFocus.index+')').text() );
	
					if(tagFocus.index <= 0){
		    			$( this ).val( $( this ).data('oldinput') );
		    			tagFocus.focus = false;
					}
		    		return;
		    	} else if(inputTracingLive.isPartitionKey( keychar.toLowerCase() )){
		    		console.log("e");
		    		
		    		var s = $( this ).val();	    		
		    			s = s.replace(/[^0-9a-z\- ]/gi, '');
		    		
		    			$( this ).val( inputTracingLive.trim(s,'-') );
		    		
		    		var inObject = {};
		    		// copy only if this value is not availabele
			    		$(this).closest('ul').find('.admin-edit-taglist-container a').each(function(index){
			    			inObject[$( this ).find('span.new').first().text()] = 1;	
			    		});

			    		if(typeof inObject[$( this ).val()] === 'undefined'){
		    				// send tag to server
				    			inputTracingLive.clearTimeout( $(this).data('liveIndex') );
							    if ($(this).val()) {
							        inputTracingLive.setTimeout( $(this).data('liveIndex'), $(this).val() , $(this).data('type'), $(this).data('contentid'));
							    }
						    // copy info to list		
			    				$( this	).closest('ul').find('.admin-edit-taglist-container').first().append('<a class="admin-deletable admin-tag-bubble"><span class="new">'+$( this ).val()+'</span><span class="editable-delete editable-delete-tag">x</span></a>');
			    		
						}
		    		
					 // clear html stuff   
		    			$( this ).val('');
			    		$( this	).closest('.admin-selectList').html('');
			    		$( this ).closest('.admin-selectList').addClass('hidden');
		    		
		    		// reset search list
			    		tagFocus.focus = false;
			    		tagFocus.index = 0;
		    		
		    		return;
		    	} else if (keycheck.test($( this ).val())){
		    		var s = $( this ).val();	    		
		    			s = s.replace(/[^0-9a-z\- ]/gi, '');
		    		
		    			$( this ).val( s );
		    	}
				/*
		    	var valInp = $( this ).val();
		    	$('.admin-selectList').html('');
		    	$('.admin-selectList').addClass('hidden');
		    	if(valInp.length > 0){
			    	var z = 0;
			    	$.each(tagArray,function(key, val){
			    		if(z > 3)
			    			return;
		
			    		if( val.substring(0, valInp.length) == valInp){
			    			var sub = val.substring(valInp.length);
			    			
			    			$('.admin-selectList').append('<p>'+valInp+'<span>'+sub+'</span></p>');
			    			z++;
			    			$('.admin-selectList').removeClass('hidden');
			    		}
			    	});
		    	}*/
			}
		}
	});
});

var inputTracingLive = {
	timerDown: 0,
	timerInterval: 750,
	theLoop: null,
	
	theIndex: 0,
	
	theValues: [],
	theValueDummy: {
		name: '',
		value: '',
		time: 0,
		theLoop: null
	},
	trim: function trim (str, charlist) {
  		var whitespace = [
    		' ',
		    '\n',
		    '\r',
		    '\t',
		    '\f',
		    '\x0b',
		    '\xa0',
		    '\u2000',
		    '\u2001',
		    '\u2002',
		    '\u2003',
		    '\u2004',
		    '\u2005',
		    '\u2006',
		    '\u2007',
		    '\u2008',
		    '\u2009',
		    '\u200a',
		    '\u200b',
		    '\u2028',
		    '\u2029',
		    '\u3000'
		].join('');
	  	var l = 0;
	  	var i = 0;
	  	str += '';

		if (charlist) {
			whitespace = (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^:])/g, '$1');
		}

	  	l = str.length;
		for (i = 0; i < l; i++) {
		    if (whitespace.indexOf(str.charAt(i)) === -1) {
	      		str = str.substring(i);
	      		break;
		    }
	  	}

  		l = str.length;
	  	for (i = l - 1; i >= 0; i--) {
	    	if (whitespace.indexOf(str.charAt(i)) === -1) {
	      		str = str.substring(0, i + 1);
	      		break;
		    }
	  	}

		return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
	},
	isPartitionKey: function( key ){
		if($.inArray( key, [".","#",";",",","Enter","enter"] ) == -1)
			return false;
		
		return true;
	},
	clearTimeout: function( index ){
		this.theValues[ index ].time = 0;
		clearTimeout( this.theValues[ index ].theLoop );
	},
	setTimeout: function( index , value , type, contentID){
		
		this.theValues[ index ].value = value;
		this.theValues[ index ].theLoop = setTimeout(this.sendLiveEvent, this.timerInterval, index);
		this.theValues[ index ].time = Date.now;
		subType: this.theValues[ index ].type = type;
		contentID: this.theValues[ index ].contentID = contentID;
	},
	sendLiveEvent: function(index){
		var data = {};
			data.shake = {
				index: index
			};
			data.do = {
				type: 1,
				subType: inputTracingLive.theValues[ index ].type,
				info: {
					contentID: inputTracingLive.theValues[ index ].contentID,
					value: inputTracingLive.theValues[ index ].value
				}
			};
			data.timestamp = Math.floor(Date.now() / 1000);
			
		// var str = JSON.stringify(data);
		var str = {
			userident: uid,
			data: JSON.stringify(data),
			shake: JSON.stringify(data.shake)
		};
		ajax.getData( str );
	},
	getNextIndex: function(){
		this.theIndex++;
		this.theValues[this.theIndex] = {
			name: '',
			value: '',
			time: 0,
			type: 0,
			contentID: 0
		};
		
		return this.theIndex;
	}
};
