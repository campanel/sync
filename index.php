
<!DOCTYPE html>
<html>
    <head>
      <title>Sync Priax to OpMon</title>
      <style type="text/css">
      html{
        font-family:  Arial, 'Helvetica Neue', Helvetica, sans-serif; 
        font-size: 12px;

      }
        input:hover, textarea:hover,
        input:focus, textarea:focus {
            background-color:white;
        }
        h2{
            font-size:14px;
            width: 100%;
            text-align: center;
        }
        

        #button-blue{
             width: 180px;
             border: #3498db solid 2px;
             cursor:pointer;
             background-color: #3498db;
             color:white;
             font-size:12px;
             padding-top:10px;
             padding-bottom:10px;
             -webkit-transition: all 0.3s;
             -moz-transition: all 0.3s;
             transition: all 0.3s;
             margin-top:-4px;
             font-weight:600;
             margin-left:4px;
         }

        #button-blue:hover{
            background-color: white;
            color: #0493bd;
            border: #3498db solid 2px;
}
 /* Base styles for this pen */
*,
*:before,
*:after {
  -webkit-box-sizing: border-box;
  -moz-box-sizing:    border-box;
  box-sizing:         border-box;
}

/**
 * Tooltips!
 */

/* Base styles for the element that has a tooltip */
[data-tooltip],
.tooltip {
  position: relative;
  cursor: pointer;
}

/* Base styles for the entire tooltip */
[data-tooltip]:before,
[data-tooltip]:after,
.tooltip:before,
.tooltip:after {
  position: absolute;
  visibility: hidden;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=0);
  opacity: 0;
border-radius:         3px;
  -webkit-transform: translate3d(0, 0, 0);
  -moz-transform:    translate3d(0, 0, 0);
  transform:         translate3d(0, 0, 0);
  pointer-events: none;
}

/* Show the entire tooltip on hover and focus */
[data-tooltip]:hover:before,
[data-tooltip]:hover:after,
[data-tooltip]:focus:before,
[data-tooltip]:focus:after,
.tooltip:hover:before,
.tooltip:hover:after,
.tooltip:focus:before,
.tooltip:focus:after {
  visibility: visible;
  transition-property: opacity;
transition-duration: 1s;
transition-timing-function: ease;
transition-delay: 1s;
  -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100);
  opacity: 1;
}

/* Base styles for the tooltip's directional arrow */
.tooltip:before,
[data-tooltip]:before {
  z-index: 1001;
  border: 6px solid transparent;
  background: transparent;
  content: "";
}

/* Base styles for the tooltip's content area */
.tooltip:after,
[data-tooltip]:after {
  z-index: 1000;
  padding: 18px;
  width: 320px;
  background-color: #000;
  background-color: hsla(0, 0%, 20%, 0.9);
  color: #fff;
  content: attr(data-tooltip);
  font-size: 12px;
  line-height: 1.2;
}

/* Directions */

/* Top (default) */
[data-tooltip]:before,
[data-tooltip]:after,
.tooltip:before,
.tooltip:after,
.tooltip-top:before,
.tooltip-top:after {
  bottom: 100%;
  left: 50%;
}

[data-tooltip]:before,
.tooltip:before,
.tooltip-top:before {
  margin-left: -6px;
  margin-bottom: -12px;
  border-top-color: #000;
  border-top-color: hsla(0, 0%, 20%, 0.9);
}

/* Horizontally align top/bottom tooltips */
[data-tooltip]:after,
.tooltip:after,
.tooltip-top:after {
  margin-left: -80px;
}

[data-tooltip]:hover:before,
[data-tooltip]:hover:after,
[data-tooltip]:focus:before,
[data-tooltip]:focus:after,
.tooltip:hover:before,
.tooltip:hover:after,
.tooltip:focus:before,
.tooltip:focus:after,
.tooltip-top:hover:before,
.tooltip-top:hover:after,
.tooltip-top:focus:before,
.tooltip-top:focus:after {
  -webkit-transform: translateY(-12px);
  -moz-transform:    translateY(-12px);
  transform:         translateY(-12px); 
}

/* Left */
.tooltip-left:before,
.tooltip-left:after {
  right: 100%;
  bottom: 50%;
  left: auto;
}

.tooltip-left:before {
  margin-left: 0;
  margin-right: -12px;
  margin-bottom: 0;
  border-top-color: transparent;
  border-left-color: #000;
  border-left-color: hsla(0, 0%, 20%, 0.9);
}

.tooltip-left:hover:before,
.tooltip-left:hover:after,
.tooltip-left:focus:before,
.tooltip-left:focus:after {
  -webkit-transform: translateX(-12px);
  -moz-transform:    translateX(-12px);
  transform:         translateX(-12px); 
}

/* Bottom */
.tooltip-bottom:before,
.tooltip-bottom:after {
  top: 100%;
  bottom: auto;
  left: 50%;
}

.tooltip-bottom:before {
  margin-top: -12px;
  margin-bottom: 0;
  border-top-color: transparent;
  border-bottom-color: #000;
  border-bottom-color: hsla(0, 0%, 20%, 0.9);
}

.tooltip-bottom:hover:before,
.tooltip-bottom:hover:after,
.tooltip-bottom:focus:before,
.tooltip-bottom:focus:after {
  -webkit-transform: translateY(12px);
  -moz-transform:    translateY(12px);
  transform:         translateY(12px); 
}

/* Right */
.tooltip-right:before,
.tooltip-right:after {
  bottom: 50%;
  left: 100%;
}

.tooltip-right:before {
  margin-bottom: 0;
  margin-left: -12px;
  border-top-color: transparent;
  border-right-color: #000;
  border-right-color: hsla(0, 0%, 20%, 0.9);
}

.tooltip-right:hover:before,
.tooltip-right:hover:after,
.tooltip-right:focus:before,
.tooltip-right:focus:after {
  -webkit-transform: translateX(12px);
  -moz-transform:    translateX(12px);
  transform:         translateX(12px); 
}

/* Move directional arrows down a bit for left/right tooltips */
.tooltip-left:before,
.tooltip-right:before {
  top: 3px;
}

/* Vertically center tooltip content for left/right tooltips */
.tooltip-left:after,
.tooltip-right:after {
  margin-left: 0;
  margin-bottom: -16px;
}

.blink { text-decoration: blink; }

        </style>

        
    </head>
    <body>
    
<?php


include "globals.php";
include "config-inc.php";
require_once(dirname(__FILE__)."/BpNOC/BpSyncDB.php");

if (SINCACTIVE != "YES"){
  ?> <meta http-equiv="refresh" content="0; url=begin.php" /> <?php
  exit;
}


$task = $_POST['task'];  ?> 

 <h2>Sync ICs/AICs/Catalogs - Priax to OpMon - <?php print VERSION ?></h2>
<h4>Hosts: <? 
  $lic_hosts = BmApiOpmon::license_hosts(); 
  list($licHostDisponiveis, $licHostAdicionados) = explode("/",$lic_hosts);
  $percUsoLic = ($licHostAdicionados * 100) / $licHostDisponiveis;
  $font_color = '<font >';
  if( $percUsoLic >= 98 ){
    $font_color = '<font color="red" size="6">';
  }

  echo $font_color.$licHostDisponiveis." / ".$licHostAdicionados."</font>"
  

?>      |   Services: <? echo $lic_services = BmApiOpmon::license_services(); ?></h4>
<h6>"O sync do catalogo não satisfaz as necessidades deste cliente NETCENTRICS(desabilitado em codigo BpOpMonSyncCatalogs.php )"</h6>
<?php
if(PRIAXURL == NULL){
  print "<h1>Deve-se definir a URL Priax no arquivo config-inc.php</h1>";
  exit;
}else{
  print '<p><span class="tooltip-bottom" data-tooltip="/usr/local/opmon/share/priax/sync/config-inc.php">Connect: '.PRIAXURL.'</span></p><br/>';

}

//check_run_sync();

    ?>
    <form method="post" action=""> 

        <span class="tooltip-bottom" data-tooltip="Incremental Sync only ICS, AICs">
            <input id="button-blue" type="submit" name="task" value="Incremental Sync only ICs AICs">
        </span>
        <span class="tooltip-bottom" data-tooltip="Incremental Sync - ICS, AICs, Catalogs">
            <input id="button-blue" type="submit" name="task" value="Incremental Sync">
        </span>
        <span class="tooltip-bottom"
              data-tooltip="Cria, edita, apaga ICs, AICs e Catalogos conforme configurado no Priax para o OpMon">
            <input id="button-blue" type="submit" name="task" value="Sync Full">
        </span>
        <span class="tooltip-bottom" data-tooltip="Limpa esta página">
            <input id="button-blue" type="submit" name="task" value="Clear">
        </span>
        <span class="tooltip-bottom" data-tooltip="Errors">
            <input id="button-blue" type="submit" name="task" value="Errors">
        </span>
        <!--<span class="tooltip-bottom" data-tooltip="Alertas">
            <input id="button-blue" type="submit" name="task" value="Alertas">
        </span>
        -->
        <span class="tooltip-bottom" data-tooltip="Logs">
            <input id="button-blue" type="submit" name="task" value="Logs">
        </span>
        <!--
        <span class="tooltip-bottom" data-tooltip="Deletar todos os Logs, não tem volta!">
            <input id="button-blue" type="submit" name="task" value="Deletar todos os Logs">
        </span>
        -->
    </form>

    <br/>
    <hr/>

<?php
$pagina = $_GET["p"];
switch( $task ) {
    case 'Sync Full':
        check_run_sync();
      sync();  
        break;
    case 'Incremental Sync only ICs AICs':
        check_run_sync();
        sync_incremental_only_ics_aics();
        break;
    case 'Incremental Sync':
        check_run_sync();
        sync_incremental();
        break;
    case 'Errors':
        $result = BpSyncDB::get_logs_error();
        array_to_table($result);
        break;
    case 'Alertas':
        $result = BpSyncDB::get_logs_alert();
        array_to_table($result);
        break;
    case 'Logs':
        logs($pagina);
        break;
    case 'Deletar todos os Logs':
        deleteAllLogs();
        break;
    case 'Clear':
        doRefresh();
        break;
    default:
        logs($pagina);
        break;
} 

function sync() { 
    $output = shell_exec('/usr/bin/php -q start-priax-sync-full.php');
    $output = outputCodeToWeb($output);
    echo $output;
}

function sync_incremental_only_ics_aics() {
    $output = shell_exec('/usr/bin/php -q start-priax-sync-incrementais-only-ics-aics.php');
    $output = outputCodeToWeb($output);
    echo $output;
}

function sync_incremental() {
    $output = shell_exec('/usr/bin/php -q start-priax-sync-incrementais.php');
    $output = outputCodeToWeb($output);
    echo $output;
}
function logs($pagina) {
    $count_logs = BpSyncDB::get_count_logs();
    echo "<h2>Página: ".$pagina."</h2>";
    //echo $count_logs;
    $qnt = 100;
    $inicio = ($pagina*$qnt) - $qnt;


    // Gera outra variável, desta vez com o número de páginas que será precisa.
    // O comando ceil() arredonda "para cima" o valor
    $pags = ceil($count_logs/$qnt);
    // Número máximos de botões de paginação
    $max_links = 3;
    // Exibe o primeiro link "primeira página", que não entra na contagem acima(3)
    echo "<a href='index.php?p=1' target='_self'>primeira pagina</a> ";
    // Cria um for() para exibir os 3 links antes da página atual
    for($i = $pagina-$max_links; $i <= $pagina-1; $i++) {
    // Se o número da página for menor ou igual a zero, não faz nada // (afinal, não existe página 0, -1, -2..)
        if($i <=0) { //faz nada // Se estiver tudo OK, cria o link para outra página
        }else{
            echo "<a href='index.php?p=".$i."' target='_self'>".$i."</a> ";
        }
    }
    // Exibe a página atual, sem link, apenas o número
    echo $pagina." ";
    // Cria outro for(), desta vez para exibir 3 links após a página atual
    for($i = $pagina+1; $i <= $pagina+$max_links; $i++) {
    // Verifica se a página atual é maior do que a última página. Se for, não faz nada.
        if($i > $pags) {
        //faz nada
        } // Se tiver tudo Ok gera os links.
        else {
            echo "<a href='index.php?p=".$i."' target='_self'>".$i."</a> ";
        }
    }
    // Exibe o link "última página"
    echo "<a href='index.php?p=".$pags."' target='_self'>ultima pagina</a> ";


    $result = BpSyncDB::get_logs_pag($inicio, $qnt);
    array_to_table($result);
}


function array_to_table($result)
{
    if(count($result) == 0){
        echo '<h3> Sem dados...</h3>';
        exit;
    }else{
        echo '<h3> Registros:'.count($result).'</h3>';
    }

    $result = arrayCodeToWeb($result);
    $output = '<table>
      <thead>
        <tr>
          <th></th>
    </tr>
    </thead>
    <tbody>';
    foreach ($result as $row) {
        unset($row['id']);
        unset($row['entry_level']);
        $output .= "<tr><td>";
        $row = implode(' - ', $row);
        $output .= $row . '</td></tr>';
    }
    $output .='</tbody></table>';

    echo $output;
}


function doRefresh() { 
    echo ""; 
}

function deleteAllLogs() {
    //BpSyncDB::logs_delete();
    echo '<h3> Todos os logs foram deletados!</h3>';
    exit;
}

function check_run_sync() {
    exec('ps axu | grep start-priax-sync-full.php | grep -v grep',$out, $return_full);
    exec('ps axu | grep start-priax-sync-incrementais.php | grep -v grep',$out2, $return_incremental);
    exec('ps axu | grep start-priax-sync-incrementais-only-ics-aics.php | grep -v grep',$out3, $return_incremental_only_ics_aics);
    if( $return_full == 0 || $return_incremental == 0 || $return_incremental_only_ics_aics == 0){
        echo '<h1>Sincronismo em andamento... </h1>';
        echo '<h2>Tente mais tarde.</h2>';
        echo "<meta HTTP-EQUIV='refresh' CONTENT='1;URL=index.php'>";
        exit;
    }
}

function outputCodeToWeb($output) { 
    $output = str_replace("[32mOK[0m", '<span style="color:green">OK</span>', $output);
    $output = str_replace("[33mWARNING[0m", '<span style="color:orange">WARNING</span>', $output);
    $output = str_replace("[31mERROR[0m", '<span style="color:red">ERROR</span>', $output);
    $output = str_replace("[32mINFO[0m", '<span style="color:grey">INFO</span>', $output);
    $output = str_replace("[37mINFO[0m", '<span style="color:grey">INFO</span>', $output);
    $output = str_replace("[35mQUERY[0m", '<span style="color:brown">QUERY</span>', $output);
    $output = str_replace('[', '<br/>[', $output);
    return $output;
}

function arrayCodeToWeb($arr) {
    foreach($arr as $key => $line ) {



        switch ($line['entry_level']) {
            case 0:
                $arr[$key]['mensage'] = '<span style="color:green">OK</span> ' . $line['mensage'];
                break;
            case 1:
                $arr[$key]['mensage'] = '<span style="color:orange">WARNING</span> ' . $line['mensage'];
                break;
            case 2:
                $arr[$key]['mensage'] = '<span style="color:red">ERROR</span> ' . $line['mensage'];
                break;
            case 3:
                $arr[$key]['mensage'] = '<span style="color:grey">INFO</span> ' . $line['mensage'];
                break;
            case 4:
                $arr[$key]['mensage'] = '<span style="color:brown">QUERY</span> ' . $line['mensage'];
                break;

        }

    }
    return $arr;
}

?>

</body>
</html>
