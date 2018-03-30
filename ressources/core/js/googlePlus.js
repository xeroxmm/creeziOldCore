function onSuccess(googleUser) {
  console.log('Logged in as: ' + googleUser.getBasicProfile().getName());
  
  var id_token = googleUser.getAuthResponse().id_token;
	console.log(id_token);	
	$.post("/"+lgURL,{
		loginType: "ggl",
		key: id_token
	}, function(data, status){
		console.log(data);
		//core.login.success(data);
		$('#nav-settings').html(
			'Thanks for logging in, ' + data.name + '! <img id="sign-in-pic" src="http://graph.facebook.com/'+data.picture+'/picture" height="30px"  width="30px" style="float: right"/>'
   		);
	});
}
function onFailure(error) {
	console.log(error);
}
function onSignIn(googleUser) {
	
}
function renderButton() {
	gapi.signin2.render('my-signin2', {
	    'scope': 'https://www.googleapis.com/auth/plus.login',
	    'width': 200,
	    'height': 50,
	    'longtitle': true,
	    'theme': 'dark',
	    'onsuccess': onSuccess,
	    'onfailure': onFailure
	});
}