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
  width: 220px;
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
    
        </style>
    </head>
    <body>
    <?php include "config-inc.php"; ?>
    
<h2>Sync Priax to OpMon - <?php print VERSION ?></h2>
<?php
if(PRIAXURL == NULL){
  print "<h1>Deve-se definir a URL Priax no arquivo config-inc.php</h1>";
  exit;
}

?>
    <form method="post" action=""> 
<?php 

if (SINCACTIVE == "YES"){

?><a href="index.php">
  <input id="button-blue" type="text" name="task" value="            Já configurado!">
  </a>
  </form></body></html>
<?php
exit;
}

$task = $_POST['task'];  ?> 

        <span class="tooltip-bottom" data-tooltip="Configuração inicial do OpMon para sincronizar com o Priax,: Templates, comando">
            <input id="button-blue" type="submit" name="task" value="Iniciar">
        </span>
    </form> 

<br/>
<hr/>

<?php 

switch( $task ) {
    case 'Iniciar':
    	configureopmon();  
        break; 

} 


function configureopmon() {
  $output = shell_exec('/usr/bin/php -q configureOpmon.php');
  echo $output;
      
  $path = 'config-inc.php';

  $rs = file( $path );
  $lines = array();

  foreach ($rs as $key => $value) {
    //print "key $key - ".$value."<br \>";
    if( preg_match( '/SINCACTIVE/' , $value) ){
      $lines[] = 'define("SINCACTIVE","YES");';
    }else{
      $lines[] = trim($value);
    }
  }
  
  file_put_contents( $path , implode( "\n", $lines ) );
 ?>
 <br />
 <br />
 <br />
 <br />
 
 <a href="index.php">
  <input id="button-blue" type="text" name="task" value="              Configurado!">
  </a>
  </form></body></html>
<?php
} 

?>

</body>
</html>