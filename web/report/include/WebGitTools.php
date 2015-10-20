<?php 

class WebGitTools
{

  public static function isHTTPs()
  {
    return (array_key_exists("HTTPS",$_SERVER) && strtolower($_SERVER["HTTPS"]) == "on");
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public static function guessProtocol()
  {
    $Protocol = "http://";
    if (array_key_exists("HTTPS",$_SERVER) && strtolower($_SERVER["HTTPS"]) == "on")
      $Protocol = "https://";
    
    return $Protocol;    
  }
     
  
  // =====================================================================
  // =====================================================================
  
  
  public static function getGitURL($Protocol,$Host,$URLSubDir,$Username="")
  {
    $AtUser = "";
    if (!empty($Username))
      $AtUser = $Username."@";
     
    return $Protocol.$AtUser.$Host."/".$URLSubDir;
  }
  
    
  // =====================================================================
  // =====================================================================
  
  
  public static function getPDFURL($Protocol,$Host,$SubURL)
  {
    return $Protocol.$Host."/".$SubURL;
  }
}

?>