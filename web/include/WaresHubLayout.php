<?php 

class WaresHubLayout
{
  
  private $WareType;
  private $WareID;
  private $WareBranch;
  
  
  private static $WARETYPES = array("simulator","observer","builderext");
  
  function __construct()
  {
    $this->WareID = "";
    $this->WareType = "";
    $this->WareBranch = "";
  }
   
  
  // =====================================================================
  // =====================================================================
  
  
  public function setWareType($WareType)
  {
    $this->WareType = $WareType;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  public function setWareID($WareID)
  {
    $this->WareID = $WareID;
  }
  

  // =====================================================================
  // =====================================================================
  
  
  public function setWareBranch($WareBranch)
  {
    $this->WareBranch = $WareBranch;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function getCompatibilityString($VersionsArray)
  {
    $CompatVersions = "<i>unknown</i>";
    
    if (!empty($VersionsArray))
    {
      $CompatVersions = $VersionsArray[0]; 
      
      if (sizeof($VersionsArray) > 1)
      {
        $CompatVersions .= " <span class='text-muted'>and ".implode(", ",array_slice($VersionsArray,1))."</span>";
      }
    } 

    return $CompatVersions;
  }
  

  // =====================================================================
  // =====================================================================
  
  
  private function getGrantedUsersString($UsersArray)
  {
    if (empty($UsersArray))
      return "<i>no user</i>";
    else if (in_array("*",$UsersArray))
      return "<i>all users</i>";
    else return implode(", ",$UsersArray);
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function isBranchStar($BranchName)
  {
    $Pos = strpos($BranchName,"openfluid-");
    return ($Pos !== false && $Pos == 0 && preg_match("#(\d+\.\d+(\.\d+)*)$#", $BranchName, $MatchVersion));
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function getBranchStarString()
  {
    return "&nbsp;<span class='glyphicon glyphicon-star-empty branch-star'></span>";
  }


  // =====================================================================
  // =====================================================================
  
  
  private function getBranchesDropdown($BranchesArray)
  {
    if (empty($BranchesArray))
      return "<i>none</i>";
    else
    {            
      $TmpStr = "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'>";
      $TmpStr .= $this->WareBranch;
      
      if ($this->isBranchStar($this->WareBranch))
        $TmpStr .= $this->getBranchStarString();
        
      $TmpStr .= "&nbsp;&nbsp;<span class='caret'></span>";
      $TmpStr .= "</button>";
      $TmpStr .= "<ul class='dropdown-menu' role='menu'>";

      $StarBranches = array();
      $OtherBranches = array();
      
      foreach ($BranchesArray as $BranchName => $BranchInfos)
      {
        if ($this->isBranchStar($BranchName))
          array_push($StarBranches,$BranchName);
        else
          array_push($OtherBranches,$BranchName); 
      }
      
      rsort($StarBranches);
      $StarString = $this->getBranchStarString();
      
      foreach ($StarBranches as $BranchName)             
        $TmpStr .= "<li><a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."&wareid=".$this->WareID."&warebranch=".$BranchName."'>$BranchName $StarString</a></li>";
  
      if (!empty($StarBranches) && !empty($OtherBranches))
        $TmpStr .= " <li class='divider'></li>";

      foreach ($OtherBranches as $BranchName)
        $TmpStr .= "<li><a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."&wareid=".$this->WareID."&warebranch=".$BranchName."'>$BranchName</a></li>";
      
      
      $TmpStr .= "</ul>";
      $TmpStr .= "</div>";
      
      return $TmpStr;
    }
  
  }

  
  // =====================================================================
  // =====================================================================
  
  
  private function getContributorsString($CommittersArray)
  {
    $TmpStr = "";
    
    if (empty($CommittersArray))
      $TmpStr = "<i>unknown</i>";
    else
    {
      $TmpStr .= "<div class='contributors-list'>";
      
      foreach($CommittersArray as $CommitterName => $CommitterInfos)  
      {
        $TmpStr .= "              
              <div class='media contributor'>
                <a class='pull-left' href='#'>
                  <img class='media-object' src='http://www.gravatar.com/avatar/".md5(strtolower(trim($CommitterInfos["email"])))."?s=36&d=mm'/>
                </a>
                <div class='media-body'>                
                  ".$CommitterName."<br/>
                  <span class='text-muted commit-author'>".$CommitterInfos["count"]." commit(s)</span>
                </div>
              </div>             
            ";
      }
      $TmpStr .= "</div>";
    }
      
    return $TmpStr;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function isHTTPs()
  {
    //return strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? true : false;
    return (array_key_exists("HTTPS",$_SERVER) && strtolower($_SERVER["HTTPS"]) == "on");    
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function getGitURL($URLSubDir)
  {
    $Protocol = "http://";
    
    if ($this->isHTTPs())
      $Protocol = "https://";
    
    return $Protocol.$_SESSION["wareshub"]["url"]["defsset-githost"]."/".$URLSubDir;
  }
  
  
  // =====================================================================
  // =====================================================================

  
  private function printCommitsHistory()
  {
    $TypeKey = $this->WareType."s";
    $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];

    foreach($WareData["branches"][$this->WareBranch]["commits-history"] as $CommitID => $CommitData)
    {
      echo "
          <div class='panel panel-info panel-commit'>
            <div class='panel-heading commit-heading'>".$CommitData["date"]."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;$CommitID</div>
            <div class='panel-body'>
              <div class='media'>
                <a class='pull-left' href='#'>
                  <img class='media-object' src='http://www.gravatar.com/avatar/".md5(strtolower(trim($CommitData["authoremail"])))."?s=36&d=mm'/>
                </a>
                <div class='media-body'>                
                  <b>".$CommitData["subject"]."</b><br/>
                  <span class='text-muted commit-author'>".$CommitData["authorname"]."</span><br/>
                </div>
              </div> 
            </div>               
          </div>
          ";
    }
  }

  
  // =====================================================================
  // =====================================================================
  
  
  private function printGeneralInformations()
  {
    $TypeKey = $this->WareType."s";
    $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];

    echo "<br/>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;".sizeof($WareData["branches"][$this->WareBranch]["commits-history"])." commit(s)";
    echo "<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;<i>More informations available soon!</i>";    
  }
  
  
  // =====================================================================
  // =====================================================================
    
  
  private function generateWareContent()
  {
    $TypeKey = $this->WareType."s";
    $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];
    
    echo "<br/>";
    
    echo "<div class='container'>";
    
    echo "<h3>
            <a href='".$_SERVER["SCRIPT_NAME"]."'><span class='glyphicon glyphicon-list'></span></a>&nbsp;&nbsp;/
                <a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."'>".$TypeKey."</a>&nbsp;/
            ".$this->WareID."</h3><br/>";

    echo "<div class='row'>";
    
    echo "<div class='col-md-6'>";
    

    
    echo "OpenFLUID version(s): ".$this->getCompatibilityString($WareData["compat-versions"])."<br/>";
    echo "<br/>";
    
    if (array_key_exists("committers",$WareData))
    {    
      echo sizeof($WareData["committers"])." contributors: ";
      echo $this->getContributorsString($WareData["committers"]);    
    }

    
    echo "</div>";
    
    echo "<div class='col-md-6'>";
    
    echo "
      <div class='panel panel-success'>
        <div class='panel-heading'>git access</div>        
        <div class='panel-body'>
        
         <b>URL:</b>
         <input class='' type='text' readonly='readonly' size='40' value='".$this->getGitURL($WareData["git-url-subdir"])."'></input><br/>
         <br>         
          <li>Read access for ".$this->getGrantedUsersString($WareData["definition"]["users-ro"])."</li>
          <li>Write access for ".$this->getGrantedUsersString($WareData["definition"]["users-rw"])."</li>
         </div>
      </div>
    ";
    
    echo "</div>";
    
    echo "</div>";
    
    echo "<hr class='warepage'>";    
        
    if (empty($this->WareBranch))
    {
      echo "<h5><i>This ".$this->WareType." seems to be empty<i></h5>";
    }
    else
    {
      echo "<h4>Branch: ".$this->getBranchesDropdown($WareData["branches"])."</h4><br/>";      
      
      echo "
      <ul class='nav nav-tabs'>
        <li class='active'><a href='#general' data-toggle='tab'>General information</a></li>
        <li><a href='#commits' data-toggle='tab'>Commits history</a></li>
      </ul>";
      
      echo "
      <div class='tab-content'>
        <div class='tab-pane active' id='general'>";
      $this->printGeneralInformations();
      echo "  
        </div>
        <div class='tab-pane' id='commits'><br/>";
      $this->printCommitsHistory();
      echo  "
        </div>
      </div>
      ";
    }
   
    
    echo "</div>";
  }

  
  // =====================================================================
  // =====================================================================

  
  private function generateHomeContent()
  {
    if (! in_array ( $this->WareType, static::$WARETYPES ))
      $this->WareType = "simulator";
    
    $TypeKey = $this->WareType . "s";
    
    echo "<div class='jumbotron'>
        <div class='container'>
      ";
    
    echo $_SESSION ["wareshub"] ["labels"] ["defsset-intro"];
    echo "<br/>";
    echo "&nbsp;&nbsp;<a href='" . $_SERVER ["SCRIPT_NAME"] . "?reset=1'><span class='glyphicon glyphicon-refresh'></span>&nbsp;Reload informations</a>";
    
    echo "
          <br/><br/>
          <div style='margin-left: 100px;'>
          <ul class='nav nav-pills'>
      ";
    
    foreach ( static::$WARETYPES as $PillType )
    {
      echo "<li";
      if ($PillType == $this->WareType)
        echo " class='active'";
      echo ">";
      echo "<a href='" . $_SERVER ["SCRIPT_NAME"] . "?waretype=${PillType}'>";
      echo ucfirst ( "${PillType}s" );
      echo "  <span class='badge'>" . sizeof ( $_SESSION ["wareshub"] ["reporting"] [$PillType . "s"] ) . "</span>";
      echo "</a></li>";
    }
    
    echo "</ul>
  
          </div>
          ";
    
    echo "</div></div>";
    
    echo "<div class='container'>";
    
    $WareCount = sizeof ( $_SESSION ["wareshub"] ["reporting"] [$TypeKey] );
    
    if ($WareCount == 0)
    {
      echo "<i>There is no $this->WareType available";
    }
    else
    {
      $WareTypeInfos = $_SESSION ["wareshub"] ["reporting"] [$TypeKey];
      
      echo "<table class='table'>";
      echo "<tr><th>ID</th><th>OpenFLUID compatibility</th></tr>";
      
      foreach ( $WareTypeInfos as $WareID => $WareData )
      {
        echo "<tr>
          <td><a href='" . $_SERVER ["SCRIPT_NAME"] . "?waretype=" . $this->WareType . "&wareid=" . $WareID . "'>$WareID</a>";
        
        if (array_key_exists ( "shortdesc", $WareData ["definition"] ) && ! empty ( $WareData ["definition"] ["shortdesc"] ))
        {
          echo "<div class='mainshortdesc'><span class='text-muted'>" . $WareData ["definition"] ["shortdesc"] . "</span></div>";
        }
        echo "  </td>
          <td>" . $this->getCompatibilityString ( $WareData ["compat-versions"] ) . "</td>
          </tr>";
      }
      
      echo "</table>";
    }
    
    echo "</div>";
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
    
      <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
        <script src='https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js'></script>
        <script src='https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js'></script>
      <![endif]-->   

      <style  type='text/css'>
    ";

    echo file_get_contents($_SESSION["wareshub"]["dirs"]["system-web"]."/css/wareshub.css");
    
    echo "
      </style>  
    </head>
    <body>";
    
    echo "<div id='wrap'>";

    echo "
        <nav class='navbar navbar-topmenu' role='navigation'>
          <div class='container'><h3>".$_SESSION["wareshub"]["labels"]["defsset-title"]."</h3></div>
        </nav>
     ";
    
    if (!empty($this->WareID))
      $this->generateWareContent();
    else
      $this->generateHomeContent();
    
    
    echo "</div>";
    echo "</body>";    
  }    
  
}


?>