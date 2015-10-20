<?php 

//error_reporting(E_ALL);


function getWaresInfos($WareType,$Username)
{
  global $WHSytemRootPath;
  global $DefsSetRootPath;

  include_once($WHSytemRootPath."/include/ReportingTools.php");
  include_once($WHSytemRootPath."/web/report/include/WebGitTools.php");
    
  $RTools = new ReportingTools($WHSytemRootPath."/config");
  $RTools->setActiveDefinitionsSet($DefsSetRootPath);

  $Report = $RTools->getWebReport();

  if (!isset($DefsSetGitHostname))
    $DefsSetGitHostname = $_SERVER["SERVER_ADDR"];
  
  if (!isset($DefsSetGitProtocol))
    $DefsSetGitProtocol = WebGitTools::guessProtocol();
           
  $JSONContent = "{\n";

  if (!empty($Report[$WareType]))
  {
        
    $FirstWare = true;
      
    foreach ($Report[$WareType] as $WID => $WData)
    {
      if (!$FirstWare)
      {
        $JSONContent .= ",\n";
      }        
      $JSONContent .= "  \"$WID\" : {\n";
      $FirstWare = false;  
          
      $ShortDesc = "";
      if (array_key_exists("shortdesc",$WData["definition"]))
        $ShortDesc = $WData["definition"]["shortdesc"];
      
      $JSONContent .= "    \"shortdesc\" : \"$ShortDesc\",\n";
          
      $JSONContent .= "    \"git-url\" : \"".WebGitTools::getGitUrl($DefsSetGitProtocol,
                                                                    $DefsSetGitHostname,
                                                                    $WData["git-url-subdir"],
                                                                    $Username)."\",\n";

      if (array_key_exists("branches",$WData))
      {
         $JSONContent .= "    \"git-branches\" : [";
         
         $FirstBranch = true;
         foreach ($WData["branches"] as $BranchName => $BranchData)
         {
           if (!$FirstBranch)
           {
             $JSONContent .= ",";
           }
           $FirstBranch = false;
           $JSONContent .= "\"$BranchName\"";
         }
         $JSONContent .= "],\n";
      }
      
      if (array_key_exists("open-issues",$WData))
      {
        $JSONContent .= "    \"issues-counts\" : {\n";         
        $JSONContent .= "      \"bugs\" : {$WData["open-issues"]["bug"]},\n";
        $JSONContent .= "      \"features\" : {$WData["open-issues"]["feature"]},\n";
        $JSONContent .= "      \"reviews\" : {$WData["open-issues"]["review"]}\n";
        $JSONContent .= "    },\n";
      }
      
      $JSONContent .= "    \"users-ro\" : [\"".implode("\",\"",$WData["definition"]["users-ro"])."\"],\n";
      $JSONContent .= "    \"users-rw\" : [\"".implode("\",\"",$WData["definition"]["users-rw"])."\"]\n";
          
      $JSONContent .= "  }";
    }      
  }      
  
  $JSONContent .= "\n}";
    
  return $JSONContent;
}


// =====================================================================



function isWareType($WareType)
{
  return in_array($WareType,array("simulators","observers","builderexts"));
}


// =====================================================================
// =====================================================================


$WHSytemRootPath = realpath(__DIR__."/../..");
$DefsSetRootPath = realpath(dirname($_SERVER["SCRIPT_FILENAME"])."/../..");

require("Slim/Slim.php");

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


// =====================================================================


// handle / : FluidHub informations
$app->get('/',function ()
{
  $app->response->headers->set('Content-Type', 'application/json ; charset=UTF8');
  
  echo "{\n";
  echo "  \"nature\" : \"OpenFLUID FluidHub\",\n";
  echo "  \"api-version\" : \"0.5-20151014\",\n";
  echo "  \"capabilities\" : [\"wareshub\"],\n";
  echo "  \"status\" : \"ok\"\n";
  echo "}";
});


// =====================================================================


$app->get('/wares',function () use($app)
{
  global $WHSytemRootPath;
  global $DefsSetRootPath;
  
  $app->response->headers->set('Content-Type', 'application/json ; charset=UTF8');

  $JSONContent = "{\n";
    
  include_once($WHSytemRootPath."/include/ReportingTools.php");
  
  $RTools = new ReportingTools($WHSytemRootPath."/config");
  $RTools->setActiveDefinitionsSet($DefsSetRootPath);
  
  $Report = $RTools->getWebReport();
  
    
  $WareTypes = ManagementTools::getWareTypes();
  
  $FirstType = true;
  
  foreach ($WareTypes as $WT)
  {
    if (!$FirstType)
      $JSONContent .= ",\n";
  
    $FirstType = false;
  
    $JSONContent .= "  \"${WT}s\" : ";
  
    if (empty($Report[$WT."s"]))
    {
      $JSONContent .= "[]";
    }
    else
    {
      $JSONContent .= "[\n";
  
      $FirstWare = true;
  
      foreach ($Report[$WT."s"] as $WID => $WData)
      {
        if (!$FirstWare)
        {
          $JSONContent .= ",\n";
        }
        $JSONContent .= "    \"$WID\"";
        $FirstWare = false;
      }
  
      $JSONContent .= "\n  ]";
    }
   }
   
  $JSONContent .= "\n}";
  
  echo $JSONContent;
});


// =====================================================================


$app->get('/wares/:waretype',function ($waretype) use($app)
{
  $username = $app->request()->params('username');

  if (isWareType($waretype))
  {
    $app->response->headers->set('Content-Type', 'application/json ; charset=UTF8');
    echo getWaresInfos($waretype,$username);
  }
  else
    $app->response->setStatus(401);
});


// =====================================================================


$app->put('/wares/:waretype/:wareid',function ($waretype,$wareid) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


$app->patch('/wares/:waretype/:wareid',function ($waretype,$wareid) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


$app->delete('/wares/:waretype/:wareid',function ($waretype,$wareid) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


$app->get('/wares/:waretype/:wareid/git',function ($waretype,$wareid) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


$app->get('/wares/:waretype/:wareid/git/:branch',function ($waretype,$wareid,$branch) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


$app->get('/wares/:waretype/:wareid/git/:branch/issues',function ($waretype,$wareid,$branch) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


$app->get('/wares/:waretype/:wareid/git/:branch/commits',function ($waretype,$wareid,$branch) use($app)
{
  $app->response->setStatus(501);
});


// =====================================================================


// handle errors
$app->notFound(function ()
{
  header('HTTP/1.1 400 Bad Request',true,400);
});


// =====================================================================


$app->run();



?>