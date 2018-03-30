<?php
	security::loadUserLevel(userLevelEnum::NONE);
	
	html::head();
	B::ID('wrap')
		->inner()
		->_DIV('content-main','content-main')
			->in()
				->_DIV('content-page-in','content-page-in')
				->in()
				->_DIV( 'landing-1' , ['landing-1','inline-block'] );
				
	B::ID('landing-1')->setContent(				
	'<div class="content-header-join"></p><h1>Hi, I\'m creezi</h1><h2>Social collaboration for everyone</h2><span class="join-subline">Want to be the first?</br> Submit your email and we keep you posted about updates, news and early access.</span>
	<div id="sib_embed_signup" style="padding: 10px;">
    <div class="wrapper" style="position:relative;margin-left: auto;margin-right: auto;">
        <input type="hidden" id="sib_embed_signup_lang" value="de">
        <input type="hidden" id="sib_embed_invalid_email_message" value="Sorry this email address seems invalid, please try again champ.">
	    <input type="hidden" name="primary_type" id="primary_type" value="email">
        <div id="sib_loading_gif_area" style="position: absolute;z-index: 9999;display: none;">
            <img src="http://img.mailinblue.com/new_images/loader_sblue.gif" style="display: block;margin-left: auto;margin-right: auto;position: relative;top: 40%;">
        </div>
        <form class="description" id="theform" name="theform" action="https://my.sendinblue.com/users/subscribeembed/js_id/2j7r0/id/1" onsubmit="return false;">
            <input type="hidden" name="js_id" id="js_id" value="2j7r0"><input type="hidden" name="listid" id="listid" value="2"><input type="hidden" name="from_url" id="from_url" value="yes"><input type="hidden" name="hdn_email_txt" id="hdn_email_txt" value="">
            <div class="container rounded">
                
               <input type="hidden" name="req_hid" id="req_hid" value="" pmbx_context="AEAC6969-D3C8-489B-8E67-325453A68F97" style="font-size: 13px;">
                    <div class="view-messages" style=" margin:5px 0;"> </div>
                        <!-- an email as primary -->
            <div class="primary-group email-group forms-builder-group" style="">
                            <div class="row mandatory-email" style="padding: 10px 20px; position: relative; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(52, 52, 52); font-size: 17px;">
                                <div class="lbl-tinyltr"  style="clear: both; float: none; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif;">Please enter your email address, to join the list.  <red style="color:#ff0000;">*</red></div>
                                <input type="text" name="email" id="email" value="" pmbx_context="FE82E88E-BD80-4105-8862-01DD8DEE69E9" style="padding: 10px 4px; width: 35%; min-width: auto;">
                                <div style="clear:both;"></div>
                                <div class="hidden-btns">
                                    <a class="btn move" href="#"><i class="icon-move"></i></a><br>
                        <!--<a class="btn btn-danger delete"  href="#"><i class="icon-white icon-trash"></i></a>-->
                                </div>
                            </div>
                         </div>
                        <!-- end of primary -->
                         <div class="byline" style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(52, 52, 52); font-weight: bold; font-size: 17px; text-align: center;">
                         <button class="button editable " type="submit" data-editfield="subscribe"  style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); display: inline-block; white-space: nowrap; min-height: 40px; margin: 0px 5px 0px 0px; padding: 0px 22px; text-decoration: none; text-transform: uppercase; text-align: center; font-weight: bold; font-style: normal; font-size: 14px; cursor: pointer; border: 0px; border-radius: 4px; vertical-align: top; height: auto; line-height: 150%; background: rgb(5, 5, 5);">Submit </button></div>
                         <div style="clear:both;"></div>
                        </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="https://my.sendinblue.com/public/theme/version3/js/subscribe-validate.js?v=1465900172"></script>
<script type="text/javascript">
    jQuery.noConflict(true);
</script>
<!-- End : SendinBlue Signup Form HTML Code -->
	</div>
	');	

	html::send200();
	
	exit;
?>