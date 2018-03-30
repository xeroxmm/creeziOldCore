$( document ).ready(function() {
    if($(".fullscreenTrigger").length){
	    $('.fullscreenTrigger').on('click',function(e){
	    	e.preventDefault();
	    	if($(".fullscreenApp").length && $("#"+$(this).attr("attr-app")+"App").length){
	    		$(".fullscreenApp").css('display','none');
	    		$("#"+$(this).attr("attr-app")+"App").css('display','flex');
	    	}
	    });
	    if($(".fullscreenApp").length){
		    $('.fullscreenApp .close').on('click', function(e){
		    	e.preventDefault();
		    	$(".fullscreenApp").css('display','none');
		    });
	    }
    }
});