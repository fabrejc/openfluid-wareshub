<?php 


include_once(__DIR__."/WebGitTools.php");

abstract class BasePageLayout
{
  
  protected $WareType;
    
  protected static $WARETYPES = array("simulator","observer","builderext");

  protected static $SingularWareTypes = array(
      "simulator" => "simulator",
      "observer" => "observer",
      "builderext" => "builder-extension"
  );
  
  protected static $PluralWareTypes = array(
      "simulator" => "simulators",
      "observer" => "observers",
      "builderext" => "builder-extensions"
  );
  
  protected static $MutedIssuesIcons = array(
      "bug" => "glyphicon-exclamation-sign text-muted",
      "feature"  => "glyphicon-circle-arrow-up text-muted",
      "review" => "glyphicon-eye-open text-muted"
  );
  
  
  protected static $EnlightedIssuesIcons = array(
      "bug" => "glyphicon-exclamation-sign issue-color-bug",
      "feature"  => "glyphicon-circle-arrow-up issue-color-feature",
      "review" => "glyphicon-eye-open issue-color-review"
  );
  
  
  protected $HTMLExtraScripts;
  
  // =====================================================================
  // =====================================================================
    
  
  function __construct()
  {
    $this->WareID = "";
    $this->WareType = "";
    $this->WareBranch = "";
    $this->HTMLExtraScripts = "";
  }
   
  // =====================================================================
  // =====================================================================
  
  
  abstract public function getPageContent();
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function setWareType($WareType)
  {
    $this->WareType = $WareType;
  }
  
  
  // =====================================================================
  // =====================================================================
        
  
  protected function getCompatibilityString($VersionsArray,$LongString = true)
  {
    $CompatVersions = "<i>unknown</i>";
    
    if (!empty($VersionsArray))
    {
      if (array_key_exists("openfluid-current-version",$_SESSION["wareshub"]["definitions-config"]) &&
          in_array($_SESSION["wareshub"]["definitions-config"]["openfluid-current-version"],$VersionsArray))
        $CompatVersions = $this->getCurrentBranchStarString();
      else
        $CompatVersions = $this->getBranchStarString();            
      
      $CompatVersions .= "&nbsp;".$VersionsArray[0];
      
      if (sizeof($VersionsArray) > 1)
      {
        if ($LongString)
          $CompatVersions .= " <span class='text-muted'>and ".implode(", ",array_slice($VersionsArray,1))."</span>";
        else
          $CompatVersions .= " <span class='text-muted'>and previous</span>";
      }
    } 

    return $CompatVersions;
  }
  

  // =====================================================================
  // =====================================================================
  
  
  protected function isBranchStar($BranchName)
  {
    $Pos = strpos($BranchName,"openfluid-");
    return ($Pos !== false && $Pos == 0 && preg_match("#(\d+\.\d+(\.\d+)*)$#", $BranchName, $MatchVersion));
  }
  
  
  // =====================================================================
  // =====================================================================

  
  protected function isCurrentVersionBranch($BranchName)
  {
    if (array_key_exists("openfluid-current-version",$_SESSION["wareshub"]["definitions-config"]) &&
        $BranchName == "openfluid-".$_SESSION["wareshub"]["definitions-config"]["openfluid-current-version"])
      return true;
    else
      return false;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  protected function getCurrentBranchStarString()
  {
    return "&nbsp;<span class='glyphicon glyphicon-star branch-star'></span>";
  }

  
  // =====================================================================
  // =====================================================================
  
  
  protected function getBranchStarString()
  {
    return "&nbsp;<span class='glyphicon glyphicon-star-empty branch-star'></span>";
  }

  
  // =====================================================================
  // =====================================================================
      
  
  private function getHiddenValuesForLoginBox()
  {
    $HiddenTxt = "";
    
    if (isset($_REQUEST["waretype"]))
      $HiddenTxt .= "<input type='hidden' name='waretype' value='".$_REQUEST["waretype"]."'>";
    
    if (isset($_REQUEST["wareid"]))
      $HiddenTxt .= "<input type='hidden' name='wareid' value='".$_REQUEST["wareid"]."'>";
    
    if (isset($_REQUEST["warebranch"]))
      $HiddenTxt .= "<input type='hidden' name='warebranch' value='".$_REQUEST["warebranch"]."'>";
    
    return $HiddenTxt;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  protected function isUserAdmin()
  {
    return ($_SESSION["login"]->isConnected() && 
            in_array($_SESSION["login"]->getUserName(),$GLOBALS["DefsSetAdminUsers"]));
  }
  

  // =====================================================================
  // =====================================================================
  
  
  private function getAdminBox()
  {
    $AdminLink = "";
    
    if ($_SESSION["login"]->isConnected() && $this->isUserAdmin())
    {
      $AdminLink = "&nbsp;&nbsp;<a href='".$_SERVER ["SCRIPT_NAME"]."?admin=1' class='btn btn-warning btn-xsm' role='button'>admin</a>";
    }

    return $AdminLink;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function getLoginBox()
  {
    if (!$GLOBALS["DefsSetLoginEnabled"])
      return "";
    
    if ($_SESSION["login"]->isConnected())
    {      
      return "<form class='navbar-form navbar-right' role='form' action='index.php' method='post'>
          <div class='form-group login-group'>
          <span class='glyphicon glyphicon-user'></span>&nbsp;".$_SESSION["login"]->getUserName()."&nbsp;&nbsp;
          <input type='hidden' name='disconnect' value='1'>".$this->getHiddenValuesForLoginBox()."
          <button type='submit' class='btn btn-default btn-xsm'>Sign out</button>
          </div>
          </form>";      
    }
    else
    {
      $ErrMsg = $_SESSION["login"]->getErrorMessage();
        
      if (!empty($ErrMsg))
        $ErrMsg = "<span class='login-errormsg'>$ErrMsg</span>";
          
      return "
            <div class='navbar-form navbar-right'>$ErrMsg &nbsp;            
              <button type='button' class='btn btn-default navbar-btn btn-xsm' data-container='body' data-toggle='login-popover' data-placement='bottom'>Sign in</button>
              <div id='login-popover-content-wrapper' style='display: none'>
                <form role='form' action='index.php' method='post'>
                  <div class='form-group'>
                    <input type='text' class='form-control input-sm' placeholder='Username' name='loginuser'>
                    <input type='password' class='form-control input-sm login-field' placeholder='Password' name='loginpwd'>
                    <label class='login-checkbox'>
                      <input type='checkbox' class='login-checkbox' name='loginremember'>&nbsp;Remember me for 14 days
                    </label>".$this->getHiddenValuesForLoginBox()."  
                  </div>            
                 <button type='submit' class='btn btn-default btn-sm'>Sign in</button>
               </form>
             </div>
           </div> 
           <script>
             $('button[data-toggle=login-popover]').popover({ 
               html : true,
               content: function() {
                 return $('#login-popover-content-wrapper').html();
               }
             });
	        </script>                    
          ";
    }
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function generatePage()
  {
    echo "
   <html>
     <head>
      <title>Wareshub</title>
      <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
      <link rel='icon' type='image/png' href='data/images/favicon.png' />
      <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400|Open+Sans+Condensed:300,400' rel='stylesheet' type='text/css'>
      <link rel='stylesheet' href='//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css'>
      <link rel='stylesheet' href='//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css'>
      <script src='//code.jquery.com/jquery-1.11.0.min.js'></script>
      <script src='//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js'></script>
      ".$this->HTMLExtraScripts."
    
      <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
        <script src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>
        <script src='https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js'></script>
      <![endif]-->   

      <style  type='text/css'>
    ";

    echo file_get_contents($_SESSION["wareshub"]["dirs"]["system-web-report"]."/css/wareshub.css");
    
    echo "
      </style>  
    </head>
    <body>";
    
    echo "<div id='wrap'>";

    echo "
        <nav class='navbar navbar-topmenu' role='navigation'>
          <div class='container'>
            <div class='navbar-header'>".$_SESSION["wareshub"]["labels"]["defsset-title"].$this->getAdminBox()."</div>
            <div class=''>".$this->getLoginBox()."</div>    
          </div>              
        </nav>
     ";
    
    $this->getPageContent();    
    
    echo "  <div id='push'></div>
          </div>";
    echo "

         <div id='footer'>
           <div class='container'>
             <p class='text-center'><br>".$_SESSION["wareshub"]["labels"]["defsset-footer"]."</p>
           </div>
         </div>
       </body>
     </html>
   ";
    
  }    
  
}


?>