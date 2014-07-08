<?php 

class WebGitTools
{

  public static function isHTTPs()
  {
    return (array_key_exists("HTTPS",$_SERVER) && strtolower($_SERVER["HTTPS"]) == "on");
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public static function getGitURL($Host,$URLSubDir)
  {
    if (array_key_exists("defsset-gitprotocol",$_SESSION["wareshub"]["url"]))
      $Protocol = $_SESSION["wareshub"]["url"]["defsset-gitprotocol"];
    else 
    {
      $Protocol = "http://";

      if (self::isHTTPs())
        $Protocol = "https://";
    }
    
    return $Protocol.$Host."/".$URLSubDir;
  }

  
  // =====================================================================
  // =====================================================================
  
  
  public static function getPDFURL($Host,$SubURL)
  {
    $Protocol = "http://";
  
    if (self::isHTTPs())
      $Protocol = "https://";
  
    return $Protocol.$Host."/".$SubURL;
  }
}

?>