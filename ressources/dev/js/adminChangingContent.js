var x = 0;
var inEditingMode = false;
var tagArray = [];
var tagFocus = {index: 0};

$( document ).ready(function() {
    $(document).on('mouseenter','#content-collection .grid-main-item', function(){
    	$(this).children('.box-control').toggleClass('hidden');
    });
    $(document).on('mouseleave','#content-collection .grid-main-item', function(){
    	$(this).children('.box-control').toggleClass('hidden');
    });
    $(document).on('click','#content-collection .grid-main-item .icon-delete', function(){
    	var thisItemID = $(this).closest('.box-control').data('itemid');
    	$(this).closest('.grid-main-item').attr('id','item-'+x);
    	console.log(thisItemID);
    	
    	sObjectN = sObject;
		sObjectN.cID = $('#theContentInfoBox').data('contentid');
		sObjectN.eID = thisItemID;
		sObjectN.removeCollection = true;
		sObjectN.timestamp = $.now();
		sObjectN.type = 12;
		
		sendToServer(sObjectN,'item-'+x);
    });
    $('.button.admin-private').on('click', function(){
    	addWarning('Changing status <span>:: private ::</span> of this post');
    	
    	sObjectN = sObject;
		sObjectN.cID = $('#theContentInfoBox').data('contentid');
		sObjectN.is_private = Math.abs($('#theContentInfoBox').data('private') - 1);
		sObjectN.timestamp = $.now();
		
		sendToServer(sObjectN,"msg"+x);
    });
    $('#wrap').on('click','button.admin-save', function(){
    	var box = $(this).parent().parent();
    	$(this).remove();
    	
    	box.text('You are in editing mode! saving ...');
    	
    	var tagString = '';
    	$('#admin-edit-taglist-container a').each(function(){
    		$(this).find( "span" ).remove();
    		tagString += $(this).text()+',';
    	});
    	
    	sObjectN = sObject;
		sObjectN.cID = $('#theContentInfoBox').data('contentid');
		sObjectN.is_private = Math.abs($('#theContentInfoBox').data('private'));
		sObjectN.title = $('#admin-edit-title').val(),
		sObjectN.tags = tagString,
		sObjectN.timestamp = $.now();
		sObjectN.description = $('#image-description-box-area').val();
		sObjectN.type = 11;
		
		// create h1 agoin
		$('#admin-edit-title').before('<h1 class="'+$('#admin-edit-title').attr("class")+'">'+$('#admin-edit-title').val()+'</h1>');
    	$('#admin-edit-title').remove();
		
		// remove tags input 
		$('#admin-edit-tags').remove();
		
		// remove description 
		$('#image-description-box').text( $('#image-description-box-area').val() );
		$('#image-description-box-area').remove();
		
		// add tag a
		$('#admin-edit-taglist-container a').each(function(){
			$(this).attr('href','https://creezi.com/t/'+$(this).text());
		});
		sendToServer(sObjectN,box.attr('id'));
		inEditingMode = false;

		$('.button.admin-edit').removeClass('admin-notClickable');
		$('.userContent-x').removeClass('admin-editable');
		tagArray = [];
		tagFocus = {index: 0};
    });
    $('.button.admin-edit').on('click', function(){
    	if(inEditingMode)
    		return;
    	tagArray = ($('#theContentInfoBox').data('alltags')).split(',');
    	//console.log(tagArray);	
    	inEditingMode = true;
    	$('.button.admin-edit').addClass('admin-notClickable');
    	addWarning('You are in editing mode! Save changes with: <button class="admin-save">:: save ::</button>');
    	$('.userContent-x').addClass('admin-editable');
    	$('.userContent-x').each(function(){
    		 
    		 if( $(this).is("h1") ){
    		 	
    		 	// Look for headline or title
    		 	
    		 	$(this).before('<input id="admin-edit-title" class="'+$(this).attr("class")+' transparent" value="'+$(this).text()+'"/>');
    			$(this).remove();
    		 } else if( $(this).is("ul") && $(this).is("#meta-tags") ){
    		 	
    		 	// remove editable flag
    		 	$( this ).removeClass( 'admin-editable' );
    		 	
				// Create New Info Container
    		 	$(this).append('<li id="admin-edit-taglist-container"></li><li id="admin-edit-taginput-container" class="admin-editable"></li>');
    		 	
    		 	// Look for tags and copy them
    		 	$('#meta-tags li.tag').each(function(){
    		 		$('#admin-edit-taglist-container').html( $('#admin-edit-taglist-container').html() + $(this).html() );
    		 		$( this ).remove();
    		 	});
    		 	
    		 	// make tags editable
    		 	$('#admin-edit-taglist-container a').each(function(){
    		 		$( this ).addClass('admin-deletable');
    		 		$( this ).addClass('admin-tag-bubble');
    		 		$( this ).html($( this ).html() + '<span>x</span>');
    		 	});
    		 	
    		 	// create tag input field
    		 	$('#admin-edit-taginput-container').html( '<input data-oldinput="" id="admin-edit-tags" class="adminSemiWhite transparent" value=""/><div class="hidden admin-selectList"></div>' );
    		 	
    		 	//$(this).append('<li><input id="admin-edit-tags"  class="'+$(this).attr("class")+' transparent" value=""/></li>');
    		 } else if( $(this).attr('id') == 'image-description-box' ){
    		 	$( this ).html('<textarea id="image-description-box-area">'+$( this ).text()+'</textarea>');
    		 }
    	}); 
    });
    // Delete set tags
    $('#wrap').on('click','#admin-edit-taglist-container a' , function(e){
    	if(inEditingMode){
	    	e.preventDefault();
	    	$(this).remove();
    	}
    });
    
    // add tag
    $('#wrap').on('click', '.admin-editable .admin-selectList p', function(){
    	$('#admin-edit-tags').val( $(this).text() );
    	$('#admin-edit-tags').data('oldinput', $(this).text());
    	$('#admin-edit-tags').focus();
    	
		$('#admin-edit-taginput-container .admin-selectList').html('');
		$('#admin-edit-taginput-container .admin-selectList').addClass('hidden');
		
		// reset search list
		tagFocus.focus = false;
		tagFocus.index = 0;
   	});
    $('#wrap').on('keyup','#admin-edit-tags', function(e){
    	var keycheck = /[^A-Za-z0-9\- ]/gi;
    	if(inEditingMode){
	    	var keychar = (e.key+'');
	    	var old = $( this ).data('oldinput');
	    	console.log(keychar);
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
	    	} else if (keycheck.test($( this ).val())){
	    		var s = $( this ).val();	    		
	    			s = s.replace(/[^0-9a-z\- ]/gi, '');
	    		
	    			$( this ).val( s );
	    	} else if(keychar.toLowerCase() == 'enter'){
	    		$('#admin-edit-taglist-container').append('<a class="admin-deletable admin-tag-bubble">'+$( this ).val()+'<span>x</span></a>');
	    		$( this ).val('');
	    		$('.admin-selectList').html('');
	    		$('.admin-selectList').addClass('hidden');
	    		
	    		// reset search list
	    		tagFocus.focus = false;
	    		tagFocus.index = 0;
	    		return;
	    	}

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
	    	}
    	}
    });
    
    $('#wrap').on('click','.admin-editable-x', function(){
    	$this = 0;
    	$this.removeClass('admin-editable-x');
    	$this.before('<input class="'+$this.attr("class")+' transparent" value="'+$this.text()+'"/>');
    	$this.remove();
    });
});
function addWarning(text){
	x++;
	$('#grid-main').before('<div class="admin-warning admin-bubble" id="msg'+x+'"><p>'+text+'</p></div>');
}
function addSuccess(text){
	x++;
	$('#grid-main').before('<div class="admin-success admin-bubble" id="msg'+x+'"><p>'+text+'</p></div>');
	$('#msg'+x).fadeOut(3500);
}
var sObject = {
				cID: null,
				title: null,
				tags: null,
				is_private: null,
				is_adult: null,
				description: null,
				userhash: uid,
				timestamp: $.now(),
				type: 10
			};
function sendToServer(sObjectX, msgBox){
	var url = 'https://creezi.com/ajax';
	var success = false;
	var dataX = "userident="+uid+"&data="+JSON.stringify(sObjectX);
	$.ajax({
        url: url,
        type: 'POST',
        data: dataX,
        cache: false,
        dataType: 'json',
        success: function(data, textStatus, jqXHR){
            if(data.data.contentData !== 'undefined' && data.data.contentData.length > 0 && data.data.contentData[0].id == $('#theContentInfoBox').data('contentid')){
            	success = true;
            	console.log(sObjectN.is_private);
            	//console.log(data);
            	$('#theContentInfoBox').data('private',sObjectN.is_private);
            	addSuccess("status successfully changed");
            	if(sObjectN.is_private == 0)
            		$('.admin-private .icon').attr('data-icon','z');
            	else if(sObjectN.is_private == 1)
            		$('.admin-private .icon').attr('data-icon','y');
            } else {
            	console.log("fail");
            	console.log(dataX);
            }
            
        },
        error: function(jqXHR, textStatus, errorThrown){
			console.log(textStatus);
        },
        complete: function(){
			$('#'+msgBox).fadeOut(1500);
        }
	});
}
