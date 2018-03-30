<?php
namespace Firewall;

class DistributorURLs {
    private $urls    = [];
    private $useConv = FALSE;
    public function setURLs(array $strings):DistributorURLs{
        $this->urls = $strings;

        return $this;
    }
    public function getURLs():array{
        return $this->urls;
    }
    public function getURLRandom():string{
        if(count($this->urls) < 1)
            return $_SERVER["HTTP_HOST"];

        return $this->urls[mt_rand(0,count($this->urls)-1)] ?? $_SERVER["HTTP_HOST"];
    }
    public function setConvRohit($bool){ $this->useConv = $bool; }

    public function sendConvRohitX($cidOfZeropark){
        if(!$this->useConv || empty($cidOfZeropark)){
            /*if(stripos($_SERVER['REQUEST_URI'],'favicon') === FALSE){
                $myFile = @fopen("/usr/local/lsws/SITES/tubesearch.co/logs-tier1/".time()."-".$_SERVER['REMOTE_ADDR'].".txt", "w");
                $txt = $_SERVER[ "REQUEST_URI" ]."\n".print_r($_SESSION,true);
                if($myFile !== FALSE) {
                    @fwrite($myFile, $txt);
                    @fclose($myFile);
                } else {

                }
            }*/
            return;
        }
        @fclose(fopen('http://postback.zeroredirect1.com/zppostback/5c065711-cfe4-11e4-915c-0ecf5f154b02?cid='.$cidOfZeropark,'r'));
    }
}