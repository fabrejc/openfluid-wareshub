<?php

  echo "The web access system for wareshub is under construction<br/>";
  
  echo "<br/>";
  
  $WHSytemRootPath = realpath(__DIR__."/..");
  $ActiveDefsRootPath = realpath(dirname($_SERVER["SCRIPT_FILENAME"])."/..");
  
  echo "<ul>";
  echo "<li>WaresHub system root path: $WHSytemRootPath </li>";
  echo "<li>Active WaresHub  root path: $ActiveDefsRootPath </li>";
  echo "</ul>";

?>