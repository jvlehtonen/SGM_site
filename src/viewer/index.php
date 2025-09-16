<?php
header('Content-Type: text/html; charset=utf-8');

function getstrlist(): array {
    $arr = array();
    include("../sql/connect.php");
    $sql = "SELECT variant, structure FROM for_datatable WHERE 0 < structure ORDER BY resnum, variant";
    $stmt = $conn->stmt_init();
    $stmt->prepare($sql);
    $stmt->execute();
    if ( $result = $stmt->get_result() ) {
        while($row = $result->fetch_assoc() ) {
            $arr[ $row['variant'] ] = $row['structure'];
        }
    }
    $conn->close();
    return $arr;
}

function printOptions($text, $seek): void {
        if ( $text ) {
            echo "<option value='WT'>WT</option>\n";
        } else {
            echo "<option value='WT'></option>\n";
        }

    foreach ($seek as $x => $x_value) {
        if ( $text ) {
            echo "<option value='$x" . "'>$x" . "</option>\n";
        } else {
            echo "<option value='$x" . "'></option>\n";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <!--
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer | SynGAP Server</title>
    <style>
     * {
     margin: 0;
     padding: 0;
     box-sizing: border-box;
     }
     nav {
         margin-bottom: 5px;
     }
      #app {
	  position: relative;
                height: 800px;
	  border: 1px solid #ccc;
	  float: left;
      }

      #details {
	  position: static;
	  margin: 0 0 0 10px;
      height: 800px;
	  float: left;
      }

      #details > iframe {
	  margin: 0;
	  padding: 0;
	  width: 100%;
	  height:100%;
      }

      #controls {
	  margin: 0 10px 0 10px;
	  float: left;
      }

      #controls > button {
	    display: block;
	    margin: 10px 5px 10px 25px;
	    width: 75%;
	    text-align: left;
      }

      #controls > hr {
        /*margin: 0 5px 0 5px;*/
        margin-top: 5px;
        margin-bottom: 5px;
      }

      #controls > input, #controls > div > select {
	    width: 95%;
	    margin: 5px;
	    display: block;
      }
      #controls > button, #controls > div > select {
        padding-left: 5px;
      }

      /* Tooltip container */
      .tooltip {
	  position: relative;
	  display: inline-block;
      }

      /* Tooltip text */
      .tooltip .tooltiptext, .tooltip .tooltiptextwide {
          visibility: hidden;
          background-color: #555;
          color: #fff;
          border-radius: 6px;

          /* Position the tooltip text */
          position: absolute;
          z-index: 1;
          top: -5px;
          left: 105%;

          /* Fade in tooltip */
          opacity: 0;
          transition: opacity 0.3s;
      }
      .tooltip .tooltiptext {
          width: 200px;
          text-align: center;
          padding: 5px 5px;
      }
      .tooltip .tooltiptextwide {
          width: 600px;
          text-align: justify;
          text-justify: inter-word;
          padding: 5px 5px;
      }
      /* Show the tooltip text when you mouse over the tooltip container */
      .tooltip:hover .tooltiptext, .tooltip:hover .tooltiptextwide {
          visibility: visible;
          opacity: 1;
      }

      .left {
	  float: left;
	  width: 200px;
      }

      .main {
	  float: left;
	  width: 800px;
      }

      .right {
	  float: left;
	  width: 820px;
      }

      /* Use a media query to add a break point at 1820px: */
      @media screen and (max-width: 1820px) {
	  .main, .right {
	      width: 100%;
	  }
      }
    </style>
    <link rel="icon" type="image/png" href="../misc/titles/SynGAP.png" sizes="32x32" />
    <link rel="stylesheet" type="text/css" href="molstar.css" />
    <link rel="stylesheet" href="../styles/fa.css" />
    <link rel="stylesheet" href="../styles/overlay.css" />
    <link rel="stylesheet" href="../styles/topnav.css" />
    <link rel="stylesheet" href="../styles/infotooltip.css" />
    <script type="text/javascript" src="index.js"></script>
    <script type="text/javascript" src="functions.js"></script>
    <script type="text/javascript" src="../sites/w3.js"></script>
    <script type="text/javascript" src="../sites/overlay.js"></script>
  </head>
  <body>
      <div w3-include-html="/sites/overlay.html"></div>
      <a href="/index.html"><span><img src="/misc/titles/sgm_viewer.png"
      alt="SynGap Missense Server" style="padding-top:1em; padding-left:1em;"/></span></a>

      <nav style="margin-bottom:15px">
       <div style="display:flex;">
          <div class="topnav fa-icon" w3-include-html="/sites/hamburger.html"></div>
          <script>w3.includeHTML();</script>
          <a class="fa-icon" href="/index.html"><i class='fa fa-home' style='font-size:36px;color:#00B4CC;'></i></a>
          <div class="fa-icon">
            <a href="/table/index.php"><i class='fa fa-table' style='font-size:36px;color:#00B4CC;'></i></a>
          </div>
          <div class="infotooltip fa-icon">
            <a href="/table/metrics.html"><i class='fa fa-info-circle' style='font-size:36px;color:#00B4CC;'></i></a>
            <span class="infotooltiptext w200 myjust">
              Description of table metrics (columns)
            </span>
          </div>
          <div class="infotooltip fa-icon">
            <a href="https://molstar.org/viewer-docs/#mouse-controls" target="_blank"><i class='fa fa-computer-mouse' style='font-size:36px;color:#00B4CC;'></i></a>
            <span class="infotooltiptext w600 myjust">
              Mol* mouse controls in the viewer
          <hr>
          <ul style="padding-left: 15px;">
          <li><b>Rotate</b>: click the left mouse button and move. Alternatively, use the Shift button + left mouse button and drag to rotate the canvas.</li>
          <li><b>Translate</b>: click the right mouse button and move. Alternatively, use the Control button + the left mouse button and move. On a touchscreen device, use a two-finger drag.</li>
          <li><b>Zoom</b>: use the mouse wheel. On a touchpad, use a two-finger drag. On a touchscreen device, pinch two fingers.</li>
          <li><b>Center and zoom</b>: use the right mouse button to click onto the part of the structure you wish to focus on.</li>
          <li><b>Clip</b>: use the Shift button + the mouse wheel to change the clipping planes. On a touchpad, use the Shift button + a two-finger drag.</li>
          </ul>
          <hr>
          More on <a href="https://molstar.org/viewer-docs/#mouse-controls" target="_blank" style="color:#00B4CC;">Mol* page</a>
            </span>
          </div>
          <div class="infotooltip fa-icon">
            <i class='fa fa-question-circle' style='font-size:36px;color:#00B4CC;'></i>
            <span class="infotooltiptext w600 myjust">
              <p><b>3DViewer</b> can be used to visualize molecular dynamics (MD) simulation trajectory snapshots of the modelled SynGAP missense variants. The user can move between three 150 ns replica simulations (˅) and between five models (><), including the initial variant model and MD simulations snapshots for 0 ns, 50 ns, 100 ns and 150 ns.</p>
          <p style="margin-top:5px">The table section provides
          additional information for each variant, including a
          detailed description of the structural effects of the
          missense mutation, structural annotation, relevant MD
          simulation metrics (e.g., RMSD, B-factor) and data plots
          (e.g., Ramachandran, RMSD), and sequence- and physics-based
          predictions (e.g., SIFT, PolyPhen-2) and official ClinVar
          status. Currently, structural modelling is provided for
          SynGAP missense variants that have a template within the
          scope of SynGAP model (residues from 198 to 730). Color of
          table rows is based on value of SGM Consensus.</p>
            </span>
          </div>
       </div>
      </nav>
<?php
          $seekers = getstrlist();
?>
      <div class="section">
	<div id='controls' class='left'>
          <h3>Variant</h3>
          <div class="tooltip">
	    <select id='url' placeholder='url' >
	      <option value="">--Please choose an option--</option>
<?php printOptions( true, $seekers ); ?>
	    </select>
	    <span class="tooltiptext">Each variant for which SGM has computed MD has five model structures:
	      <ul>
		<li><b>1 / 5</b> Initial model</li>
		<li><b>2 / 5</b> At 0 ns</li>
		<li><b>3 / 5</b> At 50 ns</li>
		<li><b>4 / 5</b> At 100 ns</li>
		<li><b>5 / 5</b> At 150 ns</li>
	      </ul>
          Other variants have only the initial model
	    </span>
	  </div>
	  <input id="seek" placeholder="Type variant ..." list="structures">
	  <datalist id="structures">
<?php printOptions( false, $seekers ); ?>
	  </datalist>
          <h4>Simulation</h4>
          <div class="tooltip">
	    <select id='simulation' placeholder='simulation' >
              <option value='1' selected>1</option>
              <option value='2'>2</option>
              <option value='3'>3</option>
            </select>
	    <span class="tooltiptext">Dynamic changes of each variant
	      has been simulated three times. The initial model for
	      variant is same in each simulation.</span>
	  </div>
	</div>

	  <div id="app" class='main'></div>

	  <div id='details' class='right'>
            <iframe id='data' src=""></iframe>
	  </div>
      </div>

      <script>
        let dict = new Map();
        dict.set("WT", 2);
<?php
foreach ($seekers as $v => $s) {
    echo "dict.set(\"".$v."\", ".$s.");";
}
?>
        var pdbId = '', simulation= '1';
        var request = findGetParameter('q');
        if ( request != null && request != '' ) {
            var variants = document.getElementById("structures");
            var i;
            for (i = 0; i < variants.options.length; i++) {
                if ( variants.options[i].value == request ) {
                    pdbId = request;
                    break;
                }
            }
        }
        var format = 'pdb';
        var resnum = '1';
        $('url').value = pdbId;
        $('url').onchange = function (e) {
            pdbId = e.target.value;
            $('seek').value = pdbId;
            $('data').src = "";
            if ( pdbId != "" )
                resnum = pdbId.substring(1, 4);
            if ( pdbId != "" )
                loadAsymUnit(pdbId, format, dict, simulation);
        }
        $('seek').value = pdbId;
        $('seek').onchange = function (e) {
            pdbId = e.target.value;
            $('url').value = pdbId;
            $('data').src = "";
            if ( pdbId != "" )
                resnum = pdbId.substring(1, 4);
            if ( pdbId != "" )
                loadAsymUnit(pdbId, format, dict, simulation);
        }
        $('simulation').value = simulation;
        $('simulation').onchange = function (e) {
            simulation = e.target.value;
            if ( pdbId != "" )
                resnum = pdbId.substring(1, 4);
            if ( pdbId != "" )
                loadAsymUnit(pdbId, format, dict, simulation);
        }
        BasicMolStarWrapper.init('app' /** or document.getElementById('app') */).then(
            () => {
                BasicMolStarWrapper.setBackground(0xffffff);
                BasicMolStarWrapper.setClipping();
                if ( pdbId != '' ) {
                    loadAsymUnit(pdbId, format, dict, simulation);
                }
            }
        );

        addSeparator();
        addTippedControl('Add membrane', () => loadMembrane( format ),
        'Shows approximate location of the membrane (blue = inner leaflet; red = outer leaflet)');
        addTippedControl('Add RAS-GTPase', () => loadRAS( format ),
        'Loads RAS protein (location is approximate) to the view');
        addTippedControl('Add WT', () => loadWT( format ),
        'Loads WT structure (0 ns frame) to the view');
        addSeparator();
        addTippedControl('Refocus', () => reFocus( pdbId, resnum ),
        'Refocus view to the mutated residue (only for variants)');
        addTippedControl('Focus on ...', () => focusOn(),
        'Focus on residue number ...');
        addSeparator();
        addTippedControl('Download variant', () => downloadFile(pdbId, dict, simulation),
                         'Download currently shown structure in PDB format. All five models in one file. Does not include the membrane, WT nor RAS.');

        BasicMolStarWrapper.animate.modelIndex.targetFps = 30;

    </script>
  </body>
</html>
