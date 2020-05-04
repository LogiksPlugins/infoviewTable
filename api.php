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
        "guid"=> "#SESS_GUID#",
        "groupuid"=> "#SESS_GROUP_NAME#",
        // "access_level"=> "#SESS_ACCESS_LEVEL#",
        // "privilegeid"=> "#SESS_PRIVILEGE_ID#",
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

  function getInfoFilterField($fkey, $fieldConfig) {
    if(!isset($fieldConfig['type'])) $fieldConfig['type']="text";
    switch($fieldConfig['type']) {
      case 'dataMethod': case 'dataSelector': case 'dataSelectorFromUniques': case 'dataSelectorFromTable':
      case 'dropdown': case 'select':
      if(!isset($fieldConfig['options'])) $fieldConfig['options']=[];

      if(!isset($fieldConfig['no-option'])) {
        $fieldConfig['no-option']="Select ".toTitle($fkey);
      }
      $noOption=_ling($fieldConfig['no-option']);

      $html = "<select class='form-control field-dropdown' name='filter[{$fkey}]'>";
      
      if(is_array($fieldConfig['options'])) {
        if(!array_key_exists("", $fieldConfig['options']) || (isset($fieldConfig['options']['']) && $fieldConfig['options']['']===true)) {
          $html.="<option value=''>{$noOption}</option>";
        }
        foreach ($fieldConfig['options'] as $key => $value) {
          if($key==null || strlen($key)<=0) continue;
          $html.="<option value='{$key}'>{$value}</option>";
        }
      }
      if(isset($fieldConfig['dbkey'])) $dkey1=$fieldConfig['dbkey'];
      else $dkey1="app";
      
      if(isset($fieldConfig['search']) && $fieldConfig['search']==true) {
        
      } else {
        $html.=generateSelectOptions($fieldConfig,"",$dkey1);
      }

      $html .= "</select>";
      return $html;
      break;

      default:
        return "<input type='text' class='form-control' name='search[{$fkey}]' />";
    }
    return "";
  }

  function getInfoViewTableActions($actions=[]) {
    $html="";
    foreach ($actions as $key => $button) {
      if(isset($button['policy']) && strlen($button['policy'])>0) {
        $allow=checkUserPolicy($button['policy']);
        if(!$allow) continue;
      }
      if(!isset($button['class'])) $button['class']="";

      if(isset($button['label'])) $label=$button['label'];
      else $label="";

      if(isset($button['title'])) $title=$button['title'];
      else $title="";

      if(isset($button['icon']))  $icon=$button['icon'];
      else $icon="";
      
      if(strlen($icon)>0 && $icon == strip_tags($icon)) {
        $icon="<i class='{$icon}'></i> ";
      }

      if(!isset($button['type'])) $button['type']="button";

      $key = _replace($key);

      $html .= "<span class='{$button['class']} mouseAction' cmd='{$key}' title='{$title}'>{$icon}{$label}</span>";
    }
    return $html;
  }

  function getInfoViewSidebar($infoConfig) {
    if(!isset($infoConfig['table']) || !isset($infoConfig['cols']) || !isset($infoConfig['colkey'])) return "";

    if(!isset($infoConfig['type'])) $infoConfig['type'] = "list";

    $html = [];
    
    $tbl1=current(explode(",",$infoConfig['table']));
    $sql=_db()->_selectQ($infoConfig['table'],$infoConfig['cols'],["{$tbl1}.blocked"=>'false']);
    if(is_array($infoConfig['where'])) {
      foreach($infoConfig['where'] as $a=>$b) {
        if($b=="RAW") {
          unset($infoConfig['where'][$a]);
          $infoConfig['where'][_replace($a)]=$b;
        } else {
          $infoConfig['where'][$a]=_replace($b);
        }
      }
      $sql->_where($infoConfig['where']);
    } else {
      $sql->_whereRAW(_replace($infoConfig['where']));
    }

    if(isset($_POST) && count($_POST)>0) {
      foreach($_POST as $a=>$b) {
        $sql->_where(["{$tbl1}.{$a}"=>[clean($b),"LIKE"]]);
      }
    }

    if(isset($infoConfig['orderby']) && strlen($infoConfig['orderby'])>0) {
      $sql->_orderBy($infoConfig['orderby']);
    } elseif(isset($infoConfig['orderBy']) && strlen($infoConfig['orderBy'])>0) {
      $sql->_orderBy($infoConfig['orderBy']);
    }

    if(isset($infoConfig['groupby']) && strlen($infoConfig['groupby'])>0) {
      $sql->_groupBy($infoConfig['groupby']);
    } elseif(isset($infoConfig['groupBy']) && strlen($infoConfig['groupBy'])>0) {
      $sql->_groupBy($infoConfig['groupBy']);
    }

    $sql->_limit(100);
    $data=$sql->_GET();
    
    if(!$data) return "";

    switch ($infoConfig['type']) {
      case 'list':
        $html[] = "<ul class='list-group' data-colkey='{$infoConfig['colkey']}'>";
        foreach ($data as $row) {
          if(isset($row['title']) && isset($row['value'])) {
            $html[] = "<li class='list-group-item' data-refid='{$row['value']}' data-colkey='{$infoConfig['colkey']}' cmd='filterInfoviewDataOnSidebar'>{$row['title']}</li>";
          }
        }
        $html[] = "</ul>";
        break;
      case 'accordion':
        
        // break;
      case 'tree':
        
        // break;
      default:
        $html[] = "<p>Not supported yet</p>";
        break;
    }

    return implode("", $html);
  }
}

?>