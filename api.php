<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("generateInfoForm")) {
  loadModuleLib("forms","api");
  
  function generateInfoTableForm($infoConfig, $dtuid) {
    $nouserFields=["id","guid","groupuid",
                   "access_level","access_rule","privilegeid","workflow",
                   "edited_by","edited_on","created_by","created_on"];
    
    if($infoConfig['vmode']=="view") {
      return "";
    }
    $vmode=explode(",",$infoConfig['vmode']);
    $colsArr=explode(",",$_ENV['INFOVIEW']['cols']);
    
    if(!isset($_ENV['INFOVIEW']['form'])) {
      $_ENV['INFOVIEW']['form']=[];
      
      foreach($colsArr as $v) {
        $f=current(explode(" ",$v));
        $f=explode(".",$f);
        $f=end($f);
        if(in_array($f,$nouserFields)) continue;
        
        $k=explode(".",$v);
        $k=explode("as ",end($k));
        $k=_ling(end($k));
        
        $_ENV['INFOVIEW']['form']['fields'][$v]=[
          "label"=> $k,
        ];
        
        if(strpos(strtolower($v),"date")!==false) {
          $_ENV['INFOVIEW']['form']['fields'][$v]['type']="date";
        }
      }
//       printArray($_ENV['INFOVIEW']['form']);
      $_ENV['INFOVIEW']['form']['source']=[
        "type"=> "sql",
        "table"=> current(explode(",",$_ENV['INFOVIEW']['table'])),
        "where"=> ["md5(id)"]
      ];
      $_ENV['INFOVIEW']['form']['forcefill']=[
        "groupuid"=> "#SESS_GROUP_NAME#",
        "access_level"=> "#SESS_ACCESS_LEVEL#",
        "privilegeid"=> "#SESS_PRIVILEGE_ID#",
      ];
      
      if(isset($_ENV['INFOVIEW']['colkey'])) {
        $_ENV['INFOVIEW']['form']['forcefill'][$_ENV['INFOVIEW']['colkey']]="#REFID#";
      }
      
      $_SESSION['INFOVIEWTABLE'][$dtuid]['form']=$_ENV['INFOVIEW']['form'];
    }
    if(isset($_SESSION['INFOVIEWTABLE'][$dtuid]['form']['forcefill'])) {
      if(isset($_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['data'])) {
        foreach($_SESSION['INFOVIEWTABLE'][$dtuid]['form']['forcefill'] as $col=>$rule) {
          $rule=substr($rule,1,strlen($rule)-2);
          if(isset($_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['data'][$rule])) {
            $_SESSION['INFOVIEWTABLE'][$dtuid]['form']['forcefill'][$col]=$_SESSION['INFOVIEW'][$_ENV['FORMKEY']]['data'][$rule];
          }
        } 
      }
    }

    $html=["<tr data-refhash='{$dtuid}'>"];
    foreach($_ENV['INFOVIEW']['form']['fields'] as $key=>$f) {
      $key=current(explode(" ",$key));
      $key=explode(".",$key);
      $html[]=getInfoFieldCol(end($key), $f);
    }
    if(count($_ENV['INFOVIEW']['form']['fields'])<count($colsArr)) {
      $html[]="<td class='info-col-actions' colspan='".(count($colsArr)-count($_ENV['INFOVIEW']['form']['fields'])+1)."' width=0px>".
              "<button class='btn btn-primary' onclick='submitInfoForm(this)'><i class='fa fa-save'></i></button>".
              "</td>";
    } else {
      $html[]="</tr><tr>";
      $html[]="<td class='info-col-actions text-right' colspan='100' width=0px>".
              "<button class='btn btn-primary' onclick='submitInfoForm(this)'><i class='fa fa-save'></i> "._ling("Save")."</button>".
              "</td>";
      $html[]="</tr>";
    }
    $html[]="</tr>";
    return implode("",$html);
  }
  
  function getInfoFieldCol($fkey, $fieldConfig) {
    if(!isset($fieldConfig['label'])) $fieldConfig['label']=toTitle($fkey);
    $fieldConfig=array_merge([
          "fieldkey"=>$fkey,
          "required"=> false,
          "type"=>"text",
          "width"=>"auto",
          "span"=>1,
          "placeholder"=>toTitle($fieldConfig['label']),
          "tooltip"=>"",
        ],$fieldConfig);
    if($fieldConfig['required']) {
      $fieldConfig['placeholder'].=" *";
    }
    
    $html=getFormField($fieldConfig,"");
//     $html="<input type='text' name='{$fkey}' class='form-control' data-value='' placeholder='{$fieldConfig['label']}' />";
    return "<td class='infotable-col infotable-col-{$fkey} {$fieldConfig['type']}' width='{$fieldConfig['width']}' colspan='{$fieldConfig['span']}' title='{$fieldConfig['tooltip']}'>".$html."</td>";
  }
}

?>