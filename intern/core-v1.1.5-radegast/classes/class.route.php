<?php
class route{
	public static function toHome(){
		header("Location: http://".$_SERVER['HTTP_HOST']);
		exit();
	}
}
?>