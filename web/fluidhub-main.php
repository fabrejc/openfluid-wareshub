<?php 


if (array_key_exists("request",$_REQUEST))
{
  $WHSytemRootPath = realpath(__DIR__."/..");
  $DefsSetRootPath = realpath(dirname($_SERVER["SCRIPT_FILENAME"])."/..");   
  
  if ($_REQUEST["request"] == "nature")
  {
    header ( "Content-Type: text/plain; charset=UTF8" );
    echo "OpenFLUID fluidhub";
  }

  // =====================================================================
  
  else if ($_REQUEST["request"] == "name")
  {
    include_once($WHSytemRootPath."/include/ReportingTools.php");
    
    $RTools = new ReportingTools($WHSytemRootPath."/config");
    $RTools->setActiveDefinitionsSet($DefsSetRootPath);
    
    $DefsConfig = $RTools->getActiveDefsConfig();
    
    header ( "Content-Type: text/plain; charset=UTF8" );
    echo $DefsConfig["name"];
    
  } 
  
  // =====================================================================
  
  else if ($_REQUEST["request"] == "capabilities")
  {
    header ( "Content-Type: application/json; charset=UTF8" );
    
    echo "{\n";
    echo "  \"capabilities\" : [\n";
    echo "    \"wares-list\",\n";
    echo "    \"wares-list-detailed\"\n";
    echo "   ]\n";
    echo "}";
  }
  else if ($_REQUEST["request"] == "wares-list")
  {
    include_once($WHSytemRootPath."/include/ReportingTools.php");
    
    $RTools = new ReportingTools($WHSytemRootPath."/config");
    $RTools->setActiveDefinitionsSet($DefsSetRootPath);

    $Report = $RTools->getWebReport();
    
    header ( "Content-Type: application/json; charset=UTF8" );
    
    echo "{\n";
    echo "  \"wares\" : {\n";
    
    
    $WareTypes = ManagementTools::getWareTypes();
    
    $FirstType = true;
    
    foreach ($WareTypes as $WT)
    {
      if (!$FirstType)
        echo ",\n";
      
      $FirstType = false;
      
      echo "    \"${WT}s\" : ";
      
      if (empty($Report[$WT."s"]))
      {
        echo "[]";
      }
      else
      {  
        echo "[\n";
        
        $FirstWare = true;
      
        foreach ($Report[$WT."s"] as $WID => $WData)
        {
          if (!$FirstWare)
          {
            echo ",\n";
          }        
          echo "      \"$WID\"";        
          $FirstWare = false;  
        }      
        
        echo "\n    ]";
      }      
    }        
    echo "\n  }\n}";
    
  }
  
  // =====================================================================
  
  else if ($_REQUEST["request"] == "wares-list-detailed")
  {
    include_once($WHSytemRootPath."/include/ReportingTools.php");    
    include_once(__DIR__."/include/WebGitTools.php");
    
    
    $RTools = new ReportingTools($WHSytemRootPath."/config");
    $RTools->setActiveDefinitionsSet($DefsSetRootPath);

    $Report = $RTools->getWebReport();
    
    if (!isset($DefsSetGitHostname))
      $DefsSetGitHostname = $_SERVER["SERVER_ADDR"];
    
    if (!isset($DefsSetGitProtocol))
      $DefsSetGitProtocol = WebGitTools::guessProtocol();
    
    
    header ( "Content-Type: application/json; charset=UTF8" );
    
    echo "{\n";
    echo "  \"wares\" : {\n";
        
    
    $WareTypes = ManagementTools::getWareTypes();
    
    $FirstType = true;
    
    foreach ($WareTypes as $WT)
    {
      if (!$FirstType)
        echo ",\n";
      
      $FirstType = false;
      
      echo "    \"${WT}s\" : ";
      
      if (empty($Report[$WT."s"]))
      {
        echo "{}";
      }
      else
      {  
        echo "{\n";
        
        $FirstWare = true;
      
        foreach ($Report[$WT."s"] as $WID => $WData)
        {
          if (!$FirstWare)
          {
            echo ",\n";
          }        
          echo "      \"$WID\" : {\n";        
          $FirstWare = false;  
          
          $ShortDesc = "";
          if (array_key_exists("shortdesc",$WData["definition"]))
            $ShortDesc = $WData["definition"]["shortdesc"];
          echo "        \"shortdesc\" : \"$ShortDesc\",\n";
          
          echo "        \"git-url\" : \"".WebGitTools::getGitUrl($DefsSetGitProtocol,$DefsSetGitHostname,$WData["git-url-subdir"])."\",\n";
          
          echo "        \"users-ro\" : [\"".implode("\",\"",$WData["definition"]["users-ro"])."\"],\n";
          echo "        \"users-rw\" : [\"".implode("\",\"",$WData["definition"]["users-rw"])."\"]\n";
          
          echo "      }";
        }      
        
        echo "\n    }";
      }      
    }        
    echo "\n  }\n}";
  }
  
  // =====================================================================
  
  else
  {
    header ( 'HTTP/1.1 400 Bad Request', true, 400 );
  }
  
  // =====================================================================
  
} 
else
{
  header ( 'HTTP/1.1 400 Bad Request', true, 400 );
}


?>