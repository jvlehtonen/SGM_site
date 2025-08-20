<?php
header('Content-Type: text/html; charset=utf-8');
include("../sql/connect.php");

function pointImage($str): void {
    $src = mb_substr($str, 0, 1);
    $dst = mb_substr($str, -1);
    echo "<tr><td colspan=6><img src=\"/images/amino_acids_mutants/".$src.$dst.".png\"></td></tr>";
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

            if ( 2 == $strtype ) {
                echo "<p class=\"myjust\"><b>Variant " . $variant . "</b>: " . $row['description'];
                echo "</p>\n";
            } else {
                echo "<p class=\"myjust\"><b>Variant " . $variant . "</b>: ";
                if ( !is_null($row['summary']) ) {
                    $summary = $row['summary'];
                     echo $summary . "</p><p><em>Disclaimer: This summary was generated using AI and should be interpreted alongside expert review.</em></p>\n";
                } else {
                    echo "No MD-based annotation or description available</p>\n";
                }
            }

            echo "<table class=\"table table-bordered table-sm text-center\">
<thead class=\"thead-light\">\n";
            echo "<tr><th>c.dna</th>
    <th>Variant</th>
	<th>Domain</th>
	<th>Clinical Status</th>
	<th>gnomAD</th>
	<th>SGM Consensus</th>
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
            if ( is_null($row["consensus"]) ) {
                echo "<td " . $style . "></td>";
            }
            else {
                echo "<td " . $style . ">" . $row["consensus"] . "</td>";
            }
            echo "</tr>\n";
            pointImage($row["variant"]);

            echo "</tbody>
    </table>\n\n";

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
 } </style>
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
 <link rel="stylesheet" href="/styles/fa.css" />
</head>
<body style="padding: 10px;">
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
<img id=\"topology\" src=\"/data/images/topology/".$variant.".png\" alt=\"Topology schema for ".$variant." NOT AVAILABLE\"/>
</div>

<hr>

<h4>Changes in root mean squared deviation (RMSD) during simulations</h4>
<div id=\"containerx\">
  <img id=\"imagermsd\" src=\"/data/rmsd_sims/".$variant.".png\" alt=\"RMSD simulation for ".$variant." NOT AVAILABLE\">
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
  <img style='height: 600px; width: auto' id=\"imagerama\" src=\"/data/ramaplots/sim-1/0ns/".$variant.".png\">
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
  <img id=\"imagemin\" src=\"/data/rmsf/rmsf_min/sim-1/".$variant.".png\">
  <div id=\"buttonsx\">
    <button onclick=\"firstImagemin()\">|<</button>
    <button onclick=\"previousImagemin()\"><</button>
    <button onclick=\"nextImagemin()\">></button>
    <button onclick=\"lastImagemin()\">>|</button>
  </div>
</div>

<script>
  var imagesrmsd = [\"/data/rmsd_sims/".$variant.".png\",
  \"/data/rmsd_loop/sim-1/".$variant.".png\", \"/data/rmsd_loop/sim-2/".$variant.".png\", \"/data/rmsd_loop/sim-3/".$variant.".png\" ];
  var indexrmsd = 0;
  var imgrmsd = document.getElementById(\"imagermsd\");

  var imagesrama = [
   \"/data/ramaplots/sim-1/0ns/".$variant.".png\",
   \"/data/ramaplots/sim-1/50ns/".$variant.".png\",
   \"/data/ramaplots/sim-1/100ns/".$variant.".png\",
   \"/data/ramaplots/sim-1/150ns/".$variant.".png\",
   \"/data/ramaplots/sim-2/0ns/".$variant.".png\",
   \"/data/ramaplots/sim-2/50ns/".$variant.".png\",
   \"/data/ramaplots/sim-2/100ns/".$variant.".png\",
   \"/data/ramaplots/sim-2/150ns/".$variant.".png\",
   \"/data/ramaplots/sim-3/0ns/".$variant.".png\",
   \"/data/ramaplots/sim-3/50ns/".$variant.".png\",
   \"/data/ramaplots/sim-3/100ns/".$variant.".png\",
   \"/data/ramaplots/sim-3/150ns/".$variant.".png\"
  ];
  var indexrama = 0;
  var imgrama = document.getElementById(\"imagerama\");

  var imagesmin = [\"/data/rmsf/rmsf_min/sim-1/".$variant.".png\",
  \"/data/rmsf/rmsf_min/sim-2/".$variant.".png\", \"/data/rmsf/rmsf_min/sim-3/".$variant.".png\",
  \"/data/rmsf/rmsf_max/sim-1/".$variant.".png\", \"/data/rmsf/rmsf_max/sim-2/".$variant.".png\",
  \"/data/rmsf/rmsf_max/sim-3/".$variant.".png\"];
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
