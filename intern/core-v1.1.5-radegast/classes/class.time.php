<?php

class dateAndTime{
	private $source;
	private $isDateObj = FALSE;
	private $dateObj;
	private $dateNow;
	
	private $dateNowUnix;
	private $dateObjUnix;
	
	private $min5 = 300; // 300s == 5min
	private $min10 = 600;
	private $min20 = 1200;
	private $min30 = 1800;
	private $h1 = 3600;
	private $h2 = 7200;
	private $h4 = 14400;
	private $h12 = 43200;
	private $d1 = 86400;
	private $d2 = 172800;
	private $d3 = 259200;
	private $d4 = 345600; 
	private $d5 = 432000;
	private $w1 = 604800;
	private $w2 = 1205600; 
	private $w3 = 1814400;
	private $m1 = 2419200;
	private $m2 = 4838400;
	private $m3 = 7257600;
	private $m6 = 14515200;
	private $y1 = 31536000;
	private $y2 = 63072000;
	private $y3 = 94608000;
	
	function __construct($s){
		$this->source = $s;
		$this->dateObj = new DateTime($s);
		$this->dateNow = new DateTime("now");
		
		$this->dateNowUnix = $this->dateNow->getTimestamp();
		$this->dateObjUnix = $this->dateObj->getTimestamp();
	}
	public function getTimeStamp(){
		return $this->dateObjUnix;
	}
	public function getTimeDiffToNowAsString(){
		$interval = $this->dateNowUnix - $this->dateObjUnix;
		
		if($interval > $this->y1){
			$t = (int)($interval/$this->y1);
			if($t == 1)	
				$string = $t.' y';
			else
				$string = $t.' ys';
		} else if($interval > $this->m1){
			$t = (int)($interval/$this->m1);	
			if($t == 1)	
				$string = $t.' m';
			else
				$string = $t.' ms';
		} else if($interval > $this->w1){
			$t = (int)($interval/$this->w1);	
			if($t == 1)	
				$string = $t.' w';
			else
				$string = $t.' ws';
		} else if($interval > $this->d1){
			$t = (int)($interval/$this->d1);	
			if($t == 1)	
				$string = $t.' d';
			else
				$string = $t.' ds';
		} else if($interval > $this->h1){
			$t = (int)($interval/$this->h1);	
			if($t == 1)	
				$string = $t.' h';
			else
				$string = $t.' hrs';
		} else if($interval > $this->min5){
			$t = 5*(int)($interval/$this->min5);	

			$string = $t.' mins';
		} else {
			$string = ' 1 min';
		}
		$string .= '';
		
		return $string;
	}
}
