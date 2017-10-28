<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//To be used in infoview
//$slug=_slug("module/uitype/subcat");

if(isset($_ENV['INFOVIEW']) && isset($_ENV['INFOVIEW']['config']) && isset($_ENV['INFOVIEW-REFHASH'])) {
  
  if(isset($_ENV['INFOVIEW']['vmode'])) $_ENV['INFOVIEW']['config']['vmode']=$_ENV['INFOVIEW']['vmode'];
  else $_ENV['INFOVIEW']['config']['vmode']="view";
  
  $_ENV['INFOVIEW']=$_ENV['INFOVIEW']['config'];
  if(!isset($_ENV['INFOVIEW']['type'])) $_ENV['INFOVIEW']['type']="sql";
  if(!isset($_ENV['INFOVIEW']['uimode'])) $_ENV['INFOVIEW']['uimode']="grid";
  
  $duidArr=array_map(function($a) {
    if(is_array($a)) return "";
    else return $a;
  },$_ENV['INFOVIEW']);
  
  $dtuid=md5(microtime().implode(".",$duidArr));
  $dcode=$_ENV['INFOVIEW-REFHASH'];
  
  switch(strtolower($_ENV['INFOVIEW']['type'])) {
    case "sql":
      $f=__DIR__."/ui/sql_{$_ENV['INFOVIEW']['uimode']}.php";
      if(file_exists($f) && isset($_ENV['INFOVIEW']['table']) && isset($_ENV['INFOVIEW']['cols']) && isset($_ENV['INFOVIEW']['where'])) {
        include $f;
      } else {
        echo "<h1 align=center>Sorry, defination error.</h1>";
      }
      break;
    case "php":
      include __DIR__."/ui/php.php";
      break;
    case "file":
      if(file_exists(APPROOT.$_ENV['INFOVIEW']['file'])) {
        include APPROOT.$_ENV['INFOVIEW']['file'];
      } else {
        echo "<h1 align=center>Sorry, source file is not found.</h1>";
      }
      break;
    default:
      echo "<h1 align=center>Sorry, configuration is not supported.</h1>";
  }
} else {
  echo "<h1 align=center>Sorry, configuration is not correct.</h1>";
}
if(!isset($_ENV['INFOVIEWLOADED'])) {
  $_ENV['INFOVIEWLOADED']=true;
  
  echo _css("infoviewTable");
  echo _js("infoviewTable");
}
?>
