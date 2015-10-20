<?php 

$AuthIncFilePath = dirname($_SERVER["SCRIPT_FILENAME"])."/include/auth.php";

include_once($AuthIncFilePath);


class LoginManager
{
  
  private $Connected;
  
  private $UserName;
  
  private $CookieName;
  private $CookieSecretKey;
  
  private $ErrorMessage;
  
  
  public function __construct()
  { 
    $this->Connected = false;
    $this->ErrorMessage = "";
    $this->UserName = "";
    $this->CookieName = $GLOBALS["DefsSetCookieBaseName"]+"-auth";
    $this->CookieSecretKey = $GLOBALS["DefsSetCookieSecretKey"];
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function isConnected()
  {
    //echo "isConnected() : ".$this->Connected."<BR>";
    return $this->Connected;
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public function getUserName()
  {
    return $this->UserName;
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public function getErrorMessage()
  {
    return $this->ErrorMessage;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function resetErrorMessage()
  {
    $this->ErrorMessage = "";
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function generateCookie($ID,$Expiration)
  {
    $Key = hash_hmac( 'md5', $ID . $Expiration,$this->CookieSecretKey);
    $Hash = hash_hmac( 'md5', $ID . $Expiration, $Key );
  
    $Cookie = $ID . '|' . $Expiration . '|' . $Hash;
  
    return $Cookie;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function setCookie($ID,$Remember=false)
  { 
//    echo "in setCookie()<BR>";
    
    if ($Remember)
      $Expiration = time() + 1209600; // 14 days
    else 
      $Expiration = time() + 21600; // 6 hours
    
    $Cookie = $this->generateCookie($ID,$Expiration);
  
    $Ret = setcookie($this->CookieName,$Cookie,$Expiration);
    
    //echo "out setCookie() $Cookie<BR>";
        
    return $Ret;    
  }
  

  // =====================================================================
  // =====================================================================
  
  
  private function verifyCookie()
  {
    
    //echo "in verifyCookie()<BR>";
  
    if (empty($_COOKIE[$this->CookieName]))
    {
      //echo "out verifyCookie() empty cookie<BR>";
      $this->disconnect();
//      $this->ErrorMessage = "Credentials cannot be verified";
      return false;
    }
  
    list($ID,$Expiration,$HMAC) = explode('|',$_COOKIE[$this->CookieName]);
  
    $Expired = $Expiration;
  
    if ($Expired < time())
    {
      //echo "out verifyCookie() expired<BR>";
      $this->disconnect();
      $this->ErrorMessage = "Credentials has expired";
      return false;
    }
  
    $Key = hash_hmac( 'md5', $ID . $Expiration,$this->CookieSecretKey);
    $Hash = hash_hmac( 'md5', $ID . $Expiration, $Key );
  
    if ($HMAC != $Hash)
    {
      //echo "out verifyCookie() bad hash<BR>";
      $this->disconnect();
      $this->ErrorMessage = "Wrong credentials";
      return false;
    }

    //echo "out verifyCookie() good!!!<BR>";
    $this->Connected = true;
    $this->UserName = $ID;
    $this->resetErrorMessage();
    return true;  
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function deleteCookie()
  {
    //echo "in deleteCookie()<BR>";
    setcookie($this->CookieName,"",time()-86400);
    //echo "out deleteCookie()<BR>";
  }
  

  // =====================================================================
  // =====================================================================
  
  
  public function connect($User,$Passwd,$Remember=false)
  {
     //echo "in connect() keep? $Remember<BR>";
     if (!empty($User))
     {
       $this->deleteCookie();
       if (AuthProcess::run($User,$Passwd))
       {
         $this->Connected = $this->setCookie($User,$Remember);
       }
       else
       {
         $this->ErrorMessage = "Wrong username/password";
         $this->Connected = false;
       }
     }
     else
       $this->Connected = false;
     
     if ($this->Connected)
       $this->UserName = $User;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function disconnect()
  {
    $this->deleteCookie();
    $this->Connected = false;
    $this->UserName = "";
  }
  

  // =====================================================================
  // =====================================================================
  
  
  public function check()
  {
    $this->verifyCookie(); 
  }
  
}
?>