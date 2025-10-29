<?php
header('Content-Type: text/html; charset=utf-8');
include("../sql/connect.php");

function pointImage($str): void {
    $src = mb_substr($str, 0, 1);
    $dst = mb_substr($str, -1);
    echo "<tr><td colspan=5><img src=\"../images/amino_acids_mutants/".$src.$dst.".png\"></td></tr>";
}

function drawTables($conn, $variant): int {
    $sql = "SELECT * FROM for_datatable";
    $sqlwhere = " WHERE variant = ?";
    $stmt = $conn->stmt_init();
    $stmt->prepare($sql . $sqlwhere);
    $stmt->bind_param("s", $variant);
    $stmt->execute();
    $strtype = 0;
    if ( $result = $stmt->get_result() ) {
        if ( $row = $result->fetch_assoc() ) {
            $strtype = $row["structure"];
            $style = "";
            if ( !is_null($row['consensus']) ) {
                $clin_status = $row['consensus'];
                if ( $clin_status == "Likely Pathogenic" ) {
                    $style = "class=\"table-danger\"";
                }
                elseif ( $clin_status == "Likely Benign" ) {
                    $style = "class=\"table-success\"";
                }
            }

            echo "<p><b>Variant " . $variant . "</b></p>\n";
            echo "<!-- Tab links -->
<div class='tab'>
  <button class='tablinks' onclick=\"showSummary(event, 'MD')\" id='defaultOpen'>MD</button>
  <button class='tablinks' onclick=\"showSummary(event, 'AI')\">AI</button>
</div>";

            echo "<!-- Tab content -->
<div id='MD' class='tabcontent'>\n";
            if ( 2 == $strtype ) {
                echo "<p><em>Interpretation based on the MD results:</em></p>\n";
                echo "<p class='myjust'>".$row['description']."</p>\n";
            } else {
                echo "<p><em>No MD-based annotation or description available</em></p>\n";
            }
            echo "</div>\n";

            echo "<div id='AI' class='tabcontent'>\n";
            if ( !is_null($row['summary']) ) {
                echo "<p><b>Disclaimer:</b> <em>A summary generated with AI and should be interpreted alongside expert review:</em></p>\n";
                $summary = $row['summary'];
                echo "<p class='myjust'>".$summary."</p>\n";
            } else {
                echo "<p><em>No AI summary available</em></p>\n";
            }
            echo "</div>\n";

            echo "<script>
// Get the element with id='defaultOpen' and click on it
document.getElementById(\"defaultOpen\").click();
</script>\n";

            echo "<hr>\n";

            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Information</caption>
<thead class=\"thead-light\">\n";
            echo "<tr><th>c.dna</th>
    <th>Variant</th>
	<th>Domain</th>
	<th>Clinical Status</th>
	<th>gnomAD</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row["cdna"] . "</td>\n";
            echo "<td " . $style . ">" . $variant . "</td>\n";
            echo "<td " . $style . ">" . $row['domain'] . "</td>\n";
            if ( is_null($row["clinvar_uid"]) ) {
                echo "<td " . $style . "></td>";
            }
            else {
                echo "<td " . $style . "><a href=\"https://www.ncbi.nlm.nih.gov/clinvar/variation/" . $row['clinvar_uid'] . "\" target=\"_blank\">" . $row['cv_status'] . ",";
                if ( $row["cv_review"] ) {
                    for ($x = 0; $x < $row["cv_review"]; $x++) {
                        echo " <span class=\"fas fa-star\"></span>";
                    }
                }
                echo " (".$row["cv_submissions"].")";
                echo "</a></td>";
            }
            if ( is_null($row["gnomAD_id"]) ) {
                echo "<td " . $style . "></td>";
            }
            else {
                echo "<td " . $style . "><a href=\"https://gnomad.broadinstitute.org/variant/".$row["gnomAD_id"]."?dataset=gnomad_r4\" target=\"_blank\">" . $row['gnomAD_id'] . "</td>";
            }
            echo "</tr>\n";
            pointImage($row["variant"]);
            if ( 2 == $strtype ) {
            echo "<tr><td colspan=5>Contacts</td></tr>";
            echo "<tr><td colspan=5>\n";
            echo "<!-- Tab links -->
<div class='tab'>
  <button class='liglinks' onclick=\"showLigplot(event, 'initial')\" id='defaultLigplot'>initial</button>
  <button class='liglinks' onclick=\"showLigplot(event, 't0ns')\">0ns</button>
  <button class='liglinks' onclick=\"showLigplot(event, 't50ns')\">50ns</button>
  <button class='liglinks' onclick=\"showLigplot(event, 't100ns')\">100ns</button>
  <button class='liglinks' onclick=\"showLigplot(event, 't150ns')\">150ns</button>
</div>";
            echo "<!-- Tab content -->
<div id='initial' class='ligcontent'>\n";
            echo "<img src='../data/ligplot/".$row["variant"]."_initial.png'>";
            echo "</div>\n";

            echo "<div id='t0ns' class='ligcontent'>\n";
            echo "<img src='../data/ligplot/".$row["variant"]."_0ns.png'>";
            echo "</div>\n";

            echo "<div id='t50ns' class='ligcontent'>\n";
            echo "<img src='../data/ligplot/".$row["variant"]."_50ns.png'>";
            echo "</div>\n";

            echo "<div id='t100ns' class='ligcontent'>\n";
            echo "<img src='../data/ligplot/".$row["variant"]."_100ns.png'>";
            echo "</div>\n";

            echo "<div id='t150ns' class='ligcontent'>\n";
            echo "<img src='../data/ligplot/".$row["variant"]."_150ns.png'>";
            echo "</div>\n";
            echo "</td></tr>\n";

            echo "<tr><td colspan=5><img src='../data/ligplot/key.png'></td></tr>";
            } elseif ( 1 == $strtype ) {
            echo "<tr><td colspan=5>Contacts</td></tr>";
            echo "<tr><td colspan=5><img src='../data/ligplot/".$row["variant"].".png'></td></tr>\n";
            echo "<tr><td colspan=5><img src='../data/ligplot/key.png'></td></tr>";
            }
            echo "</tbody>
    </table>\n";
            if ( 2 == $strtype ) {
            echo "<script>document.getElementById(\"defaultLigplot\").click();</script>\n";
            }
            echo "\n<table class=\"table table-bordered table-sm text-center\">\n";
            echo "<caption class=\"oldcap\">Consensus predictions</caption>\n";
            echo "<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>REVEL</th>
	<th>SGM</th>
      </tr><tr>
	<th>Score</th>
	<th>Prediction</th>
	<th>Consensus</th>\n";
            echo "</tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>\n";
            echo "<td " . $style . ">" . $row['revel_score'] . "</td>\n";
            echo "<td " . $style . ">" . $row['revel_predict'] . "</td>\n";
            if ( is_null($row["consensus"]) ) {
                echo "<td " . $style . "></td>";
            }
            else {
                echo "<td " . $style . ">" . $row["consensus"] . "</td>";
            }
            echo "</tr>\n";
            echo "</tbody>\n";
            echo "</table>\n\n";

            if ( 2 == $strtype ) {
            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Structural annotation</caption>
  <thead class=\"thead-light\">\n";
            echo "<tr>
	<th>Secondary</th>
	<th>Tertiary bonds</th>
	<th>Inside out</th>
	<th>GAP-Ras interface</th>
	<th>At membrane</th>
	<th>No effect</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . ($row["SA_Secondary"] ? 'X' : '') . "</td>\n";
            echo "<td " . $style . ">" . ($row["SA_Tertiary"] ? 'X' : '') . "</td>\n";
            echo "<td " . $style . ">" . ($row["SA_Buried"] ? 'X' : '') . "</td>\n";
            echo "<td " . $style . ">" . ($row["SA_GAP_Ras_interface"] ? 'X' : '') . "</td>\n";
            echo "<td " . $style . ">" . ($row["SA_Membrane_interface"] ? 'X' : '') . "</td>\n";
            echo "<td " . $style . ">" . ($row["SA_Benign"] ? 'X' : '') . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
</table>\n\n";


            $alertstyle = $style;
            if ( $row['Alert'] ) {
                $alertstyle = "class=\"table-danger\"";
            }
            echo "<table class=\"table table-bordered table-sm text-center\">";
            echo "<thead class=\"thead-light\">\n";
            echo "<tr>
	<th>MD Alert</th>
	<th>SGM MD-based Verdict</th>\n";
            echo "</tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>\n";
            echo "<td " . $alertstyle . ">" . ($row["Alert"] ? 'X' : '') . "</td>\n";
            echo "<td " . $style . ">" . $row['verdict'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>\n";
            echo "</table>\n\n";
            }

            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Protein folding stability analysis</caption>
<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>PremPS</th>
	<th colspan=3>FoldX</th>
      </tr>
      <tr>
	<th>Prediction</th>
	<th>&Delta;&Delta;G</th>

	<th>Prediction</th>
	<th>Avg. &Delta;&Delta;G</th>
	<th>StdDev</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['premPS_predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['premPS_ddG'] . "</td>\n";
            echo "<td " . $style . ">" . $row['foldx_predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['foldx_avg_ddG'] . "</td>\n";
            echo "<td " . $style . ">" . $row['foldx_stddev'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";

            echo "<table class=\"table table-bordered table-sm text-center\">
<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>Rosetta</th>
	<th colspan=2>Foldetta</th>
      </tr>
      <tr>
	<th>Prediction</th>
	<th>&Delta;&Delta;G</th>

	<th>Prediction</th>
	<th>&Delta;&Delta;G</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['rosetta_predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['rosetta_ddG'] . "</td>\n";
            echo "<td " . $style . ">" . $row['foldetta_predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['foldetta_ddG'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";

            if ( 2 == $strtype ) {
            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Molecular Dynamics properties</caption>
<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2 rowspan=2>SASA</th>
	<th colspan=4>Normalized B-factor</th>
      </tr>
      <tr>
	<th colspan=2>backbone</th>
	<th colspan=2>sidechain</th>
      </tr>
      <tr>
	<th>Average</th>
	<th>&Delta;</th>

	<th>&Delta;</th>
	<th>StdDev</th>

	<th>&Delta;</th>
	<th>StdDev</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['sasa_average'] . "</td>\n";
            echo "<td " . $style . ">" . $row['sasa_delta'] . "</td>\n";
            echo "<td " . $style . ">" . $row['Bfactor_backbone_delta'] . "</td>\n";
            echo "<td " . $style . ">" . $row['Bfactor_backbone_stddev'] . "</td>\n";
            echo "<td " . $style . ">" . $row['Bfactor_sidechain_delta'] . "</td>\n";
            echo "<td " . $style . ">" . $row['Bfactor_sidechain_stddev'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";
            }

            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Evolutionary and physical properties</caption>
<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>PAM</th>
	<th colspan=2>Physical changes</th>
      </tr>
      <tr>
	<th>PAM250</th>
	<th>PAM120</th>
	<th>Molecular Weight &Delta;</th>
	<th>Hydropathy &Delta;</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['PAM250'] . "</td>\n";
            echo "<td " . $style . ">" . $row['PAM120'] . "</td>\n";
            echo "<td " . $style . ">" . $row['deltaMW'] . "</td>\n";
            echo "<td " . $style . ">" . $row['deltaHydropathy'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";


            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Sequence-based analysis</caption>";
            echo "<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>SIFT</th>
	<th colspan=2>PROVEAN</th>
	<th colspan=2>FATHMM</th>
</tr>
<tr>
	<th>Prediction</th>
	<th>Status</th>
	<th>Score</th>
	<th>Prediction</th>
	<th>Score</th>
	<th>Prediction</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['SIFT_animal_Predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['SIFT_animal_Warnings'] . "</td>\n";
            echo "<td " . $style . ">" . $row['provean_score'] . "</td>\n";
            echo "<td " . $style . ">" . $row['provean_predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['FATHMM_Diseasespecific_Nervous_System_Score'] . "</td>\n";
            echo "<td " . $style . ">" . $row['FATHMM_predict'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";


            echo "<table class=\"table table-bordered table-sm text-center\">
<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>PolyPhen-2 HumDiv</th>
	<th colspan=2>PolyPhen-2 HumVar</th>
      </tr>
      <tr>
	<th>pph2_prob</th>
	<th>Prediction</th>

	<th>pph2_prob</th>
	<th>Prediction</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['polyPhen2_HumDiv_pph2_prob'] . "</td>\n";
            echo "<td " . $style . ">" . $row['polyPhen2_HumDiv_predict'] . "</td>\n";
            echo "<td " . $style . ">" . $row['polyPhen2_HumVar_pph2_prob'] . "</td>\n";
            echo "<td " . $style . ">" . $row['polyPhen2_HumVar_predict'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";


            echo "<table class=\"table table-bordered table-sm text-center\">
<caption class=\"oldcap\">Deep Learning based predictions</caption>
<thead class=\"thead-light\">\n";
            echo "<tr>
	<th colspan=2>ESM1b (Q96PV0)</th>
	<th colspan=2>AlphaMissense</th>
      </tr>
      <tr>
	<th>LLR score</th>
	<th>Prediction</th>
	<th>Pathogenicity</th>
	<th>Class</th>
      </tr>\n";
            echo "</thead>
<tbody>\n";
            echo "<tr>";
            echo "<td " . $style . ">" . $row['ESM1b_Q96PV0_LLRscore'] . "</td>\n";
            echo "<td " . $style . ">" . $row['ESM1b_Q96PV0_Prediction'] . "</td>\n";
            echo "<td " . $style . ">" . $row['AlphaMissense_Pathogenicity'] . "</td>\n";
            echo "<td " . $style . ">" . $row['AlphaMissense_Class'] . "</td>\n";
            echo "</tr>\n";
            echo "</tbody>
    </table>\n\n";
        }
    }
    $stmt->close();
    return $strtype;
}
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
 <style>
 .oldcap {
  caption-side: top;
  text-align: center;
 }
 img {
     display: block;
     margin-left: auto;
     margin-right: auto;
     max-width: 100%;
     height: auto;
 }
 .myjust {
     text-align: justify;
     text-justify: inter-word;
 }

#topologycont {
// max-width: 2421px;
// width:2421px;
overflow: scroll;
}

#topology {
max-width: 2421px;
width:2421px;
}

 #containerx {
     display: flex;
     flex-direction: column;
     align-items: center;
 }
 #buttonsx {
     display: flex;
     flex-direction: row;
     align-items: center;
     justify-content: center;
     margin-top: 10px;
 }
 #buttonsx button {
     margin: 0 5px;
     padding: 5px 10px;
     border: none;
     border-radius: 5px;
     background-color: #00B4CC;
     color: white;
     font-size: 16px;
     cursor: pointer;
 }

 /* Style the tab */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    border-style: none none solid none;
    /* background-color: #e9ecef; */
}

/* Style the buttons that are used to open the tab content */
.tab button {
    background-color: #e9ecef;
    /* background-color: inherit; */
    float: left;
    border: none;
    border-bottom-style: 1px solid #ccc;
    outline: none;
    cursor: pointer;
    padding: 6px 36px;
    transition: 0.3s;
    border-radius: 16px 16px 0px 0px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #bbb;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}
.ligcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}
</style>
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
 <link rel="stylesheet" href="../styles/fa.css" />
</head>
<body style="padding: 10px;">
<script>
function showSummary(evt, name) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class='tabcontent' and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class='tablinks' and remove the class 'active'
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an 'active' class to the button that opened the tab
  document.getElementById(name).style.display = "block";
  evt.currentTarget.className += " active";
}
function showLigplot(evt, name) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class='tabcontent' and hide them
  tabcontent = document.getElementsByClassName("ligcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class='tablinks' and remove the class 'active'
  tablinks = document.getElementsByClassName("liglinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an 'active' class to the button that opened the tab
  document.getElementById(name).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
<h4>Description</h4>
<?php
         if ( ! isset($_GET['q']) ) {
             die("No variant specified");
         }
         if ( $_GET['q'] == '' ) {
             die("Empty variant specified");
         }
$variant =  $_GET['q'];

$strtype = 0;
if ( $variant == "WT" ) {
    echo "<p><b>Wild type</b> (WT) SynGAP1 protein that contains no variant mutations.</p>\n";
}
else {
    $strtype = drawTables($conn, $variant);
}
if ( $variant == "WT" || $strtype == 2 ) {
echo "<hr>

<h4>Secondary structure topology at 150 ns with B-factor coloring</h4>

<div id=\"topologycont\">
<img id=\"topology\" src=\"../data/images/topology/".$variant.".png\" alt=\"Topology schema for ".$variant." NOT AVAILABLE\"/>
</div>

<hr>

<h4>Changes in root mean squared deviation (RMSD) during simulations</h4>
<div id=\"containerx\">
  <img id=\"imagermsd\" src=\"../data/rmsd_sims/".$variant.".png\" alt=\"RMSD simulation for ".$variant." NOT AVAILABLE\">
  <div id=\"buttonsx\">
    <button onclick=\"firstImagermsd()\">|<</button>
    <button onclick=\"previousImagermsd()\"><</button>
    <button onclick=\"nextImagermsd()\">></button>
    <button onclick=\"lastImagermsd()\">>|</button>
  </div>
</div>

<hr>
<h4>Ramachandran plots</h4>
<div id=\"containerx\">
  <img style='height: 600px; width: auto' id=\"imagerama\" src=\"../data/ramaplots/sim-1/0ns/".$variant.".png\">
  <div id=\"buttonsx\">
    <button onclick=\"firstImagerama()\">|<</button>
    <button onclick=\"previousImagerama()\"><</button>
    <button onclick=\"nextImagerama()\">></button>
    <button onclick=\"lastImagerama()\">>|</button>
  </div>
</div>

<hr>
<h4>Normalized B-factor during simulations</h4>
<div id=\"containerx\">
  <img id=\"imagemin\" src=\"../data/rmsf/rmsf_min/sim-1/".$variant.".png\">
  <div id=\"buttonsx\">
    <button onclick=\"firstImagemin()\">|<</button>
    <button onclick=\"previousImagemin()\"><</button>
    <button onclick=\"nextImagemin()\">></button>
    <button onclick=\"lastImagemin()\">>|</button>
  </div>
</div>

<script>
  var imagesrmsd = [\"../data/rmsd_sims/".$variant.".png\",
  \"../data/rmsd_loop/sim-1/".$variant.".png\", \"../data/rmsd_loop/sim-2/".$variant.".png\", \"../data/rmsd_loop/sim-3/".$variant.".png\" ];
  var indexrmsd = 0;
  var imgrmsd = document.getElementById(\"imagermsd\");

  var imagesrama = [
   \"../data/ramaplots/sim-1/0ns/".$variant.".png\",
   \"../data/ramaplots/sim-1/50ns/".$variant.".png\",
   \"../data/ramaplots/sim-1/100ns/".$variant.".png\",
   \"../data/ramaplots/sim-1/150ns/".$variant.".png\",
   \"../data/ramaplots/sim-2/0ns/".$variant.".png\",
   \"../data/ramaplots/sim-2/50ns/".$variant.".png\",
   \"../data/ramaplots/sim-2/100ns/".$variant.".png\",
   \"../data/ramaplots/sim-2/150ns/".$variant.".png\",
   \"../data/ramaplots/sim-3/0ns/".$variant.".png\",
   \"../data/ramaplots/sim-3/50ns/".$variant.".png\",
   \"../data/ramaplots/sim-3/100ns/".$variant.".png\",
   \"../data/ramaplots/sim-3/150ns/".$variant.".png\"
  ];
  var indexrama = 0;
  var imgrama = document.getElementById(\"imagerama\");

  var imagesmin = [\"../data/rmsf/rmsf_min/sim-1/".$variant.".png\",
  \"../data/rmsf/rmsf_min/sim-2/".$variant.".png\", \"../data/rmsf/rmsf_min/sim-3/".$variant.".png\",
  \"../data/rmsf/rmsf_max/sim-1/".$variant.".png\", \"../data/rmsf/rmsf_max/sim-2/".$variant.".png\",
  \"../data/rmsf/rmsf_max/sim-3/".$variant.".png\"];
  var indexmin = 0;
  var imgmin = document.getElementById(\"imagemin\");


  function firstImagemin() {
      indexmin = 0;
      imgmin.src = imagesmin[indexmin];
  }

  function previousImagemin() {
      if (indexmin > 0) {
          indexmin--;
          imgmin.src = imagesmin[indexmin];
      }
  }

  function nextImagemin() {
      if (indexmin < imagesmin.length - 1) {
          indexmin++;
          imgmin.src = imagesmin[indexmin];
      }
  }

  function lastImagemin() {
      indexmin = imagesmin.length - 1;
      imgmin.src = imagesmin[indexmin];
  }


  function firstImagermsd() {
      indexrmsd = 0;
      imgrmsd.src = imagesrmsd[indexrmsd];
  }

  function nextImagermsd() {
      if (indexrmsd < imagesrmsd.length - 1) {
          indexrmsd++;
          imgrmsd.src = imagesrmsd[indexrmsd];
      }
  }

  function previousImagermsd() {
      if (indexrmsd > 0) {
          indexrmsd--;
          imgrmsd.src = imagesrmsd[indexrmsd];
      }
  }

  function lastImagermsd() {
      indexrmsd = imagesrmsd.length - 1;
      imgrmsd.src = imagesrmsd[indexrmsd];
  }


  function firstImagerama() {
      indexrama = 0;
      imgrama.src = imagesrama[indexrama];
  }

  function nextImagerama() {
      if (indexrama < imagesrama.length - 1) {
          indexrama++;
          imgrama.src = imagesrama[indexrama];
      }
  }

  function previousImagerama() {
      if (indexrama > 0) {
          indexrama--;
          imgrama.src = imagesrama[indexrama];
      }
  }

  function lastImagerama() {
      indexrama = imagesrama.length - 1;
      imgrama.src = imagesrama[indexrama];
  }

</script>\n";
}
$conn->close();
?>
</body>
</html>
