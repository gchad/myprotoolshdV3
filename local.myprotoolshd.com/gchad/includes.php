<?php

global $countryMatrix;

$countryMatrix = array(
                        
        //UK
        1 => array(252),
        
        //Germany
        3 => array(87),
        
        //Spain
        4 => array(221),
        
        //Italy
        5 => array(113),
        
        //France
        6 => array(79),
        
        //Nordic
        7 => array(62,78,105,175,227),
        
        //Africa
        8 => array(3,6,23,28,36,37,40,43,45,46,52,53,54,61,63,69,71,72,74,84,85,88,97,98,120,128,129,130,136,137,140,144,145,156,157,159,168,169,195,206,208,211,212,217,218,223,226,233,236,240,248,264,265),
        
        //Middle East
        11 => array(17,59,69,108,109,112,118,123,127,176,180,192,207,229,241,251,263),
        
        //Europe
        10 => array(2,5,11,14,15,20,21,27,35,57,59,60,62,73,78,79,86,87,90,104,105,110,113,119,122,126,131,132,133,135,141,151,152,154,162,175,189,190,193,194,205,209,214,215,221,227,228,241,249,252,257,),
        
        //Worldwide
        13 => array(62,78,105,175,227,
                    3,6,23,28,36,37,40,43,45,46,52,53,54,61,63,69,71,72,74,84,85,88,97,98,120,128,129,130,136,137,140,144,145,156,157,159,168,169,195,206,208,211,212,217,218,223,226,233,236,240,248,264,265,
                    17,59,69,108,109,112,118,123,127,176,180,192,207,229,241,251,263,
                    2,5,11,14,15,20,21,27,35,57,59,60,62,73,78,79,86,87,90,104,105,110,113,119,122,126,131,132,133,135,141,151,152,154,162,175,189,190,193,194,205,209,214,215,221,227,228,241,249,252,257
        
            )
    );
    
    
function truncate($string, $length = 100, $append = "&hellip;") {
      
  $string = trim($string);
  

  if(strlen($string) > $length) {
      
    //$lastSpace = strrpos($string,' ');
     
   // $truncate = $lastSpace === false ? $length : $lastSpace;
    
    $truncate = $length;
    return substr($string, 0, $truncate).$append;
   
  }

  return $string;
}

function orderProducts($values){
    
    $newValues = array();
    
    /** first number is the product extra fiel ID. 
     * second number is it's order. 
     * If order equals null, then it doesn't show.
     * 
     */
    $productsMatrix = array(
    
        1 => 0, //Hd native
        2 => 1, //hdx
        3 => 2, //s3
        4 => 3  //s6
        
    );
    
    foreach($values as $p){
        
        if(key_exists($p->value, $productsMatrix)){
           
           if ($productsMatrix[$p->value] !== null){
               
               $newValues[$productsMatrix[$p->value]] = $p;
           }
            
        }
    }
    
    ksort($newValues);
  
    return $newValues;
}

/**
 * l'admin par dfault sera toujours cced dans l'email
 * l'admin par dfault sera tours le destinataire de l'email sauf si un numbro pays est renseign avec un autre email d'admin
 */
global $warningEmails;
$warningEmails = array(

    'default' => 'apichod@avid.com',
    '79' => 'g.chad@puremix.net' //France

);


/**
 * Matrix map id to redirect with the spot on map
 */
 global $urlMapLang;
$urlMapLang = array(

    10 => 208, //europe
    3 => 307, //german
    4 => 297, //espana
    6 => 201, //france
    7 => 222, //nordic
    1 => 159, //uk
    11 => 234, //middle east
    8 => 244, //africa
    13 => 159, //US
    5  => 343, //italia
    

);

/**
 * Matrix that assigns tags to a category
 * 
 */
 
 global $tagsMatrix;
 $tagsMatrix = array(
 
    //Education
    10 => array(
            
            array(2,3,4,5,6),
            
            array(6,8),
            
            array(8),
        ),
    
    //Film Mixing Stage
    9 => array(
    
            array(2),
            array(3)
        ),
    
    //Music Producer
    8 => array(
            
            array(2),
        ),
    
    //Post Facility
    3 => array(
    
            array(2,3,4,5,6),
            
            array(6,8),
            
            array(10),
        ),
    
    //Radio
    11 => array(
    
            array(),
        ),
    
    //Recording Studio
    2 => array(
    
            array(),
        ),
 );
