<?php

/**
* Retourne la première ou la seconde partie du Tag de l'objet JLanguage courant
* 
* @param    boolean True pour la première partie, false pour la seconde
* @return   string  Le tag court
*/
function getLangTag($first = true) {
    $lang = JFactory::getLanguage();
    $tag = split('-', $lang->getTag());
    if ($first) {
        return $tag[0];
    }
    return $tag[1];
}


/**
* Retourne une date formattée selon la langue (anglais/français)
* 
* @param    string  $mysqlDate au format aaaa-mm-jj ou aaaa-mm-jj hh:mm:ss
* @param    string  Le tag de la langue (en ou fr), si null prend le tag de l'objet JLanguage courant
* @param    boolean True pour rajouter l'heure sinon false
* @return   string  La date formattée
*/
function readableDate($mysqlDate, $lang=null, $time=false) {
    if (!empty($mysqlDate) and $mysqlDate != '0000-00-00' and $mysqlDate != '0000-00-00 00:00:00') {
        $months = array(
            'en' => array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
            'fr' => array(1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre')
        );
        
        $hourPatterns = array(
            'en' => 'h:i a',
            'fr' => 'H\hi'
        );
        
        if ($lang == null) {
            $lang = getLangTag();
        }
        
        list($year, $month, $day) = split('-', $mysqlDate);
        $day    = intval($day);
        $month  = $months[$lang][intval($month)];
        
        if($time == true AND strpos($mysqlDate, ' ')){
            $hour = date($hourPatterns[$lang], strtotime($mysqlDate));
        }
        
        
        $return = '';
        if ($lang == 'fr') {
            $return = $day.' '.$month.' '.$year;
            
            if (isset($hour)) {
                $return .= ' à '.$hour;
            }
        } elseif ($lang == 'en') {
            switch($day) {
                case 1:
                    $day .= 'st';
                    break;
                case 2:
                    $day .= 'nd';
                    break;
                case 3:
                    $day .= 'rd';
                    break;
                default :
                    $day .= 'th';
            }
            $return = $month.' '.$day.', '.$year;
            
            if (isset($hour)) {
                $return .= ' at '.$hour;
            }
        }
        
        return $return;
    }
}


/**
* Test si le navigateur de l'utilisateur est celui renseigné
* 
* @param    array   Informations sur le navigateur recherché, ['name'] 'ie' et ['versions'] peut être une chaîne de caractères ou un tableau
* @return   boolean True si c'est le navigateur, false sinon
* @todo     Prendre en compte d'autres navigateurs
*/
function testBrowser($testedBrowser = array()) {
    switch($testedBrowser['name']) {
        case 'ie':
            $name = 'MSIE';
    }
    
    $test = false;
    if (isset($name)) {
        // Si une ou plusieurs versions sont renseignées
        if (isset($testedBrowser['versions'])) {
            if (is_array($testedBrowser['versions'])) {
                foreach ($testedBrowser['versions'] as $version) {
                    if (strpos($_SERVER["HTTP_USER_AGENT"], $name.' '.$version) !== false) {
                        $test = true;
                        break;
                    }
                }
            } elseif (strpos($_SERVER["HTTP_USER_AGENT"], $name.' '.$testedBrowser['versions']) !== false) {
                $test = true;
            }
        }
        // Sinon on ne teste que le navigateur
        elseif (strpos($_SERVER["HTTP_USER_AGENT"], $name) !== false) {
            $test = true;
        }
    }
    
    return $test;
}

/**
 * Gets IP address 
 *
 */

function getRealIpAddr() {
        
    $ip = null;
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip=$_SERVER['HTTP_CLIENT_IP']; // share internet
    } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR']; // pass from proxy
    } else {
        $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Display a variable and stops the script
 * @param mixed variable to display
 * @param boolean on true will continue the script. Dedfaults to stop
 * 
 */

function debug($variable, $stop = true) {
    if (is_object($variable) && get_class($variable) == 'JFDatabase'){
        echo '<pre>'.$variable->getErrorMsg().'</pre>';
        echo '<pre>'.print_r($variable,true).'</pre>';
    }
    elseif (is_array($variable) || is_object($variable)){
		echo '<pre>'.print_r($variable,true).'</pre>';
    } elseif(is_bool($variable)){
        var_dump($variable);
    } else {
		echo '<br/>'.$variable.'<br/>';
	}
		
	if ($stop){ die();}	else { return true; }
}

/**
 * Warns admin and sends an email
 * @param string subject
 * @param string message to admin
 * @param serialized array of emails to be warned
 */
function warnAdmin($subject, $message, $destEmail = PM_ADMIN){

	if(is_array($message) || is_object($message)){
		$newMessage = '';
		foreach ($message as $key => $value){
			$newMessage .= $key .' : '.$value.'<br/>';
		}
		$message = $newMessage;
	}
    
	if(isset($_SERVER['REQUEST_URI'])){
		$message .= "<br/>Request_uri: ".$_SERVER['REQUEST_URI'];
	}
    
    if(isset($_SERVER['HTTP_REFERER'])){
        $message .= "<br/>Http_referer: ".$_SERVER['HTTP_REFERER'];
    }
    
     if(isset($_POST) && !empty($_POST)){
         $message .= "<br/>Post:";
         foreach($_POST as $k => $v){
             $message .= "<br/>".$k." => ".$v;
         }
        
    }

	require_once(JPATH_SITE.DS.'components'.DS.'com_hpjmembres'.DS.'helpers'.DS.'sessionBusinessController.php');
	$sBC = HPJMembresSessionBusinessController::getInstance();
    $hpjuser = $sBC->getCurrentUser();   
	
    foreach (unserialize($destEmail) as $email){
       
        if(is_object($hpjuser)){
          
            $message .=  '<br/>userId = '.$hpjuser->get('userId').'<br/>'
                      .  'username = '.$hpjuser->get('username').'<br/>';        
        }
        $message .=  '<br/>ip = '.getRealIpAddr();

        JUtility::sendMail(NOREPLY_ADRESS, 'pureMix '.$_SERVER['HTTP_HOST'], $email, $subject, $message, 1);
    }
    return true;
}

/**
 * Adds the index file to a given path
 * @param string gets the path to a file OR a directory
 */
function addIndexFile($filePath){
        
    $ext = pathinfo($filePath,PATHINFO_EXTENSION);
    
    if(!$ext){
        $path = $filePath;
    }else {
        $path = pathinfo($filePath,PATHINFO_DIRNAME);
    }
    $indexFile      = $path . DS . 'index.html';
    if(!file_exists($indexFile)){
         @file_put_contents($indexFile, '<html><body bgcolor="#FFFFFF"></body></html>');  
    }
    return true; 
}

/**
 * Register to hpjcc
 * @param string email
 */
function registerHPJCC($email){
    
      if (defined('PM_PROD')){
            $postdata = array();     
            $postdata['email'] = $email;   
            $postdata['companyid'] = 'CPYGOTGV47kbJb847MKrNop';
            $postdata['submissionType'] = 'redirect';
            $postdata['lang'] = 'en';
            $postdata['returnurl'] = 'http://www.puremix.net/';

            $req = new HttpRequest();
            $req->setMethod(HTTP_METH_POST);
            $req->setPostFields($postdata);
            $req->setUrl('http://s1.hpjcc.com/en/remote/subscribe');

            try {
                $req->send(); 
                return true;
            } catch (HttpException $ex) {
               
            }
      }
    
 }

/**
 * Determine Browser
 * @return Browser
 */
function idBrowser() {
    $browser=$_SERVER['HTTP_USER_AGENT']; 

    if(preg_match('/Opera(\/| )([0-9]\.[0-9]{1,2})/', $browser)) {
        return 'OPERA';
    } else if(preg_match('/MSIE ([0-9]\.[0-9]{1,2})/', $browser)) {
        return 'IE';
    } else if(preg_match('/OmniWeb\/([0-9]\.[0-9]{1,2})/', $browser)) {
        return 'OMNIWEB';
    } else if(preg_match('/(Konqueror\/)(.*)/', $browser)) {
        return 'KONQUEROR';
    } else if(preg_match('/Mozilla\/([0-9]\.[0-9]{1,2})/', $browser)) {
        return 'MOZILLA';
        } else if(preg_match('/Chrome\/([0-9]\.[0-9]{1,2})/', $browser)) {
        return 'CHROME';
    } else {
        return 'OTHER';
    }
}

/**
 * get Browser Language
 * @return string
 */
 function langBrowser(){
         
     $browserLang = null;
     if (key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER ) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){             
        $browserLang = strtoupper(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2));
     }
     return $browserLang;
 }
 
 /**
 * get Browser Language
 * @return string
 */
 function regionBrowser(){
         
     $regionLang = null;
     if (key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER ) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
                 
             $matches = array();
             preg_match_all('#[a-zA-Z]{2}-([a-zA-Z]{2})#',$_SERVER['HTTP_ACCEPT_LANGUAGE'],$matches);
                     
        $regionLang = isset($matches[1][0]) ? $matches[1][0] : null;
     }
     
     return $regionLang;
 }

/**
 * Binds one Array to another
 * @param Array Base array
 * @param Array Optional array
 * @return Array Updated array
 * 
 */
function bindArrays(&$arrayA, $arrayB){
       
    foreach($arrayB as $keyB => $valueB){
        if (key_exists($keyB, $arrayA)){
            $arrayA[$keyB] = $valueB;
        }
    }    
}

/**
 * remove some characters
 * @param String the string that we want to clean
 */
function cleanString2Url($string){
         
     $array1 = array('é','è','à','ç');
     $array2 = array('e','e','a','c');
     return str_replace($array1,$array2,$string);
}
 
 /**
  * generate a question mark
  * @param message
  */
function getQuestionMark($message){?>
    <p class="pmmodal backbubble" pmmodalclass="questionMark spriteBckG" pmmodalbutton="none" style="width:300px;"><?=$message?></p><?php
}

/**
 * sends an email when a php errors is raised
 */

function sendPHPErrorsEmail($errno, $errstr, $errfile, $errline) {
		
	
    if ( !defined( 'E_DEPRECATED' ) )       define( 'E_DEPRECATED', 8192 );
    if ( !defined( 'E_USER_DEPRECATED' ) )  define( 'E_USER_DEPRECATED', 16384 );
        
    $arrayErrors = array(
        /*1*/    E_ERROR => 'E_ERROR',
        /*2*/    E_WARNING => 'E_WARNING',
        /*4*/    E_PARSE => 'E_PARSE',
        /*8*/    E_NOTICE => 'E_NOTICE',
        /*16*/   E_CORE_ERROR => 'E_CORE_ERROR',
        /*32*/   E_CORE_WARNING => 'E_CORE_WARNING',
        /*64*/   E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        /*128*/  E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        /*1024*/ E_USER_NOTICE => 'E_USER_NOTICE',
        /*2048*/ E_STRICT => 'E_STRICT',
        /*4096*/ E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        /*8192*/ E_DEPRECATED => 'E_DEPRECATED',
        /*16384*/E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    );
        
    $type = array_key_exists($errno, $arrayErrors) ? $arrayErrors[$errno] : $errno;
    
    $message = "<p>Error Reporting Level: ".error_reporting()."<br/><b>Error $type</b> in $errfile  <b>line $errline </b>: $errstr </p>";
    
    error_reporting(0);
	
    warnAdmin('PHP Error', $message, serialize(array('phpErrors@puremix.net')));
    
    if( !defined('PM_PROD') && defined('PM_LOCAL')) {
        debug($message);
    }

    error_reporting(E_ALL);
	
	return;

}

function shutdownHandler(){
          
    $lastError = error_get_last();
   
    $arrayErrors = array(
        /*1*/    E_ERROR => 'E_ERROR',
        /*2*/    E_WARNING => 'E_WARNING',
        /*4*/    E_PARSE => 'E_PARSE',
        /*8*/    E_NOTICE => 'E_NOTICE',
        /*16*/   E_CORE_ERROR => 'E_CORE_ERROR',
        /*32*/   E_CORE_WARNING => 'E_CORE_WARNING',
        /*64*/   E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        /*128*/  E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        /*1024*/ E_USER_NOTICE => 'E_USER_NOTICE',
        /*2048*/ //E_STRICT => 'E_STRICT',
        /*4096*/ E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',           
    );

    if($lastError &&  array_key_exists($lastError['type'], $arrayErrors)){
        
        $type = array_key_exists($lastError['type'], $arrayErrors) ? $arrayErrors[$lastError['type']] : $lastError['type'];
        
        $message = "<p><b>PHP Shutdown</b><br/>Level: " . $lastError['type'] . "<br/>Type: ".$type."<br/>Msg: " . $lastError['message'] . "<br/>File: " . $lastError['file'] . "<br/>Line: " . $lastError['line'] ."</p>";
   
        error_reporting(0);
    
        warnAdmin('php Error', $message, serialize(array('phpErrors@puremix.net')));
        
        if( !defined('PM_PROD') && defined('PM_LOCAL')) {
            echo($message);
        }
        
        debug('<p>Whoooopsy! There was an error in our script.</p><p>Would you please be kind enough to let us know about it at contact@puremix.net ?</p><p>Thanks!<br/>The pureMix team.</p>');
        
        error_reporting(E_ALL); 
    }   
    return;
}
