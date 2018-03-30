(function( $, window, undefined ) {
	
})(jQuery, this);
var counterDrag = 0;
(function($) {
	$(document).on('dragenter', function (e) { 
		e.stopPropagation(); 
		e.preventDefault();
		console.log('enter');
		if(counterDrag == 0){
			showDragAndDropArea();
			highlightDragAndDropArea();
		}
		counterDrag++;
	});
	$(document).on('dragover', function (e) { 
		e.stopPropagation(); 
		e.preventDefault(); 
		console.log('over');
	});
	$(document).on('drop', function (e) { 
		e.stopPropagation(); 
		e.preventDefault();
		console.log('drop');
		console.log(e.originalEvent);
		
		doDragAndDropStuff(e.originalEvent);
	});
	$(document).on('dragleave', function (e) { 
		e.stopPropagation(); 
		e.preventDefault();
		console.log('leave');
		counterDrag--;
		
		if(counterDrag === 0){
			hideDragAndDrop();
			deHighlightDragAndDropArea();
		}
	});
	
	$(document).on("click", '.an-element .btn-remove', function(){
		dragCart.pictures--;
		if(dragCart.pictures <= 0){
			dragCart.pictures = 0;
			dragCart.files = {};
		}
		$(this).closest('.an-element').remove();
		var j = $(this).data("uploadid");
		delete dragCart.files[j];
		updateMediaCounter();
		console.log("hit rteemove button "+j);
		console.log(dragCart);
	});
	$(document).on("click","#contentAddButton a", function(e){
		e.preventDefault();
		e.stopPropagation();
		if( $(this).hasClass('disabledA') )
			return;
		
		// uploadMedia();
		addMediaToCollection();
	});
	
	$("#mediaBox").on("keydown", '.textBoxInd', function(){
		return;
		var boxName = $(this).attr("name");
		var boxID = $(this).attr("id");
		console.log(boxID);
		if(!keypressHandler.hasOwnProperty(boxID)){
			keypressHandler[boxID] = 0;
		};

		showInfoUpdateTyping( boxName );
		console.log($('#'+boxName+'-text').attr("class"));
		

		keypressHandler[boxID] = Date.now();	
		 
	});
	$("#mediaBox").on("keyup", '.textBoxInd', function(){
		return;
		var boxName = $(this).attr("name");
		var boxID = $(this).attr("id");
		var dataIndex = $('#'+boxID).attr('data-index');
		
		console.log("keyup triggered "+boxID+ " " + $(this).val());
		console.log(keypressHandler);	
		if(!keypressHandler.hasOwnProperty(boxID))
			return;
		
		//setTimeout(function(){
			//var gap = Date.now() - keypressHandler[boxID];
			var val1 = $('#'+boxID).val();
			//if(gap > 900){
				showInfoUpdateUpload( boxName );
				
				sendToServerInfoValues( val1 , $('#picMetaBox-'+dataIndex).attr('data-picindex') , boxName );
			//}
		//	console.log(gap);
		//}, 1000);
	});
	$('#mediaBox').on("change", '.textBoxInd', function(){
		var boxName = $(this).attr("name");
		var boxID = $(this).attr("id");
		var dataIndex = $('#'+boxID).attr('data-index');
		
		console.log("changed triggered "+boxID+ " " + $(this).val());
		showInfoUpdateUpload(boxName);
		
		var doTyp = -1;
	
		if (boxName.indexOf( 'title-' ) != -1){
			doTyp = 1;
			ajaxObject.addContentIDAndTitle($('#picMetaBox-'+dataIndex).attr('data-picindex'), $(this).val());	
		} else if (boxName.indexOf( 'tags-' ) != -1){
			doTyp = 2;
			ajaxObject.addContentIDAndTags($('#picMetaBox-'+dataIndex).attr('data-picindex'), $(this).val());	
		} else if(boxName.indexOf( 'desc-' ) != -1){
			doTyp = 3;
			ajaxObject.addContentIDAndDescription($('#picMetaBox-'+dataIndex).attr('data-picindex'), $(this).val());	
		}
	});
	$('#uploading-area').on('click','.showUploadBoxesMultiContent', function (){
		// show Article Box
		$('.aHeadliner').removeClass('hidden');
		dragCart.isCollection = true;
		$('#controls-mediaBox').text('publish collection');
	});
	$('#uploading-area').on('click','.showUploadBoxesSingleContent', function(){
		// show Article Box
		$('.aHeadliner').addClass('hidden');
		dragCart.isCollection = false;
		$('#controls-mediaBox').text('publish as single images');
	});
})(jQuery, this);

function sendAjaxRequestToServer(){
	
}

var keypressHandler = {
	b1: 0
};

function setInfoFieldTitle( string , index){
	$('#add-media-container-'+index+' .theName .line1').html('<b>'+string+'</b>');
	$("#controls-mediaBox").removeClass('disabledA');
}

function setInfoFieldTags( string , index ){
	var tags = string.split(",");
	var html = '';
	
	var string;
	
	for(var i = 0; i < tags.length; i++){
		string = tags[i];
		
		if(string.length >= 2)
			html += '<div class="tagBox">'+string+' <span class="delete"><i class="fa fa-times"></i></span></div>';
	}
	
	$('#add-media-container-'+index+' .theName .line2').html(html);
}

function setInfoFieldDescription( string , index ){
	
}

function showInfoUpdateSuccess(boxName){
	$('#'+boxName+'-text').removeClass("hidden");
	$('#'+boxName+'-type').addClass("hidden");
	$('#'+boxName+'-upload').addClass("hidden");
	$('#'+boxName+'-error').addClass("hidden");
}

function showInfoUpdateUpload(boxName){
	$('#'+boxName+'-text').addClass("hidden");
	$('#'+boxName+'-type').addClass("hidden");
	$('#'+boxName+'-upload').removeClass("hidden");
	$('#'+boxName+'-error').addClass("hidden");
}

function showInfoUpdateTyping(boxName){
	$('#'+boxName+'-text').addClass("hidden");
	$('#'+boxName+'-type').removeClass("hidden");
	$('#'+boxName+'-upload').addClass("hidden");
	$('#'+boxName+'-error').addClass("hidden");
}

function showInfoUpdateError(boxName){
	$('#'+boxName+'-text').addClass("hidden");
	$('#'+boxName+'-type').addClass("hidden");
	$('#'+boxName+'-upload').addClass("hidden");
	$('#'+boxName+'-error').removeClass("hidden");
}

function showUploadFinalSuccess(string){
	$('#controls-mediaBox').html('DONE <i class="fa fa-check-circle-o fa-2"></i>');
	hideMetaEditBox();
	var res = string.split(",");
	var inx;
	$('.picMetaBox').each(function() {
		ind = $( this ).attr('data-picindex');
		if( $.inArray(ind, res) == -1 ){
			inx = $( this ).children('.textBoxInd').attr('data-index');
			$('#add-media-container-'+inx+' .btn-success').addClass("hidden");
			$('#add-media-container-'+inx+' .btn-error').removeClass("hidden");
		}
	});
}

function showUploadFinalError(){
	$('#controls-mediaBox').html('Error <i class="fa fa-times fa-2"></i>');
	hideMetaEditBox();
}

function hideMetaEditBox(){
	$('.picMetaBox').addClass("hidden");
}

function sendToServerPublishPictures(){
	var pics = '';
	var ind = 0;
	var errorTitles = [];
	var goodOnes = [];
	
	$('.picMetaBox').each(function() {
		var myID = $( this ).attr('id');
		var elementIntID = parseInt(myID.replace('picMetaBox-', ''));
		ind = $( this ).attr('data-picindex');
		if(ind != -1 && ind != '-1' && ind != '' && ind != '0' && ind != 0){
			pics += ind+',' ;
			
			if( ($('#'+myID+' #title-'+elementIntID).val()).length < 1 ){
				errorTitles[elementIntID] = false;
				$('#'+myID+' #title-'+elementIntID).css({"border-color": "#FF0000","border-width":"2px","border-style":"solid"});
			} else {
				goodOnes[elementIntID] = true;
			}
		}
	});
	
	if(errorTitles.length > 0)
		return;
	
	ajaxObject.filling();
	
	for(var key in goodOnes) {
		if (goodOnes.hasOwnProperty(key)) {
	    	ajaxObject.addContentIDAndTitle($('#picMetaBox-'+key).data('picindex') ,$('#picMetaBox-'+key+' #title-'+key).val());
			if($('#picMetaBox-'+key+' #tags-'+key).val().length > 0)
				ajaxObject.addContentIDAndTags($('#picMetaBox-'+key).data('picindex') ,$('#picMetaBox-'+key+' #tags-'+key).val());
			if($('#picMetaBox-'+key+' #desc-'+key).val().length > 0)
				ajaxObject.addContentIDAndDescription($('#picMetaBox-'+key).data('picindex') ,$('#picMetaBox-'+key+' #desc-'+key).val());
			ajaxObject.addContentIDforPublish($('#picMetaBox-'+key).data('picindex'), '');
		}
	}
	
	ajaxObject.tObject.data.contentInformation.typ5.array.title = $('#articleBoxTitle').val();
	ajaxObject.tObject.data.contentInformation.typ5.array.descr = $('#articleBoxDescription').val();
	ajaxObject.fillingDone();
	
	if(dragCart.isCollection){
		ajaxObject.tObject.data.contentInformation.typ5.array.title = $('#articleBoxTitle').val();
		ajaxObject.tObject.data.contentInformation.typ5.array.descr = $('#articleBoxDescription').val();
	}
	ajaxObject.sendAjaxRequestToServer();
	
	return;
	
	var data = new FormData();
		data.append('do', 4 );
		data.append('aUpload',3);
		data.append('val', pics);
		data.append('aText', 'NULL');

	$.ajax({
        url: dragCart.url,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(data, textStatus, jqXHR){
            if(typeof data.data.error === 'undefined' || data.data.error == "0" ){
                // Success so call function to process the form
                console.log(data);
                
				showUploadFinalSuccess(data.data.lastInfo);
					
            } else {
                // Handle errors here
                console.log(data.data);
                
				showUploadFinalError();
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            // Handle errors here
            console.log('ERRORS: ' + textStatus + " - " + errorThrown + " ");
            console.log(jqXHR);
            // STOP LOADING SPINNER
            showUploadFinalError();
        },
        complete: function(){

        }
    });
}

function sendToServerInfoValues(textValue, picInd, boxName){
	var doTyp = -1;
	
	if (boxName.indexOf( 'title-' ) != -1)
		doTyp = 1;
	else if (boxName.indexOf( 'tags-' ) != -1)
		doTyp = 2;
	else if(boxName.indexOf( 'desc-' ) != -1)
		doTyp = 3;
	
	var dataIndex = $('#'+boxName).attr('data-index');;
			
	var data = new FormData();
		data.append('do', doTyp );
		data.append('aUpload','3');
		data.append('val', textValue);
		data.append('aText',picInd);
	console.log(textValue + " "+picInd);
	$.ajax({
        url: dragCart.url,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(data, textStatus, jqXHR){
            if(typeof data.data.error === 'undefined' || data.data.error == "0" ){
                // Success so call function to process the form
                console.log(data);
                
				showInfoUpdateSuccess( boxName );
				
				if(doTyp == 1)
					setInfoFieldTitle(data.data.lastInfo , dataIndex);
				else if(doTyp == 2)
					setInfoFieldTags(data.data.lastInfo , dataIndex);
				else if(doTyp == 3)
					setInfoFieldDescription(data.data.lastInfo , dataIndex);
					
            } else {
                // Handle errors here
                console.log(data.data);
                
				showInfoUpdateError ( boxName );
            }
        },
        error: function(jqXHR, textStatus, errorThrown){
            // Handle errors here
            console.log('ERRORS: ' + textStatus + " - " + errorThrown + " ");
            console.log(jqXHR);
            // STOP LOADING SPINNER
            showInfoUpdateError ( boxName );
        },
        complete: function(){

        }
    });
}

function showDragAndDropArea(){
	if($('#content-collection').length){
		uploadObject.isInObjectInsertation = 1;
		uploadObject.thisCollectionID = $('#theContentInfoBox').data('contentid');
		uploadObject.thisCollectionTitle = $('#content-collection h1').text();
		
		$('#newItemContainer').addClass('block');
		if(!$('#newItemContainer .newItems').length)
			$('#newItemContainer').append('<div class="newItems"></div>');
		else
			$('#newItemContainer .newItems').removeClass('hidden');
	} else
	$("#page-content-upload").addClass("visible");

};
function hideDragAndDrop(){
	if(!$("#page-content-upload").hasClass('forced')){
		$("#page-content-upload").removeClass("visible");
	};
	if(uploadObject.isInObjectInsertation){
		$('#newItemContainer .newItems').addClass('hidden');
		$('#newItemContainer').removeClass('block');
	}
};
function getBase64Image(img) {
	var canvas = document.createElement("canvas");
		canvas.width = img.width;
		canvas.height = img.height;
	var ctx = canvas.getContext("2d");
		ctx.drawImage(img, 0, 0);
	var dataURL = canvas.toDataURL("image/png");

	return dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
}
function doDragAndDropStuff(e){
	counterDrag = 0;
	$("#upload-area").removeClass("visible");
	$("#uploading-area").addClass("visible");
	
	var imageUrl = e.dataTransfer.getData('text/html');
    var imageL = imageUrl.length;
    console.log(e.dataTransfer.getData('text/plain'));
    var urlX = ""; 
    if($(imageUrl).children().length > 0 ){
        urlX = $(imageUrl).find('img').attr('src');
    } else {        
        urlX = $(imageUrl).attr('src');
    }

    if(e.dataTransfer.files.length == 0 && (/creezi.com/i.test(urlX)) != false){
    	if(counterDrag < 1){
	    	hideDragAndDrop();
		}
    	return;
    }

    if(e.dataTransfer.files.length > 0)
		countDragAndDropFiles(e.dataTransfer.files);
	else if(imageL > 0 && isValidPictureURL(urlX))
		addPictureURL(urlX);
	else if(isValidVideoURL(e.dataTransfer.getData('text/plain')))
		addVideoURL(e.dataTransfer.getData('text/plain'));
};
function isValidVideoURL( url ){
	console.log(youtubeParser(url, false));
	return youtubeParser(url, true);
}
function youtubeParser(url, testOnly){
    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\??v?=?))([^#\&\?]*).*/;
    var match = url.match(regExp);
    
    if(testOnly == true)
    	return (match&&match[7].length==11)? true : false;
    else
    	return (match&&match[7].length==11)? match[7] : false;
}
function addVideoURL( url ) {
	var matchS = youtubeParser(url, false);
	
	dragCart.nextIMGCounter++;
	dragCart.pictures++;
	console.log("trz to add video url");
	var iName = 'Video: '+matchS;
	url = 'https://i.ytimg.com/vi/'+matchS+'/default.jpg';
	
	$('#uploading-area #mediaBox').append(
		getUploadAreaHTML(iName)
    );

	$('#upload-pic-'+dragCart.nextIMGCounter).append('<img src="" id="image'+dragCart.nextIMGCounter+'"/>');

	var file = {
		type: 'url',
		url: url,
		isVideo: 1
	};
	updateMediaCounter();
    dragCart.files[dragCart.nextIMGCounter] = file;
	
	$('#image'+dragCart.nextIMGCounter).attr('src', url).load(function() {  
    	
	});
	
}
function highlightDragAndDropArea(){
	$(".file-dropable").addClass("active");
}
function deHighlightDragAndDropArea(){
	$(".file-dropable").removeClass("active");
}

function isValidPictureURL(url){
	var arr = [ "NA","jpeg", "jpg", "gif", "png" ];
	var ext = url.substring(url.lastIndexOf(".")+1);
	
	if($.inArray(ext,arr)){
		return true;
	}
	/*$.ajax ({
	  type: "HEAD",
	  url : url,
	  success: function(message,text,response){
	     if(response.getResponseHeader('Content-Type').indexOf("image")!=-1)
	     	return true;
	     else
	     	return false;
	  }, error: function(){
	  		return false;
	  }
	});*/
	return false;
}
var iOfPic = 0;

function addPictureURL(url){
	dragCart.nextIMGCounter++;
	dragCart.pictures++;
	console.log("trz to add picture url");
	var iName = url;
	
	$('#uploading-area #mediaBox').append(
    	getUploadAreaHTML(iName)
    );

	$('#upload-pic-'+dragCart.nextIMGCounter).append('<img src="" id="image'+dragCart.nextIMGCounter+'"/>');

	var file = {
		type: 'url',
		url: url
	};
	updateMediaCounter();
    dragCart.files[dragCart.nextIMGCounter] = file;
	
	$('#image'+dragCart.nextIMGCounter).attr('src', url).load(function() {  
    	
	});
}
function getUploadAreaHTML(iName){
	var str = '<div class="an-element" id="add-media-container-'+dragCart.nextIMGCounter+'"><div class="thePic" id="upload-pic-'+dragCart.nextIMGCounter+'"></div><div class="theControls"><div class="theName"><div class="line1">'+iName+'</div><div class="line2"></div></div>'+
    	'<div class="btn btn-remove" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-minus-square-o fa-2"></i>&nbsp; remove</div>'+
    	'<div class="btn btn-pending hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-lock fa-2"></i>&nbsp; in queue</div>'+
    	'<div class="bar bar-upload hidden"  data-uploadid="'+dragCart.nextIMGCounter+'">'+
    		'<div class="progress"></div>'+
    	'</div>'+
    	'<div class="btn btn-success hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-check fa-2"></i>&nbsp; successful</div>'+
    	'<div class="btn btn-error hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-bolt fa-2"></i>&nbsp; error</div>'+
    	'</div></div>'+
    	'<div class="picMetaBox hidden" id="picMetaBox-'+dragCart.nextIMGCounter+'" data-picindex="-1">' +
	    	'<div class="titleBox"><label><div class="labelSpacer">Title:</div><input data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" type="text" name="title-'+dragCart.nextIMGCounter+'" id="title-'+dragCart.nextIMGCounter+'"/></label>'+
	    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="title-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="title-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
	    	'<div class="typeIntoTextfield hidden" id="title-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="title-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
	    	
	    	'<div class="titleBox"><label><div class="labelSpacer">Tags:</div><input data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" type="text" name="tags-'+dragCart.nextIMGCounter+'" id="tags-'+dragCart.nextIMGCounter+'"/></label>'+
	    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="tags-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="tags-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
	    	'<div class="typeIntoTextfield hidden" id="tags-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="tags-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
	    	
	    	'<div class="titleBox"><label><div class="labelSpacer">Description:</div><textarea data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" rows="4" cols="50" name="desc-'+dragCart.nextIMGCounter+'" id="desc-'+dragCart.nextIMGCounter+'"></textarea></label>'+
	    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="desc-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="desc-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
	    	'<div class="typeIntoTextfield hidden" id="desc-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="desc-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
    	'</div>';
	return str;
	var t = '<div class="an-element" id="add-media-container-'+dragCart.nextIMGCounter+'"><div class="thePic" id="upload-pic-'+dragCart.nextIMGCounter+'"></div><div class="theControls"><div class="theName"><div class="line1">'+iName+'</div><div class="line2"></div></div>'+
    	'<div class="btn btn-remove" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-minus-square-o fa-2"></i>&nbsp; remove</div>'+
    	'<div class="btn btn-pending hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-lock fa-2"></i>&nbsp; in queue</div>'+
    	'<div class="bar bar-upload hidden"  data-uploadid="'+dragCart.nextIMGCounter+'">'+
    		'<div class="progress"></div>'+
    	'</div>'+
    	'<div class="btn btn-success hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-check fa-2"></i>&nbsp; successful</div>'+
    	'<div class="btn btn-error hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-bolt fa-2"></i>&nbsp; error</div>'+
    	'</div></div>'+
    	'<div class="picMetaBox hidden" id="picMetaBox-'+dragCart.nextIMGCounter+'" data-picindex="-1">' +
	    	'<div class="titleBox"><label><div class="labelSpacer">Title:</div><input data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" type="text" name="title-'+dragCart.nextIMGCounter+'" id="title-'+dragCart.nextIMGCounter+'"/></label>'+
	    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="title-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="title-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
	    	'<div class="typeIntoTextfield hidden" id="title-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="title-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
	    	
	    	'<div class="titleBox"><label><div class="labelSpacer">Tags:</div><input data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" type="text" name="tags-'+dragCart.nextIMGCounter+'" id="tags-'+dragCart.nextIMGCounter+'"/></label>'+
	    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="tags-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="tags-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
	    	'<div class="typeIntoTextfield hidden" id="tags-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="tags-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
	    	
	    	'<div class="titleBox"><label><div class="labelSpacer">Description:</div><textarea data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" rows="4" cols="50" name="desc-'+dragCart.nextIMGCounter+'" id="desc-'+dragCart.nextIMGCounter+'"></textarea></label>'+
	    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="desc-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="desc-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
	    	'<div class="typeIntoTextfield hidden" id="desc-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="desc-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
    	'</div>';
}
function ifIsRightElementChangeItemToUpload(){
	if($('#content-collection').length > 0)
		$('#contentAddButton .intro-item').html('<i class="fa fa-cloud-upload" aria-hidden="true"></i>');
}
function countDragAndDropFiles(droppedFiles){
	//console.log(droppedFiles);
	
	$("#uploading-area h4").text("");
	$("#uploading-area h4").append('<span class="uploadMediaCounter">0</span><span> files are ready for upload</span>');
	
	if(droppedFiles.length > 0)
		ifIsRightElementChangeItemToUpload();
	
	for (var i = 0, file; file = droppedFiles[i]; i++) {
		if (file.type.match(/image.*/)) {
			
			dragCart.pictures++;
			dragCart.nextIMGCounter++;
			var reader = new FileReader();
				reader.xcI = dragCart.nextIMGCounter;
			var iName = file.name;
			$('#content-collection .newItems').append(
                	'<div class="an-element" id="add-media-container-'+dragCart.nextIMGCounter+'"><div class="thePic" id="upload-pic-'+dragCart.nextIMGCounter+'"></div><div class="theControls"><div class="theName"><div class="line1">'+iName+'</div><div class="line2"></div></div>'+
                	'<div class="btn btn-remove" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-minus-square-o fa-2"></i>&nbsp; remove</div>'+
                	'<div class="btn btn-upload hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-pulse fa-spinner"></i>&nbsp; pending</div>'+
                	'<div class="btn btn-pending hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-lock fa-2"></i>&nbsp; in queue</div>'+
                	'<div class="bar bar-upload hidden"  data-uploadid="'+dragCart.nextIMGCounter+'">'+
                		'<div class="progress"></div>'+
                	'</div>'+
                	'<div class="btn btn-success hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-check fa-2"></i>&nbsp; successful</div>'+
                	'<div class="btn btn-error hidden" data-uploadid="'+dragCart.nextIMGCounter+'"><i class="fa fa-bolt fa-2"></i>&nbsp; error</div>'+
                	'</div></div>'+
			    	'<div class="picMetaBox hidden" id="picMetaBox-'+dragCart.nextIMGCounter+'" data-picindex="-1">' +
				    	'<div class="titleBox"><label><div class="labelSpacer">Title:</div><input data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" type="text" name="title-'+dragCart.nextIMGCounter+'" id="title-'+dragCart.nextIMGCounter+'"/></label>'+
				    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="title-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="title-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
				    	'<div class="typeIntoTextfield hidden" id="title-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="title-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
				    	
				    	'<div class="titleBox"><label><div class="labelSpacer">Tags:</div><input data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" type="text" name="tags-'+dragCart.nextIMGCounter+'" id="tags-'+dragCart.nextIMGCounter+'"/></label>'+
				    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="tags-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="tags-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
				    	'<div class="typeIntoTextfield hidden" id="tags-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="tags-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
				    	
				    	'<div class="titleBox"><label><div class="labelSpacer">Description:</div><textarea data-index="'+dragCart.nextIMGCounter+'" class="textBoxInd" rows="4" cols="50" name="desc-'+dragCart.nextIMGCounter+'" id="desc-'+dragCart.nextIMGCounter+'"></textarea></label>'+
				    	'<div class="uploadSaveIndicator"><div class="uploadTextError hidden" id="desc-'+dragCart.nextIMGCounter+'-error"><i class="fa fa-times"></i></div><div class="uploadTextSuccess hidden" id="desc-'+dragCart.nextIMGCounter+'-text"><i class="fa fa-check-square"></i></div>'+
				    	'<div class="typeIntoTextfield hidden" id="desc-'+dragCart.nextIMGCounter+'-type"><i class="fa fa-terminal"></i></div><div class="sk-cube-grid uploadToServer hidden" id="desc-'+dragCart.nextIMGCounter+'-upload"><i class="fa fa-pulse fa-spinner"></i></div></div></div>' +
			    	'</div>'
                );
			reader.onload = function(e2) {
				iOfPic++;
				var img = document.createElement('img');
                    img.src = e2.target.result;
                    console.log(" -- ");
                    console.log(this.xcI);

                $('#upload-pic-'+this.xcI).append(img);
			};
			reader.readAsDataURL(file);
			updateMediaCounter();
			dragCart.files[dragCart.nextIMGCounter] = file;
		}
	}
	
	//console.log(dragCart);
}

var dragCart = {
	url: '/upload?do',
    dataType: 'json',
    allowedTypes: 'image/*',
	pictures:  0,
	nextIMGCounter: 0,
	media: 0,
	uploadArray : {},
	files: {},
	success: {},
	fail: {},
	isCollection: false
};

function updateMediaCounter(){
	$(".uploadMediaCounter").text(dragCart.pictures);
}

function setUploadMediaButtonString(text){
	$('#controls-mediaBox').text(text);
}

function disableUploadButton(){
	$('#controls-mediaBox').addClass('disabled');
	//$('#controls-mediaBox').addClass('disabledA');
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function firstKey(obj){
	for (var key in obj) 
		if (obj.hasOwnProperty(key))
			return key;
}

function uploadMediaPrepare(){
	setUploadMediaButtonString('uploading...');
	$('#uploading-area h4').html('<span>Your upload...</span>');
	disableUploadButton();
	$('.theControls .btn-remove').addClass('hidden');
	
	$('.theControls .btn-pending').removeClass('hidden');
	
	return true;
}
function clearTheLoadingCart(){
	dragCart = {
		url: '/upload?do',
	    dataType: 'json',
	    allowedTypes: 'image/*',
		pictures:  0,
		nextIMGCounter: 0,
		media: 0,
		uploadArray : {},
		files: {},
		success: {},
		fail: {}
	};
	$("#page-content-upload").removeClass("visible");
	$("#upload-area").addClass("visible");
	$("#uploading-area").removeClass("visible");
	$('#mediaBox').html('');
	
	$('#controls-mediaBox').text('Upload Now');
	$('#controls-mediaBox').removeClass();
	
	counterDrag = 0;
}
function uploadMediaSave(){
	if( $('#controls-mediaBox').hasClass("published") ){
		clearTheLoadingCart();
		return;
	}
	$('#controls-mediaBox').addClass('published');
	$('#controls-mediaBox').html('publish <i class="fa fa-spinner fa-pulse fa-2"></i>');
	
	if($('#controls-mediaBox').hasClass('disabled')){
		sendToServerPublishPictures();
	}
}
var uploadObject = {
	isInObjectInsertation: 0,
	thisCollectionID: 0,
	thisCollectionTitle: ''
};
var uploader = {
	_dataObject: [],
	_dataIndex: 0,
	buildObject: 	function (){
		var index = uploader._dataIndex;
		var data = new FormData();
			data.append('file',dragCart.files[index]);
			data.append('do','1');
			data.append('title','');
			// console.log(uploadObject); return;
			if(uploadObject.isInObjectInsertation == 1)
				data.append( 'colID',uploadObject.thisCollectionID );
			
			if(dragCart.files[index].hasOwnProperty("url")){
				data.append('aUpload','2');
				data.append('url',dragCart.files[index].url);
			} else {
				data.append('aUpload','1');
			}
		uploader._dataObject[index] = data;
	},
	changeButtons: 	function(){
		var index = uploader._dataIndex;
		$('#add-media-container-'+index+' .btn-pending').addClass('hidden');
		$('#add-media-container-'+index+' .btn-remove').addClass('hidden');
		// $('#add-media-container-'+index+' .bar-upload').removeClass('hidden');
		$('#add-media-container-'+index+' .btn-upload').removeClass('hidden');
	},
	setIndex:		function(){
		uploader._dataIndex = firstKey(dragCart.uploadArray);
	},
	doAJAXCall:		function(){
		console.log(dragCart);		
		$('.newItems').on('ajax' , function(){
			var $this = $(this);
			
			if(Object.size(dragCart.uploadArray) <= 0){
				return false;
			}
			
			uploader.setIndex();
			uploader.changeButtons();
			uploader.buildObject();
		
		var index = uploader._dataIndex;
			$.ajax({
		        url: dragCart.url,
		        type: 'POST',
		        data: uploader._dataObject[index],
		        cache: false,
		        dataType: 'json',
		        processData: false, // Don't process the files
		        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
		        success: function(data, textStatus, jqXHR){
		            $('#add-media-container-'+index+' .bar-upload').addClass('hidden');
		            if(typeof data.data.error === 'undefined' || data.data.error == "0" ){
		                // Success so call function to process the form
		                dragCart.success[ index ] = data.data;
		                
		                /*if(!descriptionBoxSet){
		                	descriptionBoxSet = true;
		                	$('#controls-kindOfUpload').append('<div class="aHeadliner hidden"><p>Title of Collection</p><input data-index="0" class="textBoxInd0" type="text" name="title-collection" id="articleBoxTitle"><p>Decription Text of Collection</p><textarea data-index="0" class="textBoxInd0" rows="4" cols="50" name="desc-article" id="articleBoxDescription"></textarea></div>');
		                }*/
		                success++;
	
		                if(success > 1){
		                	// $('#controls-kindOfUpload').removeClass('hidden');
		                }
		                $('#add-media-container-'+index+' .btn-success').removeClass('hidden');
		                $('#add-media-container-'+index+' .btn-remove').addClass('hidden');
		                $('#add-media-container-'+index+' .btn-upload').addClass('hidden'); 
		                //$('#picMetaBox-'+index).removeClass('hidden');
		                // $('#picMetaBox-'+index).attr('data-picindex' , data.data.id);
		                $('#controls-mediaBox').addClass('uploaded');
		                
		                // build element in collection
		                var newEl = $('#grid-main .grid-main-item:nth-child(2)').clone();
		                	newEl.attr('id','ins-'+index);
		                	newEl.find('a').attr('href',data.data.url);
		                	//newEl.addClass('hidden');
		                	//newEl.addClass('op0');
		                	newEl.find('img').attr( 'src',$('#add-media-container-'+index+' img').attr('src') );
		                	
		                $('#newItemContainer').after( newEl );
		                console.log($('#ins-'+index));
		                $('#ins-'+index).fadeIn(1000);
		            } else {
		                // Handle errors here
		                console.log(data.data);
		                dragCart.fail[ index ] = data.data;
		                
		                $('#add-media-container-'+index+' .btn-error').removeClass('hidden');
		            }
		        },
		        error: function(jqXHR, textStatus, errorThrown){
		            // Handle errors here
		            console.log(jqXHR);
		            console.log('ERRORS: ' + textStatus + " "+errorThrown);
		            var z = {
		            	error: 100, 
		            	errorMsg: textStatus
		            };
		            dragCart.fail[ index ] = z;
		            $('#add-media-container-'+index+' .bar-upload').addClass('hidden');
		            $('#add-media-container-'+index+' .btn-error').removeClass('hidden');
		            // STOP LOADING SPINNER
		        },
		        complete: function(){
		        	delete dragCart.uploadArray[index];
		        	$this.trigger('ajax');
		        }
		    });
		}).trigger('ajax');
	}
};
function addMediaToCollection(){
	// set info about collection
	// upload Media files
	dragCart.uploadArray = dragCart.files;
	uploader.doAJAXCall();
}

var descriptionBoxSet = false;
var success = 0;
function uploadMedia(){
	if($('#controls-mediaBox').hasClass("uploaded")){
		uploadMediaSave();
		return;
	}
	if($('#controls-mediaBox').hasClass("disabled") || !uploadMediaPrepare())
		return;
	
	dragCart.uploadArray = dragCart.files;
	
	$('#uploading-area').on('ajax' , function(){
		if(Object.size(dragCart.uploadArray) <= 0){
			setUploadMediaButtonString('publish');
			return false;
		}
		
		var $this = $(this);
		
		var index = firstKey(dragCart.uploadArray);
		
		$('#add-media-container-'+index+' .btn-pending').addClass('hidden');
		$('#add-media-container-'+index+' .bar-upload').removeClass('hidden');
		
		var data = new FormData();
			data.append('file',dragCart.files[index]);
			data.append('do','1');
			data.append('title','enter your title');
			
			if(dragCart.files[index].hasOwnProperty("url")){
				data.append('aUpload','2');
				data.append('url',dragCart.files[index].url);
			} else {
				data.append('aUpload','1');
			}
		$.ajax({
	        url: dragCart.url,
	        type: 'POST',
	        data: data,
	        cache: false,
	        dataType: 'json',
	        processData: false, // Don't process the files
	        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
	        success: function(data, textStatus, jqXHR){
	            $('#add-media-container-'+index+' .bar-upload').addClass('hidden');
	            if(typeof data.data.error === 'undefined' || data.data.error == "0" ){
	                // Success so call function to process the form
	                //console.log(data);
	                //console.log(data.data.id);
	                dragCart.success[ index ] = data.data;
	                
	                if(!descriptionBoxSet){
	                	descriptionBoxSet = true;
	                	$('#controls-kindOfUpload').append('<div class="aHeadliner hidden"><p>Title of Collection</p><input data-index="0" class="textBoxInd0" type="text" name="title-collection" id="articleBoxTitle"><p>Decription Text of Collection</p><textarea data-index="0" class="textBoxInd0" rows="4" cols="50" name="desc-article" id="articleBoxDescription"></textarea></div>');
	                }
	                success++;
	                //console.log(dragCart.success);
	                //console.log(dragCart.success.length);
	                if(success > 1){
	                	$('#controls-kindOfUpload').removeClass('hidden');
	                }
	                $('#add-media-container-'+index+' .btn-success').removeClass('hidden');
	                $('#picMetaBox-'+index).removeClass('hidden');
	                $('#picMetaBox-'+index).attr('data-picindex' , data.data.id);
	                $('#controls-mediaBox').addClass('uploaded');
	            } else {
	                // Handle errors here
	                console.log(data.data);
	                dragCart.fail[ index ] = data.data;
	                
	                $('#add-media-container-'+index+' .btn-error').removeClass('hidden');
	            }
	        },
	        error: function(jqXHR, textStatus, errorThrown){
	            // Handle errors here
	            console.log(jqXHR);
	            console.log('ERRORS: ' + textStatus + " "+errorThrown);
	            var z = {
	            	error: 100, 
	            	errorMsg: textStatus
	            };
	            dragCart.fail[ index ] = z;
	            $('#add-media-container-'+index+' .bar-upload').addClass('hidden');
	            $('#add-media-container-'+index+' .btn-error').removeClass('hidden');
	            // STOP LOADING SPINNER
	        },
	        complete: function(){
	        	delete dragCart.uploadArray[index];
	        	$this.trigger('ajax');
	        }
	    });
	}).trigger('ajax');
	
	return;
	
	// set up Information
	if(uploadMediaPrepare()){	
		for (var index in dragCart.files){
		    if (dragCart.files.hasOwnProperty( index )) {
				
				console.log( index );
				var data = new FormData();
				data.append('file',dragCart.files[index]);
				data.append('do','1');
				data.append('title','enter your title');
				data.append('aUpload','1');
				
				$.ajax({
			        url: dragCart.url,
			        type: 'POST',
			        data: data,
			        cache: false,
			        dataType: 'json',
			        processData: false, // Don't process the files
			        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
			        success: function(data, textStatus, jqXHR){
			            $('#add-media-container-'+index+' .bar-upload').addClass('hidden');
			            if(typeof data.data.error === 'undefined' || data.data.error == "0" ){
			                // Success so call function to process the form
			                console.log(data);
			                dragCart.success[ index ] = data.data;
			                
			                $('#add-media-container-'+index+' .btn-success').removeClass('hidden');
			            } else {
			                // Handle errors here
			                console.log(data.data);
			                dragCart.fail[ index ] = data.data;
			                
			                $('#add-media-container-'+index+' .btn-error').removeClass('hidden');
			            }
			        },
			        error: function(jqXHR, textStatus, errorThrown){
			            // Handle errors here
			            console.log('ERRORS: ' + textStatus+ " "+errorThrown);
			            var z = {
			            	error: 100, 
			            	errorMsg: textStatus
			            };
			            dragCart.fail[ index ] = z;
			            $('#add-media-container-'+index+' .bar-upload').addClass('hidden');
			            $('#add-media-container-'+index+' .btn-error').removeClass('hidden');
			            // STOP LOADING SPINNER
			        }
			    });
			}
		}
		
		
	}
}
