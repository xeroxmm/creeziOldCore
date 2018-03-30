/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function(){//'#uploading-area-hook'
	$('body').dragNdrop()
	    .onHoverShow('#dragNdrop')
	    .onDropBufferImages()
	    .onDropCreatePost();
    $('#newItemContainer').onDropAddToPost();
});

var dragNdrop = {
    durationDragEnter: 300,
    durationDragLeave: 100,
    
    ajaxURL: '//creezi.com/ajax',
    ajaxHST: '//creezi.com', //'https://creezi.com',
    
    hasPostFail: false,
    hasPostId: false,
    hasCollectionID: false,
    
    isNewPost: false,
    isExisitingPost: false,
    
    paused: false,
    
    postID: 0,
    postHTML: '',
    postTitle: 'titel',
    postType: 'i',
    
    pictureIncrement: 0,
    
    htmlElements: {
        container4Post: 'wrap',
        container4Img: 'content-image',
        container4Elements:'single-box',
        title: 'xcTitle',
        tags: 'xcTags',
        description: 'xcDescr',
        img: 'xcImg',
        imgPrefix: 'xcNewImg_'
    },
    
    listItems: [],
    
    getDataUploadFormObject: function( param ){
		var uidX = param.get("uniq");
		param.delete("uniq");

    	return $.ajax({
			xhr: function() {
				var myXhr = $.ajaxSettings.xhr();
	            if(myXhr.upload){ // Check if upload property exists
	                myXhr.upload.addEventListener('progress',function(e){
	                	if(e.lengthComputable){
					        console.log(e);
					        $('#'+dragNdrop.htmlElements.imgPrefix+uidX+' .progress .indicator').css({'width':''+(e.loaded/e.total*100)+'%'});
					        console.log('#'+dragNdrop.htmlElements.imgPrefix+uidX+' .progress .indicator >> width : '+(e.loaded/e.total*100)+'%');
					    }
	                }, false); // For handling the progress of the upload
	            }
	            return myXhr;
			},
			url: dragNdrop.ajaxURL,
			type: 'POST',
			data: param,
			cache: false,
			processData: false,
			contentType: false,
			// contentType: "application/json",
			// dataType: "json",
			success: function(result) {
				console.log(result);
			}
    	});
    },    
    getData: function( param ){
        return $.ajax({
                method: "POST",
                url: dragNdrop.ajaxURL,
                data: param,
                success: function (d) {
                    console.log(d);
                },
                error: function () {
                    console.log("ERROR: "+param);
                }
            }
        );
    },
    
    uploadPictures: function(){
        $.each(this.listItems, function(index, value){
            if(!dragNdrop.listItems[index].parsed || dragNdrop.listItems[index].uploading || dragNdrop.listItems[index].uploaded)
                return true;
            
            dragNdrop.listItems[index].uploading = true;
            
            var data = {};
				data.do = {
					type: 0,
					subType: 1,
					info: {
						contentID: dragNdrop.postID,
						elementID: dragNdrop.listItems[index].contentID
					}
				};
				data.timestamp = Math.floor(Date.now() / 1000);
					
			// var str = JSON.stringify(data);
			var fd = new FormData();    
				fd.append( 'file' , dragNdrop.listItems[index].stream );
				fd.append( 'data' , JSON.stringify(data) );
				fd.append( 'userident' , uid );
				fd.append( 'shake' , JSON.stringify({
					uniq: dragNdrop.listItems[index].uniq,
					uniqIndex: index
				}) );
				fd.append('uniq',dragNdrop.listItems[index].uniq);
				fd.append('fileurl',dragNdrop.listItems[index].stringURL);

            dragNdrop.getDataUploadFormObject( fd ).done(function(data){
                console.log(data);
                console.log(dragNdrop.listItems);
                if(typeof data.data.content !== 'undefined' && typeof data.data.content.elements !== 'undefined' && typeof data.shake.uniq !== 'undefined'){
                    dragNdrop.listItems[ data.shake.uniqIndex ].uploaded = true;
                    $('#xcNewImg_'+data.shake.uniq).css(
                    	{"border-color": "#C1E0FF", 
                        "border-width":"5px", 
                        "border-style":"solid"});
                    $('#xcNewImg_'+data.shake.uniq).data('uploadID',data.data.content.elements[0].id);    
                    dragNdrop.listItems[data.shake.uniqIndex].uploaded = true;
                    dragNdrop.uploadPictures();
                }
            });
            
            return false;
        });
    },
    postAddPictureID: function(){
    	if(dragNdrop.paused)
        	return;
        	
        $.each(dragNdrop.listItems, function(index, value){
        	if(dragNdrop.listItems[index].parsing)
                return true;
            
            if(dragNdrop.postType == 'i' && dragNdrop.postID != 0){
				// change posttype
				dragNdrop.paused = true;
				dragNdrop.postBuildCollection();
                return false;
			}
            
            dragNdrop.listItems[index].parsing = true;
            
            // get imageServerID
        	var data = {};
				data.shake = {
					uniq: value.uniq,
					index: index
				};
				data.do = {
					type: 0,
					subType: 0,
					info: {
						contentID: dragNdrop.postID
					}
				};
				data.timestamp = Math.floor(Date.now() / 1000);
				
			// var str = JSON.stringify(data);
			var str = {
				userident: uid,
				data: JSON.stringify(data),
				shake: JSON.stringify(data.shake)
			};

            dragNdrop.getData( str ).done(function( data ){
                if(typeof data.data === 'undefined' || typeof data.data.content === 'undefined' || typeof data.data.content.id === 'undefined'){
                    console.log('post creation failed');
                    console.log(data);
                    dragNdrop.hasPostFail = true;
                    return;
                }
                
                dragNdrop.listItems[data.shake.index].parsed = true;
                dragNdrop.listItems[data.shake.index].contentID = data.data.content.id;
 
 				if(dragNdrop.postID == 0){
 					dragNdrop.postSetID(data.data.content.id);
                    dragNdrop.postCreateTitle('a title');
                    dragNdrop.postCreateURL('i');
				}
				
                dragNdrop.postAddPictures();
                dragNdrop.postAddPictureID();
            }).fail(function( data ){
                console.log('post creation failed II');
                console.log(data);
                
                dragNdrop.hasPostFail = true;
            });
            if(dragNdrop.postID == 0)
            	return false;    
        });
    },
    htmlAddCollectionContainer: function(){
    	if(!($('#collIDField').length)){
    		console.log('html');
    		$('#'+dragNdrop.htmlElements.container4Img).prepend('<div id="collIDField" class="single-box container-editID" data-contentid="'+dragNdrop.postID+'"></div>');
    		$('#collIDField').html('<input class="editable-title single-headline font-size-140 userContent-x admin-editable transparent editable editable-string" placeholder="Enter your collection title" /><textarea class="editable-descr image-description-box-area editable editable-string-block">Description</textarea><p>Enter some information so the folks can find your creezi stuff</p>');
    	}
    },
    postBuildCollection: function(){
    	// get imageServerID
        	var data = {};
				data.shake = {};
				data.do = {
					type: 0,
					subType: 2,
					info: {
						contentID: dragNdrop.postID
					}
				};
				data.timestamp = Math.floor(Date.now() / 1000);
				
			// var str = JSON.stringify(data);
			var str = {
				userident: uid,
				data: JSON.stringify(data),
				shake: JSON.stringify(data.shake)
			};
			
			dragNdrop.getData( str ).done(function( data ){
                if(typeof data.data === 'undefined' || typeof data.data.content === 'undefined' || typeof data.data.content.id === 'undefined'){
                    console.log('post creation failed');
                    console.log(data);
                    dragNdrop.hasPostFail = true;
                    return;
                }
                dragNdrop.postSetID(data.data.content.id);
        		dragNdrop.postCreateTitle('a title');
        		dragNdrop.postCreateURL('c');
        		dragNdrop.paused = false;
        		
        		dragNdrop.htmlAddCollectionContainer();
        		
        		dragNdrop.postAddPictureID();
       		});
    },
    postAddPictures: function(){
        // loop through itemListElements
        console.log(dragNdrop.listItems); 

        if(dragNdrop.paused)
        	return;
        	
        $.each(dragNdrop.listItems, function(index, value){
            if(!dragNdrop.listItems[index].parsed || dragNdrop.listItems[index].binding || dragNdrop.listItems[index].contentID == 0)
                return true;
            
            dragNdrop.pictureIncrement++;
            dragNdrop.listItems[index].binding = true;
            
            $('#'+dragNdrop.htmlElements.container4Img).append('<div id="xc-' + dragNdrop.pictureIncrement + '" data-contentID="'+dragNdrop.listItems[index].contentID+'" class="container-editID ' + dragNdrop.htmlElements.container4Elements + '"></div>');
            
            $('#xc-' + dragNdrop.pictureIncrement).append('<input class="editable-title single-headline font-size-140 userContent-x admin-editable transparent '+dragNdrop.htmlElements.title+' editable editable-string" placeholder="Enter your title" />');
            $('#xc-' + dragNdrop.pictureIncrement).append('<div class="img-container" id="xcNewImg_'+value.uniq+'" ></div>');
            $('#xc-' + dragNdrop.pictureIncrement).append('<div class="meta tags "><ul id="meta-tags" class="single-tagList userContent-x"><li class="admin-edit-taglist-container"></li><li id="admin-edit-taginput-container" class="admin-editable"><input data-oldinput="" id="admin-edit-tags" class="editable editable-tags adminSemiWhite transparent" placeholder="your tags - separate with ,"><div class="hidden admin-selectList"></div></li></ul></div>');
            $('#xc-' + dragNdrop.pictureIncrement).append('<textarea class="editable-descr image-description-box-area '+dragNdrop.htmlElements.description+'editable editable-string-block">Description</textarea>');
        	
        	if(dragNdrop.listItems[index].stringURL === false){
	        	var reader = new FileReader();
	                reader.i = index;
	                reader.iName = value.uniq;
	                reader.onload = function( e ) {
	                    var img = document.createElement('img');
	                    img.src = e.target.result;
	                    $(img).css({'width':'100%'});
	                    $('#xcNewImg_'+this.iName).html(img);
						$('#xcNewImg_'+this.iName).append('<div class="justShadow"></div><div class="element-5px progress-bar progress attached attached-bottom"><div class="xcbar indicator element-5px"></div></div>');
	                    
	                    dragNdrop.listItems[index].binded = true;
	                    dragNdrop.uploadPictures();
	                };
	                reader.readAsDataURL(value.stream);
           } else {
           		if(dragNdrop.listItems[index].imgObject !== false){
           			$('#xcNewImg_'+value.uniq).html( dragNdrop.listItems[index].imgObject );
					$('#xcNewImg_'+value.uniq).append('<div class="justShadow"></div><div class="element-5px progress-bar progress attached attached-bottom"><div class="xcbar indicator element-5px"></div></div>');
           		} else {
           			$('#xcNewImg_'+value.uniq).html( '<img src="'+dragNdrop.listItems[index].stringURL+'" />' );
					$('#xcNewImg_'+value.uniq).append('<div class="justShadow"></div><div class="element-5px progress-bar progress attached attached-bottom"><div class="xcbar indicator element-5px"></div></div>');
           		}
           		dragNdrop.listItems[index].binded = true;
            	dragNdrop.uploadPictures();
           }
        });
        
        return this;
    },
    postSetID: function( id ){
        this.postID = id;
        this.hasPostId = true;
    },
    postCreateHTML: function(){
        if(this.postHTML.length > 1)
        	return;
        	
        html = '<div id="' + dragNdrop.htmlElements.container4Img + '" class="' + dragNdrop.htmlElements.container4Img + '"><div id="saveUploadInfo"><p></p></div></div>';
        this.postHTML = html;
        
        if(dragNdrop.isNewPost){
        	$('#'+dragNdrop.htmlElements.container4Post).html('');
        	$('#'+dragNdrop.htmlElements.container4Post).append( this.postHTML );
        } else {
        	$('#'+dragNdrop.htmlElements.container4Post).prepend( this.postHTML );
        }
    },
    postCreateURL: function( t ){
        var stateObj = { url: "url", innerhtml: document.body.innerHTML };
        window.history.pushState(
            stateObj,
            this.postTitle, 
            this.ajaxHST+'/'+t+'/'+this.postID
        );
        this.postType = t;
    },
    postCreateTitle: function( title ){
        this.postTitle = title;
    },
    isThisImageURL: function( urlX ){
    	if(urlX.length < 1){
    		return false;
    	}

    	var arr = [ "jpeg", "jpg", "gif", "png" ];
		var ext = urlX.substring(urlX.lastIndexOf(".")+1);

		if($.inArray(ext,arr) == -1){

			return false;
		}
		
		// check if it is something from hoster
			if( (/creezi.com\//i.test(urlX)) != false)
				return false;
		
		return true;
    }
};

(function ( $ ){
	$.fn.onDropAddToPost = function(){
		if(this.length && typeof thePostID !== 'undefined' && typeof thePostType !== 'undefined'){
			dragNdrop.isExisitingPost = true;
			dragNdrop.postID = thePostID;
			dragNdrop.postType = thePostType;
			dragNdrop.htmlElements.container4Post = this.attr('id');
		}
	},
    $.fn.dragNdrop = function() {
    	$(document).on('hover','.img-container', function(e){
    		$("#fleet").animate({ "height": 'inherit' }, "slow");
    	});
        // bind dragNdropEvents
            this.on('dragenter', function(e){
                e.stopPropagation(); 
                e.preventDefault();
            });
            this.on('dragover', function(e){
                e.stopPropagation(); 
                e.preventDefault();
            });
            this.on('drop', function(e){
                e.stopPropagation(); 
                e.preventDefault();
            });
            this.on('dragleave', function(e){
                e.stopPropagation(); 
                e.preventDefault();
            });
        return this;
    };
    $.fn.onHoverShow = function( identifier ){
        if($(identifier).length){
            this.on('dragenter', function(e){
                if(dragNdrop.isNewPost)
                	return;
                $(identifier).removeClass('hidden');
            });
            this.on('dragleave', function(e){
                if(dragNdrop.isNewPost)
                	return;
            });
            this.on('drop', function (e) {
            	if(dragNdrop.isNewPost)
                	return;
            	$(identifier).addClass('hidden');
            });
            $(identifier).on('mouseleave', function(e){
            	if(dragNdrop.isNewPost)
                	return;
            	$(this).addClass('hidden');
            });
            $(identifier).on('dragleave', function(e){
            	if(dragNdrop.isNewPost)
                	return;
            	$(this).addClass('hidden');
            });
        }
        return this;
    };
    $.fn.onDropBufferImages = function(){
        this.on('drop', function (e) { 
            e.stopPropagation(); 
            e.preventDefault();

			var urlJ = false;
			
			if(typeof e.originalEvent.dataTransfer === 'undefined')
				return;
			
			var urlString = e.originalEvent.dataTransfer.getData('text/plain');
			var urlObject = e.originalEvent.dataTransfer.getData('text/html');

            if(
            	(typeof e.originalEvent.dataTransfer.files === 'undefined' || e.originalEvent.dataTransfer.files.length < 1) &&
            	!urlString.length
            )
        		return;
			
			if(urlString.length && (urlJ = dragNdrop.isThisImageURL( urlString )) == false )
				return;
			
			console.log(urlString);
			console.log(urlObject);
            // iterate through files
            for (var i = 0, file; file = e.originalEvent.dataTransfer.files[i]; i++) {
				if (file.type.match(/image.*/)) {
                    var iName 	= file.name;
                    var iSize   = file.size;
                    var uniq    = strhash(iName+""+iSize);
					console.log('isImage');
                    // add item to DragNdropList
                    dragNdrop.listItems.push(
                        {
                            uniq: uniq,
                            name: iName,
                            size: iSize,
                            stream: file,
                            isStringRaw: false,
                            stringURL: false,
                            imgObject: false,
                            
                            contentID: 0,		// ServerContentID
                            parsing: false,		// getting ServerContentID
                            parsed: false,		// file with ID
                            
                            binding: false,		// binding into DOM
                            binded: false,		// binded into DOM
                            
                            uploading: false,	// sending to Server
                            uploaded: false		// server got the file
                        }
                    );
				}
            };
            if(urlString.length){
	            var isStringRaw = true;
	            var stringURL = urlString;
	            	if(stringURL.indexOf('http://') == -1 && stringURL.indexOf('https://') == -1)
	            		stringURL = 'http://'+stringURL;
	            var imgObject = false;
	            
	            if(urlObject.length){
	            	var imageUrl = urlObject;
	    				
	    				imgObject = $(imageUrl);
	    				
	    				isStringRaw = false;
	            }
	            
            	var iName 	= stringURL;
                var iSize   = 1;
                var uniq    = strhash(iName+""+iSize);
				console.log('isImage');
                // add item to DragNdropList
                dragNdrop.listItems.push(
                    {
                        uniq: uniq,
                        name: iName,
                        size: iSize,
                        stream: false,
                        isStringRaw: isStringRaw,
                        stringURL: stringURL,
                        imgObject: imgObject,
                        
                        contentID: 0,		// ServerContentID
                        parsing: false,		// getting ServerContentID
                        parsed: false,		// file with ID
                        
                        binding: false,		// binding into DOM
                        binded: false,		// binded into DOM
                        
                        uploading: false,	// sending to Server
                        uploaded: false		// server got the file
                    }
                );
            }
            console.log('end of loop');
		});
        return this;
    },
    $.fn.onDropCreatePost = function(){
        // check if post exists
        this.on('drop', function (e) {    
            if(!$('#xcContainer').length){
	        	dragNdrop.isNewPost = true;
	        	dragNdrop.postCreateHTML();
            }
            dragNdrop.postAddPictureID();
        });
        // add to post
    };
}(jQuery));