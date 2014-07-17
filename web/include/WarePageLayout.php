<?php

include_once(__DIR__."/BasePageLayout.php");

class WarePageLayout extends BasePageLayout
{
  private $WareID;
  private $WareBranch;
  
  private $Issues;
  private $OpenIssuesStats;
  
  
  // =====================================================================
  // =====================================================================
  
  
  function __construct()
  {
    $this->WareID = "";
    $this->WareBranch = "";
    
    $this->Issues = array();
    $this->OpenIssuesStats = array();
    $this->OpenIssuesStats["bug"] = 0;
    $this->OpenIssuesStats["feature"] = 0;
    $this->OpenIssuesStats["review"] = 0;
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
  
  private function getBranchesDropdown($BranchesArray)
  {
    if (empty($BranchesArray))
      return "<i>none</i>";
    else
    {
      $TmpStr = "<div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown'>";
      $TmpStr .= $this->WareBranch;
  
      if ($this->isBranchStar($this->WareBranch))
      {
        if ($this->isCurrentVersionBranch($this->WareBranch))
          $TmpStr .= $this->getCurrentBranchStarString();
        else
          $TmpStr .= $this->getBranchStarString();
      }
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
      $CurrentStarString = $this->getCurrentBranchStarString();
  
      foreach ($StarBranches as $BranchName)
      {
        if ($this->isCurrentVersionBranch($BranchName))
          $TmpStr .= "<li><a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."&wareid=".$this->WareID."&warebranch=".$BranchName."'>$BranchName $CurrentStarString</a></li>";
        else
          $TmpStr .= "<li><a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."&wareid=".$this->WareID."&warebranch=".$BranchName."'>$BranchName $StarString</a></li>";
      }
  
  
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
  
  
  private function getStatusString($Status)
  {
    if ($Status == "stable")
      return "<span class='text-success'>$Status</span>";
  
      return $Status;
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function printCommitsHistory()
  {
  $TypeKey = $this->WareType."s";
  $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];
  
  $CommitsCount = sizeof($WareData["branches"][$this->WareBranch]["commits-history"]);
  
  echo "&nbsp;&nbsp;<b>$CommitsCount commit(s) in this branch</b><br/>";
  echo "<br/>";
  
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


    if (array_key_exists("wareshub",$WareData["branches"][$this->WareBranch]))
    {
      $WaresHubBranchData = $WareData["branches"][$this->WareBranch]["wareshub"];



      if (array_key_exists("description",$WaresHubBranchData) &&
          !empty($WaresHubBranchData["description"]))
      {
        echo "Detailed description: ".$WaresHubBranchData["description"]."<br/>";
        echo "<br/>";
      }


      if (array_key_exists("tags",$WaresHubBranchData) &&
          is_array($WaresHubBranchData["tags"]) &&
          !empty($WaresHubBranchData["tags"]))
      {
        foreach ($WaresHubBranchData["tags"] as $Tag)
        {
          echo "<span class='label label-tag'>#$Tag</span>&nbsp;";
        }
        echo "<br/><br/>";
      }

      echo "Development status: ";
      if (array_key_exists("status",$WaresHubBranchData) &&
          !empty($WaresHubBranchData["status"]))
        echo $this->getStatusString($WaresHubBranchData["status"]);
      else
        echo "<span class='text-muted'>not specified</span>";
      echo "<br/>";

      echo "<br/>";

      echo "License: ";
      if (array_key_exists("license",$WaresHubBranchData) &&
          !empty($WaresHubBranchData["license"]))
        echo $WaresHubBranchData["license"];
      else
        echo "<span class='text-muted'>not specified</span>";
      echo "<br/>";

      echo "<br/>";

      echo "Contacts:<br/>";
      foreach ($WaresHubBranchData["contacts"] as $Email)
      {
        echo "&nbsp;&nbsp;<a href=mailto:$Email><span class='glyphicon glyphicon-envelope'></span>&nbsp;$Email</a>";
      }

    }
    else
    {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;<i>No information available</i>";
    }

  }


  // =====================================================================
  // =====================================================================
  
  
  private function rebuildIssues()
  {
    $this->Issues = array();
    
    $this->Issues["open"] = array();
    $this->Issues["closed"] = array();
    
    $TypeKey = $this->WareType."s";
    $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];
    
        
    if (array_key_exists("wareshub",$WareData["branches"][$this->WareBranch]))
    {
      $WaresHubBranchData = $WareData["branches"][$this->WareBranch]["wareshub"];
    
      $HasIssues = array_key_exists("issues",$WaresHubBranchData) &&
                   !empty($WaresHubBranchData["issues"]);
      
      if ($HasIssues)
      {
        foreach ($WaresHubBranchData["issues"] as $IssueID => $IssueData)
        {
          if (array_key_exists("title",$IssueData) &&
              array_key_exists("creator",$IssueData) &&
              array_key_exists("date",$IssueData) &&
              array_key_exists("type",$IssueData) &&
              array_key_exists("state",$IssueData))
          {
            $NewIssue = array();
            $NewIssue["ID"] = $IssueID;
            $NewIssue["title"] = $IssueData["title"];
            $NewIssue["creator"] = $IssueData["creator"];
            $NewIssue["date"] = $IssueData["date"];
            $NewIssue["type"] = $IssueData["type"];
            $NewIssue["description"] = "";
            $NewIssue["urgency"] = "not precised";
            
            if (array_key_exists("description",$IssueData))
              $NewIssue["description"] = $IssueData["description"];
            
            if (array_key_exists("urgency",$IssueData))
              $NewIssue["urgency"] = $IssueData["urgency"];
                          
            // issues with missing state or state != closed are considered as open
            if (!array_key_exists("state",$IssueData) || 
                $IssueData["state"] != "closed")
            {
              array_push($this->Issues["open"],$NewIssue);
              $this->OpenIssuesStats[$IssueData["type"]]++;
            }
            else
              array_push($this->Issues["closed"],$NewIssue);
          }              
        }
      }      
    }  
  }
  
  
  // =====================================================================
  // =====================================================================
  
  
  private function printIssues()
  {
    echo "<div class='issue-stat'>";
    echo "<b>Open issue(s):</b>";
    echo "</div>";
    
    $IssuesTypes = array("bug" => "bug(s) to fix",
                         "feature" => "new feature(s) requested",
                         "review" => "review(s) needed");
    
    foreach ($IssuesTypes as $IssueType => $IssueText)
    {
      echo "<div class='issue-stat'>";
      
      if ($this->OpenIssuesStats[$IssueType] > 0)    
        echo "<span class='glyphicon ".self::$EnlightedIssuesIcons[$IssueType]."'></span>";
      else
        echo "<span class='glyphicon ".self::$MutedIssuesIcons[$IssueType]."'></span>";
      
      echo "&nbsp;{$this->OpenIssuesStats[$IssueType]} $IssueText";
      echo "</div>";
    }  
    
    
    echo "</br>";
    echo "</br>";
    
    if (!empty($this->Issues["open"]))
    {
      foreach ($this->Issues["open"] as $Issue)
      {
        echo "
          <div class='panel panel-default panel-issue'>
            <div class='panel-heading issue-heading'>
              <span class='glyphicon ".self::$EnlightedIssuesIcons[$Issue["type"]]."'></span><b>&nbsp;{$Issue["title"]}</b><br>
              <span class='text-muted'>Created by {$Issue["creator"]} on {$Issue["date"]}, urgency is {$Issue["urgency"]}</span>              
            </div>
            <div class='panel-body'>".nl2br($Issue["description"])."</div>
          </div>      
          ";        
      } 
      
    }
    
  }

  
  // =====================================================================
  // =====================================================================  
  

  public function getPageContent()
  {
    $TypeKey = $this->WareType."s";
    $WareData = $_SESSION["wareshub"]["reporting"][$TypeKey][$this->WareID];

    echo "<br/>";

    echo "<div class='container'>";

    echo "<h3>
        <a href='".$_SERVER["SCRIPT_NAME"]."'><span class='glyphicon glyphicon-list'></span></a>&nbsp;&nbsp;/
            <a href='".$_SERVER["SCRIPT_NAME"]."?waretype=".$this->WareType."'>".$TypeKey."</a>&nbsp;/
                ".$this->WareID."</h3>";

    if (array_key_exists("shortdesc",$WareData["definition"]))
      echo "<h4>".$WareData["definition"]["shortdesc"]."</h4>";

    echo "<br/>";

    echo "<div class='row'>";

    echo "<div class='col-md-6'>";

    if (!empty($WareData["pdfdoc-url-subfile"]))
    {
      echo "Documentation: <a href='".WebGitTools::getPDFURL($_SESSION["wareshub"]["url"]["defsset-gitprotocol"],$_SESSION["wareshub"]["url"]["defsset-githost"],$WareData["pdfdoc-url-subfile"])."'>
          <span class='glyphicon glyphicon-file'></span>&nbsp;PDF
          </a><br/>";
    }
    echo "OpenFLUID version(s): ".$this->getCompatibilityString($WareData["compat-versions"])."<br/>";
    echo "<br/>";


    if (array_key_exists("committers",$WareData))
    {
      echo sizeof($WareData["committers"])." contributors: ";
      echo $this->getContributorsString($WareData["committers"]);
    }


    echo "</div>";

    $GitURL = WebGitTools::getGitURL($_SESSION["wareshub"]["url"]["defsset-gitprotocol"],
                                     $_SESSION["wareshub"]["url"]["defsset-githost"],
                                     $WareData["git-url-subdir"],
                                     $_SESSION["login"]->getUserName());
    
    echo "<div class='col-md-6'>
          <div class='panel panel-success'>
          <div class='panel-heading'>git access</div>
            <div class='panel-body'>
              <b>URL:</b>
              <input class='' type='text' readonly='readonly' size='40' value='".$GitURL."'></input><br/>
              <br>
              <li>Read access for ".$this->getGrantedUsersString($WareData["definition"]["users-ro"])."</li>
              <li>Write access for ".$this->getGrantedUsersString($WareData["definition"]["users-rw"])."</li>
            </div>
          </div>
          </div>
          </div>";

    echo "<hr class='warepage'>";

    if (empty($this->WareBranch))
    {
      echo "<h5><i>This ".static::$SingularWareTypes[$this->WareType]." seems to be empty</i></h5>";
    }
    else
    {
      echo "<h4>Branch: ".$this->getBranchesDropdown($WareData["branches"])."</h4><br/>";

      $this->rebuildIssues();
      
      echo "
          <ul class='nav nav-tabs'>
          <li class='active'><a href='#general' data-toggle='tab'>General information</a></li>
          <li><a href='#commits' data-toggle='tab'>Commits history</a></li>          
          <li><a href='#issues' data-toggle='tab'>Issues</a></li>";
      
      echo "
          </ul>";

      echo "
          <div class='tab-content'>
          <div class='tab-pane branch-tab active' id='general'>";
      $this->printGeneralInformations();
      echo "
          </div>
          <div class='tab-pane branch-tab' id='commits'>";
      $this->printCommitsHistory();
      echo  "
          </div>
          <div class='tab-pane branch-tab' id='issues'>";
      $this->printIssues();
      echo "</div>
          </div>
          ";
    }

    echo "</div>";
  }


  // =====================================================================
  // =====================================================================



}


?>