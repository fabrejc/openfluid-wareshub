<?php


session_start();

import_request_variables("gP", "whreq_");


if (isset($whreq_reset))
{
  session_destroy();
  header("Location: ".$_SERVER["SCRIPT_NAME"]);
}


#######


if (!isset($_SESSION["wareshub"]))
{

  $WHSytemRootPath = realpath(__DIR__."/..");
  $DefsSetRootPath = realpath(dirname($_SERVER["SCRIPT_FILENAME"])."/..");  
  
  include_once($WHSytemRootPath."/include/ReportingTools.php");
  
  $RTools = new ReportingTools($WHSytemRootPath."/config");
  $RTools->setActiveDefinitionsSet($DefsSetRootPath);
  
  $Report = $RTools->getWebReport();
  
  $_SESSION["wareshub"] = array();
  $_SESSION["wareshub"]["reporting"] = array();
  $_SESSION["wareshub"]["reporting"]["simulators"] = $Report["simulators"];
  $_SESSION["wareshub"]["reporting"]["observers"] = $Report["observers"];
  $_SESSION["wareshub"]["reporting"]["builderexts"] = $Report["builderexts"];
  $_SESSION["wareshub"]["dirs"] = array();
  $_SESSION["wareshub"]["dirs"]["system-root"] = $WHSytemRootPath;
  $_SESSION["wareshub"]["dirs"]["system-config"] = $WHSytemRootPath."/config";
  $_SESSION["wareshub"]["dirs"]["system-web"] = $WHSytemRootPath."/web";
  $_SESSION["wareshub"]["dirs"]["defsset-root"] = $DefsSetRootPath;  
  $_SESSION["wareshub"]["dirs"]["defsset-web"] = $DefsSetRootPath."/web";
  
  if (!isset($DefsSetTitle))
    $DefsSetTitle = "OpenFLUID-WaresHub";
  
  $_SESSION["wareshub"]["labels"]["defsset-title"] = $DefsSetTitle;
  
  if (!isset($DefsSetIntro))
    $DefsSetIntro = "This OpenFLUID WaresHub hosts simulators, observers and builder-extensions for OpenFLUID";
  
  $_SESSION["wareshub"]["labels"]["defsset-intro"] = $DefsSetIntro;
  
  
  if (!isset($DefsSetGitHostname))
    $DefsSetGitHostname = $_SERVER["SERVER_ADDR"];
    
  $_SESSION["wareshub"]["url"]["defsset-githost"] = $DefsSetGitHostname; 
}  


#######


include_once(__DIR__."/include/WaresHubLayout.php");

$Layout = new WaresHubLayout();

if (isset($whreq_waretype))
{
  $Layout->setWareType($whreq_waretype);
  
  if (isset($whreq_wareid))
  {
    $Layout->setWareID($whreq_wareid);
    
    if (isset($whreq_warebranch))
    {
      $Layout->setWareBranch($whreq_warebranch);
    }
    else
    {
      $WareData = $_SESSION["wareshub"]["reporting"][$whreq_waretype."s"][$whreq_wareid];
      
      if (!empty($WareData["compat-versions"]))
      {
        // take the higher compat version as default branch
        $Layout->setWareBranch("openfluid-".$WareData["compat-versions"][0]);
      }
      else if (!empty($WareData["branches"]))
      {
        // take the first branch
        reset($WareData["branches"]);
        $Layout->setWareBranch(key($WareData["branches"]));
      }
    }
  }
  
}



$Layout->generatePage();


?>