<?php
class cConnection{
	private $ip = null;
	private $connection = null;
	private $noTransfer = false;
	
	private $gets = array();
	
	private $return = null;
	private $lastError = null;
	
	public function __construct(){
		$this->connection = curl_init(); 
		
		return $this;
	}
	public function setIP($ip){
		$this->ip = $ip;
		
		return $this;
	}
	public function setGETValue($var,$value){
		$this->gets[$var] = $value;
		
		return $this;
	}
	public function setNoTransfer($bool){
		$this->noTransfer = $bool;
		
		return $this;
	}
	
	public function establish(){	
		if($this->connection === NULL || $this->ip === NULL)
			return this;
		
		if(!empty($this->gets)){
			$this->ip .= '?';
				
			foreach($this->gets as $key => $val){
				$this->ip .= $key.'='.$val."&";
			}
			
			$this->ip = substr($this->ip, 0, -1);
		}
		
		curl_setopt($this->connection, CURLOPT_URL, $this->ip);
		
		if($this->noTransfer)
			curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, false);
		else
			curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, false);
		
		$this->return = curl_exec($this->connection);
		
		if($this->return === FALSE)
			$this->lastError = curl_error($this->connection);
		else
			$this->lastError = null;
		
		curl_close($this->connection);
		
		return $this;
	}
	
	public function getReturnByJSON(){
		return @json_decode($this->return);
	}
	
	public function getReturnPlain(){
		return $this->return;
	}
	public function getLastError(){
		return $this->lastError;
	}
}
?>
