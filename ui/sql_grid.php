<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$_SESSION['INFOVIEWTABLE'][$dtuid]=$_ENV['INFOVIEW'];

$colsArr=explode(",",$_ENV['INFOVIEW']['cols']);
?>
<div id='infoviewTable_<?=$dtuid?>' class="row infoTableView infoTableGrid infoOnOpen" 
      data-dcode='<?=$dcode?>' data-dtuid='<?=$dtuid?>' data-page=0 data-limit=20 data-ui="grid">
    <div class='col-md-12 table-responsive'>
        <?php
          if(isset($_ENV['INFOVIEW']['buttons']) && is_array($_ENV['INFOVIEW']['buttons'])) {
            echo "<div class='form-actions text-right'>";
            echo getInfoViewActions($_ENV['INFOVIEW']['buttons']);
            echo "</div>";
          }
        ?>
        <table class="table table-bordered table-pagination">
            <thead>
                <tr>
                    <?php
                    foreach($colsArr as $v){
                          $k=explode(".",$v);
                          $k=explode("as",end($k));
                          $k=_ling(end($k));
                        ?>
                        <th name='<?=$v?>'><?=toTitle($k)?></th>
                        <?php
                    }
                    ?>
                </tr>
                <th class='filters hidden'>
                    
                </th>
            </thead>
            <tbody>
              
            </tbody>
            <tfoot class='info-form'>
            </tfoot>
            <tfoot class='info-pagination'>
            </tfoot>
        </table>
    </div>
</div>
