var core = {
	login : {
		success : function($data){
			if($data.hasOwnProperty('status') && $data.hasOwnProperty('type') && $data.hasOwnProperty('data') && $data.data.hasOwnProperty('status')){
				core.hide.class('loginPanel');
				core.show.class('loggedInPanel');
			}
		}
	},
	hide : {
		class : function(string){
			$('.'+string).css('display','none');
		}
	}
};
