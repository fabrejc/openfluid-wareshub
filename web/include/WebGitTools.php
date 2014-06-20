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
    $Protocol = "http://";
  
    if (self::isHTTPs())
      $Protocol = "https://";
  
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