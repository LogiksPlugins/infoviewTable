<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$colsArr=explode(",",$_ENV['INFOVIEW']['cols']);

$_ENV['INFOVIEW']['columns']=$colsArr;

if(!isset($_ENV['INFOVIEW']['hidden'])) $_ENV['INFOVIEW']['hidden']=[];

$_SESSION['INFOVIEWTABLE'][$dtuid]=$_ENV['INFOVIEW'];
// printArray($_ENV['INFOVIEW']);
?>
<div id='infoviewTable_<?=$dtuid?>' class="row infoTableView infoTableGrid infoOnOpen" 
      data-dcode='<?=$dcode?>' data-dtuid='<?=$dtuid?>' data-page=0 data-limit=20 data-ui="grid">
    <div class='col-md-12 table-responsive infoview-table'>
        <?php
          if(isset($_ENV['INFOVIEW']['actions']) && is_array($_ENV['INFOVIEW']['actions'])) {
            echo "<div class='form-actions text-right'>";
            echo getInfoViewActions($_ENV['INFOVIEW']['actions']);
            echo "</div>";
          }
        ?>
        <table class="table table-bordered table-pagination">
            <thead>
                <tr>
                    <?php
                    foreach($colsArr as $n=>$v) {
                          $xtras="";
                          $clz="";
                      
                          $k=explode(".",$v);
                          $k=explode(" as ",end($k));
                          $k1=_ling(end($k));
                          if($n==0) {
                            $xtras="width=100px";
                            continue;
                          } else {
                            
                          }
                          
                          $nm=$k[0];
                          if(in_array($nm, $_ENV['INFOVIEW']['hidden'])) $clz.="hidden d-none noshow";
                        ?>
                        <th name='<?=$nm?>' class='<?=$clz?>' <?=$xtras?> ><?=toTitle($k1)?></th>
                        <?php
                    }
                    ?>
                    <th class='actions'>-</th>
                </tr>
                <th class='filters hidden'>
                    
                </th>
            </thead>
            <tbody>
              
            </tbody>
            <tfoot class='info-pagination'>
            </tfoot>
        </table>
        <table class="table table-condensed">
          <tfoot class='info-form'>
              <?php
                if(checkUserRoles($_ENV['INFOVIEW']['security']['module'],$_ENV['INFOVIEW']['security']['activity'],"CREATE")) {
                  echo generateInfoTableForm($_ENV['INFOVIEW'],$dtuid);
                }
              ?>
          </tfoot>
        </table>
        <?php
          if(isset($_ENV['INFOVIEW']['footactions']) && is_array($_ENV['INFOVIEW']['footactions'])) {
            echo "<div class='form-actions text-right'>";
            echo getInfoViewActions($_ENV['INFOVIEW']['footactions']);
            echo "</div>";
          }
        ?>
    </div>
</div>
