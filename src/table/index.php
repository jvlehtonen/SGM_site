<?php
header('Content-Type: text/html; charset=utf-8');
$results_per_page = 200;

function sortURL($column, $order, $sortCol, $text) {
    $up_or_down = str_replace(array('ASC','DESC'), array('up','down'), $order);
	$asc_or_desc = $order == 'ASC' ? 'desc' : 'asc';
    echo "<a href=\"index.php?column=".$sortCol;
    echo "&order=".$asc_or_desc;
    if ( isset($_GET['subset']) ) echo '&subset='.$_GET['subset'];
    if ( isset($_GET['sengines']) ) echo '&sengines='.$_GET['sengines'];
    if ( isset($_GET['q']) ) echo '&q='.$_GET['q'];
    echo "\">".$text."<b class=\"fas fa-sort";
    echo $column == $sortCol ? '-' . $up_or_down : '';
    echo "\"></b></a>";
}

function pageURL($pc, $class, $text) {
    echo " <a href=\"?page=$pc";
    if ( isset($_GET['column']) ) echo '&column='.$_GET['column'];
    if ( isset($_GET['order']) ) echo '&order='.$_GET['order'];
    if ( isset($_GET['subset']) ) echo '&subset='.$_GET['subset'];
    if ( isset($_GET['sengines']) ) echo '&sengines='.$_GET['sengines'];
    if ( isset($_GET['q']) ) echo '&q='.$_GET['q'];
    echo "\" class=\"".$class."\">".$text."</a>";
}

function radioBut($group, $val, $title) {
    echo "<input type=\"radio\" id=\"sub".$val."\" name=\"".$group."\" value=\"".$val."\"";
    echo " onclick=\"handleClick(this);\"";
    if ( isset($_GET[$group]) && $_GET[$group] == $val) echo ' checked';
    if ( ! isset($_GET[$group]) && $val == 'clinical' ) echo ' checked';
    echo "><label for=\"sub".$val."\">".$title."</label>\n";
}

function pointImage($str) {
    $src = mb_substr($str, 0, 1);
    $dst = mb_substr($str, -1);
    echo "<span class=\"mytooltip\"><img src=\"/images/amino_acids_mutants/".$src.$dst.".png\"></span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
include("../sql/connect.php");
$columns = array('basenum', 'resnum', 'cv_rank', 'verdictID', 'deltaMW', 'deltaHydropathy', 'foldx_predict',
                 'foldx_avg_ddG', 'premPS_predict', 'premPS_ddG', 'sasa_average',
                 'sasa_delta', 'Bfactor_backbone_delta', 'Bfactor_sidechain_delta', 'polyPhen2_HumDiv_predict',
                 'polyPhen2_HumDiv_pph2_prob', 'polyPhen2_HumVar_predict', 'polyPhen2_HumVar_pph2_prob',
                 'SIFT_animal_Predict', 'SIFT_animal_Warnings', 'SIFT_animal_Sequences', 'SIFT_animal_Conservation',
                 'PAM250', 'PAM120', 'ESM1b_Q96PV0_LLRscore', 'provean_score',
                 'ESM1b_Q96PV0_Prediction', 'FATHMM_Diseasespecific_Nervous_System_Score',
                 'AlphaMissense_Pathogenicity', 'AlphaMissense_Class', 'clinvar_uid',
                 'cv_review', 'cv_submissions', 'gnomAD_id', 'structure', 'doi', 'HGVSc', 'allele_count',
                 'rosetta_predict', 'rosetta_ddG', 'foldetta_predict', 'foldetta_ddG',
                 'allele_freq', 'cdna', 'revel_score', 'consensus');
$searchcols = array('variant', 'cdna');

$scol = isset($_GET['sengines']) && in_array($_GET['sengines'], $searchcols ) ? $_GET['sengines'] : 'cdna';

$column = isset($_GET['column']) && in_array($_GET['column'], $columns ) ? $_GET['column'] : 'basenum';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

$sql = "SELECT * FROM for_datatable";
$sqlwhere = " WHERE ".$scol." LIKE ?";
$subset = $_GET['subset'] ?? 'clinical';
if ( $subset == 'clinical' ) $sqlwhere = $sqlwhere." AND clinvar_uid IS NOT NULL";
if ( $subset == 'gnomad' ) $sqlwhere = $sqlwhere." AND gnomad_id IS NOT NULL";
if ( $subset == 'structure' ) $sqlwhere = $sqlwhere." AND 0 < structure";
if ( $subset == 'mdstructure' ) $sqlwhere = $sqlwhere." AND 2 = structure";

$sqlorder = " ORDER BY ".$column.' '.$sort_order;

if (isset($_GET["page"])) { $page = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;
$sqlsubset = " LIMIT $start_from, ".$results_per_page;
// $sqlsubset = "";

$stmt = $conn->stmt_init();
$stmt->prepare($sql . $sqlwhere . $sqlorder . $sqlsubset);

$search = isset($_GET['q']) ? '%'.$_GET['q'].'%' : '%';
$stmt->bind_param("s", $search);
$stmt->execute();

if ( $result = $stmt->get_result() ) {
	$add_class_name = 'highlight';
    ?>
  <head>
    <title>Data | SynGAP Server</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="/misc/titles/SynGAP.png" sizes="32x32" />
    <link rel="stylesheet" href="./css_final.css" />
    <link rel="stylesheet" href="/styles/fa.css" />
    <link rel="stylesheet" href="/styles/overlay.css" />
    <link rel="stylesheet" href="/styles/topnav.css" />
    <link rel="stylesheet" href="/styles/infotooltip.css" />
    <script type="text/javascript" src="/sites/w3.js"></script>
    <script type="text/javascript" src="/sites/overlay.js"></script>
  </head>
  <body>
    <div w3-include-html="/sites/overlay.html"></div>
    <h1><a href="/index.html"><span><img src="/misc/titles/sgm_table.png" alt="SynGap Missense Server" style="height:64px;"></span></a></h1>
    <nav style="margin-bottom:15px">
       <div style="display:flex;">
       <div class="topnav fa-icon" w3-include-html="/sites/hamburger.html"></div>
       <script>w3.includeHTML();</script>
       <a class="fa-icon" href="/index.html"><i class='fa fa-home' style='font-size:36px;color:#00B4CC;'></i></a>
       <div class="infotooltip fa-icon">
       <a href="/sql/export_csv.php"><i class='fa fa-download' style='font-size:36px;color:#00B4CC;'></i></a>
       <span class="infotooltiptext w120 mycenter">
       The table is downloadable as a semicolon (;) separated CSV file
       </span>
       </div>
       <div class="infotooltip fa-icon">
       <a href="metrics.html"><i class='fa fa-info-circle' style='font-size:36px;color:#00B4CC;'></i></a>
       <span class="infotooltiptext w200 myjust">
       Description of table metrics (columns)
       </span>
       </div>
       <div class="infotooltip fa-icon">
        <i class='fa fa-question-circle' style='font-size:36px;color:#00B4CC;'></i>
        <span class="infotooltiptext w600 myjust">
          <b>Data Table</b> includes various sequence-, structure-, evolution- and/or physics-based pathogenicity predictions (e.g., SIFT, PolyPhen-2, ESMb1, AlphaMissense) for the missense variants. The structural modelling is provided for all missense variants within the scope of the SynGAP model (res. 198-730). The structural annotation and molecular dynamics (MD) simulation metrics (e.g., SASA, B-factor) are provided for those missense variants that have been studied using SynGAP-solvent MD simulations. Column Variant shows change in residue as tooltip and has link to 3D Viewer if there is a structure to show.
        </span>
      </div>
      </div>
    </nav>

   <div class="topnav">
     <form action="/table/index.php" id="filters" method="GET">
      <label style="margin-left: 10px;">Search:</label>
      <select name="sengines" onchange="seChange(this);">
<?php if ( "cdna" == $scol) {
  echo "<option value=\"cdna\" selected>c.dna</option>
<option value=\"variant\">Variant</option>
</select>
<label>For:</label>
<input type=\"search\" placeholder=\"Name (e.g. c.597C>A)\" style=\"margin-left: 10px;\"
 aria-label=\"Search variant\" id=\"q\" name=\"q\" maxlength=\"9\" value=\"". htmlspecialchars($_GET['q']) ."\"/>";
} else {
  echo "<option value=\"cdna\" selected>c.dna</option>
<option value=\"variant\" selected>Variant</option>
</select>
<label>For:</label>
<input type=\"search\" placeholder=\"Name (e.g. N199K)\" style=\"margin-left: 10px;\"
 aria-label=\"Search variant\" id=\"q\" name=\"q\" maxlength=\"9\" value=\"". htmlspecialchars($_GET['q']) ."\"/>";
} ?>
      <button type="submit">
        <i class="fa fa-search"></i>
      </button>
      <input type="button" id="reset" style="cursor:pointer;" value="&#xF0E2;" onclick="show_all()" />
      <label><b>Rows:</b></label>
      <?php radioBut('subset', 'clinical', 'In ClinVar'); ?>
      <?php radioBut('subset', 'gnomad', 'In gnomAD'); ?>
      <?php radioBut('subset', 'structure', 'With 3D model'); ?>
      <?php radioBut('subset', 'mdstructure', 'With 3D and MD data'); ?>
      <?php radioBut('subset', 'all', 'All'); ?>
      <label style="margin-left:10px;">|</label>
      <input type="button" id="showhide" style="width:200px;text-align:center;" value="Show/Hide columns" onclick="sgmshowhide()" />
     </form>
   </div>
      <div class="container">
        <ul id="shmenu" class="container__menu container__menu--hidden">
          <li><label> <input type="checkbox" data-column-index="21"/>SGM Consensus</label></li>
          <li><label> <input type="checkbox" data-column-index="16"/>Domain</label></li>
          <li><label> <input type="checkbox" data-column-index="1"/>ClinVar</label></li>
          <li><label> <input type="checkbox" data-column-index="13"/>gnomAD</label></li>
          <li><label> <input type="checkbox" data-column-index="9"/>ESM1b</label></li>
          <li><label> <input type="checkbox" data-column-index="11"/>AlphaMissense</label></li>
          <li><label> <input type="checkbox" data-column-index="20"/>REVEL</label></li>
          <li><label> <input type="checkbox" data-column-index="2"/>FoldX</label></li>
          <li><label> <input type="checkbox" data-column-index="17"/>Rosetta</label></li>
          <li><label> <input type="checkbox" data-column-index="18"/>Foldetta</label></li>
          <li><label> <input type="checkbox" data-column-index="3"/>PremPS</label></li>
          <li><label> <input type="checkbox" data-column-index="19"/>PROVEAN</label></li>
          <li><label> <input type="checkbox" data-column-index="6"/>PolyPhen-2</label></li>
          <li><label> <input type="checkbox" data-column-index="10"/>FATHMM</label></li>
          <li><label> <input type="checkbox" data-column-index="7"/>SIFT</label></li>
          <li><label> <input type="checkbox" data-column-index="8"/>PAM</label></li>
          <li><label> <input type="checkbox" data-column-index="14"/>Physical</label></li>
          <li><label> <input type="checkbox" data-column-index="4"/>SASA</label></li>
          <li><label> <input type="checkbox" data-column-index="5"/>Normalized B-factor</label></li>
          <li><label> <input type="checkbox" data-column-index="12"/>SynGAP Structural Annotation</label></li>
          <li><label> <input type="checkbox" data-column-index="15"/>DOI</label></li>
        </ul>
      </div>
    <p style="margin-top: 5px; margin-left: 10px;"><b>Table of SynGAP1 Isoform &alpha;2 (UniProt Q96PV0-1) Missense Variants.</b></p>
    <div class="outer-wrapper">
      <div class="table-wrapper">
        <table id="mainTable" class="table table-bordered table-hover table-sm">
        <thead class="thead-light">
  <tr>
	<th rowspan=2 style="position:sticky; left:0px; top:0px; z-index:3;"><?php sortURL($column, $sort_order, 'basenum', "c.dna") ?></th>
    <th rowspan=2 style="z-index:2;"><?php sortURL($column, $sort_order, 'resnum', "Variant") ?></th>
	<th rowspan=2 data-column-index="21"><?php sortURL($column, $sort_order, 'consensus', "SGM Consensus") ?></th>
	<th rowspan=2 data-column-index="16">Domain</th>
	<th colspan=3 data-column-index="1">ClinVar</th>
	<th colspan=3 data-column-index="13">gnomAD</th>
	<th colspan=2 data-column-index="9"><?php sortURL($column, $sort_order, 'ESM1b_Q96PV0_LLRscore', "ESM1b") ?></th>
	<th colspan=3 data-column-index="11"><?php sortURL($column, $sort_order, 'AlphaMissense_Pathogenicity', "AlphaMissense") ?></th>
	<th colspan=2 data-column-index="20"><?php sortURL($column, $sort_order, 'revel_score', "REVEL") ?></th>
	<th colspan=3 data-column-index="2"><?php sortURL($column, $sort_order, 'foldx_avg_ddG', 'FoldX') ?></th>
	<th colspan=2 data-column-index="17"><?php sortURL($column, $sort_order, 'rosetta_ddG', 'Rosetta') ?></th>
	<th colspan=2 data-column-index="18"><?php sortURL($column, $sort_order, 'foldetta_ddG', 'Foldetta') ?></th>
	<th colspan=2 data-column-index="3"><?php sortURL($column, $sort_order, 'premPS_ddG', 'PremPS') ?></th>
	<th colspan=2 data-column-index="19"><?php sortURL($column, $sort_order, 'provean_score', 'PROVEAN') ?></th>
	<th colspan=2 data-column-index="6"><?php sortURL($column, $sort_order, 'polyPhen2_HumDiv_pph2_prob', "PolyPhen-2 HumDiv") ?></th>
	<th colspan=2 data-column-index="6"><?php sortURL($column, $sort_order, 'polyPhen2_HumVar_pph2_prob', "PolyPhen-2 HumVar") ?></th>
	<th colspan=2 data-column-index="10">FATHMM</th>
	<th colspan=4 data-column-index="7">SIFT</th>
	<th colspan=2 data-column-index="8">PAM</th>
	<th colspan=2 data-column-index="14">Physical</th>
	<th colspan=2 data-column-index="4">SASA</th>
	<th colspan=2 data-column-index="5">Normalized B-factor backbone</th>
	<th colspan=2 data-column-index="5">Normalized B-factor sidechain</th>
	<th colspan=9 data-column-index="12">SynGAP Structural Annotation</th>
	<th rowspan=2 data-column-index="15">DOI</th>
  </tr>
  <tr>
    <th data-column-index="1"><?php sortURL($column, $sort_order, 'cv_rank', "Clinical Status") ?></th>
    <th data-column-index="1"><?php sortURL($column, $sort_order, 'cv_review', "Review") ?></th>
    <th data-column-index="1"><?php sortURL($column, $sort_order, 'cv_submissions', "Subm.") ?></th>
    <th data-column-index="13"><?php sortURL($column, $sort_order, 'gnomAD_id', "ID") ?></th>
    <th data-column-index="13"><?php sortURL($column, $sort_order, 'allele_count', "Allele count") ?></th>
    <th data-column-index="13"><?php sortURL($column, $sort_order, 'allele_freq', "Allele freq.") ?></th>
    <!-- ESM1b -->
	    <th data-column-index="9">LLR score</th>
	    <th data-column-index="9">Prediction</th>
    <!-- AlphaMissense -->
	    <th data-column-index="11">Pathogenicity</th>
	    <th data-column-index="11">Class</th>
	    <th data-column-index="11">Optimized</th>
    <!-- REVEL -->
	    <th data-column-index="20">Score</th>
	    <th data-column-index="20">Prediction</th>
    <!-- FoldX -->
	<th data-column-index="2">Average &Delta;&Delta;G</th>
    <th data-column-index="2">Prediction</th>
	<th data-column-index="2">StdDev</th>
    <!-- Rosetta -->
	<th data-column-index="17">&Delta;&Delta;G</th>
	<th data-column-index="17">Prediction</th>
    <!-- Foldetta -->
	<th data-column-index="18">&Delta;&Delta;G</th>
	<th data-column-index="18">Prediction</th>
    <!-- PremPS -->
	<th data-column-index="3">&Delta;&Delta;G</th>
	<th data-column-index="3">Prediction</th>
    <!-- PROVEAN -->
	<th data-column-index="19">Score</th>
	<th data-column-index="19">Prediction</th>
    <!-- pph2 HumDiv -->
	<th data-column-index="6">pph2_prob</th>
	<th data-column-index="6">Prediction</th>
    <!-- pph2 HumVar -->
	<th data-column-index="6">pph2_prob</th>
	<th data-column-index="6">Prediction</th>

	<th data-column-index="10"><?php sortURL($column, $sort_order, 'FATHMM_Diseasespecific_Nervous_System_Score', "Nervous System Score") ?></th>
	<th data-column-index="10">Prediction</th>

    <th data-column-index="7"><?php sortURL($column, $sort_order, 'SIFT_animal_Predict', "Prediction") ?></th>
	<th data-column-index="7"><?php sortURL($column, $sort_order, 'SIFT_animal_Warnings', "Status") ?></th>
	<th data-column-index="7"><?php sortURL($column, $sort_order, 'SIFT_animal_Conservation', "Conservation") ?></th>
	<th data-column-index="7"><?php sortURL($column, $sort_order, 'SIFT_animal_Sequences', "Sequences") ?></th>

	<th data-column-index="8"><?php sortURL($column, $sort_order, 'PAM250', "PAM250") ?></th>
	<th data-column-index="8"><?php sortURL($column, $sort_order, 'PAM120', "PAM120") ?></th>

	<th data-column-index="14"><?php sortURL($column, $sort_order, 'deltaHydropathy', "Hydropathy &Delta;") ?></a></th>
	<th data-column-index="14"><?php sortURL($column, $sort_order, 'deltaMW', "MW &Delta;") ?></th>

	<th data-column-index="4"><?php sortURL($column, $sort_order, 'sasa_average', 'Average') ?></th>
	<th data-column-index="4"><?php sortURL($column, $sort_order, 'sasa_delta', '&Delta;') ?></th>
	<th data-column-index="5"><?php sortURL($column, $sort_order, 'Bfactor_backbone_delta', '&Delta;') ?></th>
	<th data-column-index="5">StdDev</th>
	<th data-column-index="5"><?php sortURL($column, $sort_order, 'Bfactor_sidechain_delta', '&Delta;') ?></th>
	<th data-column-index="5">StdDev</th>
	<th data-column-index="12">Secondary</th>
	<th data-column-index="12">Tertiary bonds</th>
	<th data-column-index="12">Inside out</th>
	<th data-column-index="12">GAP-Ras interface</th>
	<th data-column-index="12">At membrane</th>
	<th data-column-index="12">No effect</th>
	<th rowspan=2 data-column-index="12">MD Alert</th>
    <th rowspan=2 data-column-index="12"><?php sortURL($column, $sort_order, 'verdictID', 'Verdict') ?></th>
	<th rowspan=2 data-column-index="12">Description</th>
  </tr>
        </thead>
        <tbody>

<?php $clinvarstyles = array(
    0 => "table-normal",
    1 => "table-success",
    2 => "table-normal",
    3 => "table-warning",
    4 => "table-danger",
    5 => "table-normal",
    6 => "table-normal",
    7 => "table-normal",
    8 => "table-normal"
);
    while($row = $result->fetch_assoc() ):
      $stylename = "table-normal";
      if ( !is_null($row['consensus']) ) {
          if ( $row['consensus'] == "Likely Benign" ) { $stylename = $clinvarstyles[1];}
          elseif ( $row['consensus'] == "Likely Pathogenic" ) { $stylename = $clinvarstyles[4];}
      }
      $style = "class=\"" . $stylename . "\"";

      echo "<tr ". $style .">";
      echo "<td>".$row["cdna"]."</td>";
      echo "<td class=\"myparentCell " . $stylename . ($column == 'resnum' ? ' '.$add_class_name : '') ."\">".$row["variant"];
      if ( 0 < $row["structure"] ) {
          // echo "<div class=\"myparentCell2\">";
          echo "<br><a href=\"https://syngapmissenseserver.utu.fi/viewer/index.php?q=".$row["variant"]."\">(3D Viewer)</a>";
          // echo "<span class=\"mytooltip2\"><img src=\"/images/structure/".$row["variant"].".png\"/></span>";
          // echo "</div>";
      }
      pointImage($row["variant"]);
      echo "</td>";
      echo "<td data-column-index=\"21\">".$row["consensus"]."</td>";
      echo "<td data-column-index=\"16\">".$row["domain"]."</td>";

      // ClinVar
      echo "<td data-column-index=\"1\" ". ($column == 'statusID' ? 'class="lb '.$add_class_name.'"' : 'class="lb"') .">";
      if ( is_null($row["clinvar_uid"]) ) {
          echo "</td>";
      }
      else {
          echo "<a href=\"https://www.ncbi.nlm.nih.gov/clinvar/variation/".$row["clinvar_uid"]."\" target=\"_blank\">".$row["cv_status"]."</a></td>";
      }

      echo "<td data-column-index=\"1\">";
      if ( $row["cv_review"] ) {
          for ($x = 0; $x < $row["cv_review"]; $x++) {
              echo "<span class=\"fas fa-star\"></span> ";
          }
      }
      echo "</td>";

      echo "<td data-column-index=\"1\">".$row["cv_submissions"]."</td>";

      // gnomAD
      if ( is_null($row["gnomAD_id"]) ) {
          echo "<td data-column-index=\"13\" class=\"lb\"></td>";
          echo "<td data-column-index=\"13\"></td>";
          echo "<td data-column-index=\"13\"></td>";
      }
      else {
          echo "<td data-column-index=\"13\" class=\"lb\"><a href=\"https://gnomad.broadinstitute.org/variant/".$row["gnomAD_id"]."?dataset=gnomad_r4\" target=\"_blank\">".$row["gnomAD_id"]."</a></td>";
          if ( is_null($row["allele_count"]) ) {
              echo "<td data-column-index=\"13\"></td>";
              echo "<td data-column-index=\"13\"></td>";
          }
          else {
              printf("<td data-column-index=\"13\">%d</td>", $row["allele_count"] );
              if ( $row["allele_count"] == 0 ) {
                  echo "<td data-column-index=\"13\">0</td>";
              }
              else {
                  printf("<td data-column-index=\"13\">%.2e</td>", $row["allele_freq"] );
              }
          }
      }
      echo "<td data-column-index=\"9\" class=\"lb\">".$row["ESM1b_Q96PV0_LLRscore"]."</td>";
      echo "<td data-column-index=\"9\">".$row["ESM1b_Q96PV0_Prediction"]."</td>";
      echo "<td data-column-index=\"11\" class=\"lb\">".$row["AlphaMissense_Pathogenicity"]."</td>";
      echo "<td data-column-index=\"11\">".$row["AlphaMissense_Class"]."</td>";
      echo "<td data-column-index=\"11\">".$row["alphamissense_predict"]."</td>";
      echo "<td data-column-index=\"20\" class=\"lb\">".$row["revel_score"]."</td>";
      echo "<td data-column-index=\"20\" class=\"lb\">".$row["revel_predict"]."</td>";
      echo "<td data-column-index=\"2\" class=\"lb\">".$row["foldx_avg_ddG"]."</td>";
      echo "<td data-column-index=\"2\">".$row["foldx_predict"]."</td>";
      echo "<td data-column-index=\"2\">".$row["foldx_stddev"]."</td>";
      echo "<td data-column-index=\"17\" class=\"lb\">".$row["rosetta_ddG"]."</td>";
      echo "<td data-column-index=\"17\">".$row["rosetta_predict"]."</td>";
      echo "<td data-column-index=\"18\" class=\"lb\">".$row["foldetta_ddG"]."</td>";
      echo "<td data-column-index=\"18\">".$row["foldetta_predict"]."</td>";
      echo "<td data-column-index=\"3\" class=\"lb\">".$row["premPS_ddG"]."</td>";
      echo "<td data-column-index=\"3\">".$row["premPS_predict"]."</td>";
      echo "<td data-column-index=\"19\" class=\"lb\">".$row["provean_score"]."</td>";
      echo "<td data-column-index=\"19\">".$row["provean_predict"]."</td>";
      echo "<td data-column-index=\"6\" class=\"lb\">".$row["polyPhen2_HumDiv_pph2_prob"]."</td>";
      echo "<td data-column-index=\"6\">".$row["polyPhen2_HumDiv_predict"]."</td>";
      echo "<td data-column-index=\"6\">".$row["polyPhen2_HumVar_pph2_prob"]."</td>";
      echo "<td data-column-index=\"6\">".$row["polyPhen2_HumVar_predict"]."</td>";
      echo "<td data-column-index=\"10\" class=\"lb\">".$row["FATHMM_Diseasespecific_Nervous_System_Score"]."</td>";
      echo "<td data-column-index=\"10\">".$row["FATHMM_predict"]."</td>";
      echo "<td data-column-index=\"7\" class=\"lb\">".$row["SIFT_animal_Predict"]."</td>";
      echo "<td data-column-index=\"7\">".$row["SIFT_animal_Warnings"]."</td>";
      echo "<td data-column-index=\"7\">".$row["SIFT_animal_Conservation"]."</td>";
      echo "<td data-column-index=\"7\">".$row["SIFT_animal_Sequences"]."</td>";
      echo "<td data-column-index=\"8\" class=\"lb\">".$row["PAM250"]."</td>";
      echo "<td data-column-index=\"8\">".$row["PAM120"]."</td>";
      echo "<td data-column-index=\"14\" class=\"lb\">".$row["deltaHydropathy"]."</td>";
      echo "<td data-column-index=\"14\">".$row["deltaMW"]."</td>";
      echo "<td data-column-index=\"4\" class=\"lb\">".$row["sasa_average"]."</td>";
      echo "<td data-column-index=\"4\">".$row["sasa_delta"]."</td>";
      echo "<td data-column-index=\"5\" class=\"lb\">".$row["Bfactor_backbone_delta"]."</td>";
      echo "<td data-column-index=\"5\">".$row["Bfactor_backbone_stddev"]."</td>";
      echo "<td data-column-index=\"5\">".$row["Bfactor_sidechain_delta"]."</td>";
      echo "<td data-column-index=\"5\">".$row["Bfactor_sidechain_stddev"]."</td>";
      echo "<td data-column-index=\"12\" class=\"lb\">". ($row["SA_Secondary"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">". ($row["SA_Tertiary"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">". ($row["SA_Buried"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">". ($row["SA_GAP_Ras_interface"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">". ($row["SA_Membrane_interface"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">". ($row["SA_Benign"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">". ($row["Alert"] ? 'X' : '') ."</td>";
      echo "<td data-column-index=\"12\">".$row["verdict"]."</td>";
      echo "<td data-column-index=\"12\" class=\"description_column ".$stylename."\">".$row["description"]."</td>";
      echo "<td data-column-index=\"15\"><a href=\"https://doi.org/".$row["doi"]."\" target=\"_blank\">".$row["doi"]."</a></td>";
      echo "</tr>\n";
      endwhile;
      ?>
        </tbody>
        </table>
      </div>
    </div>
<?php
$sqlcount = "SELECT COUNT(cdna) AS total FROM for_datatable".$sqlwhere;
$stmt2 = $conn->stmt_init();
$stmt2->prepare($sqlcount);
$stmt2->bind_param("s", $search);
$stmt2->execute();
if ( $res = $stmt2->get_result() )
{
    $row = $res->fetch_assoc();
    echo "<p>Found ".$row["total"]." rows. Show ".$results_per_page." rows per page.";
    $total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
    $previous = $page -1;
    $next = $page +1;
    echo " Page ".$page."/".$total_pages." ";
    if ($page>1) {
        pageURL( $previous, 'previous', '&laquo; Previous' );
    }
    echo " |";
    if ($page<$total_pages) {
        pageURL( $next, 'next', 'Next &raquo;' );
    }
    echo "</p>";
}
?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
	    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
	    crossorigin="anonymous"></script>
    <footer>
      <script>
	const event = new Date(document.lastModified);
    document.write("The ClinVar data retrieved 2025-08-07. The gnomAD data retrieved 2025-05-09.");
    //document.write("This page was last modified: " + event.toISOString());
      </script>
    </footer>
    <script>
    function $(id) { return document.getElementById(id); }
    function show_all() {
        $('q').value = "";
        location.replace("index.php");
    }
    function handleClick(myRadio) {
        document.getElementById("filters").submit();
        // alert('New value: ' + myRadio.value);
    }

    function sgmshowhide() {
        const menu = document.getElementById('shmenu');
        menu.classList.toggle('container__menu--hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const menu = document.getElementById('shmenu');
        const table = document.getElementById('mainTable');
        const headers = [].slice.call(table.querySelectorAll('th'));
        const cells = [].slice.call(table.querySelectorAll('th, td'));
        const numColumns = headers.length;

        const thead = table.querySelector('thead');
        thead.addEventListener('contextmenu', function (e) {
            e.preventDefault();

            // const rect = thead.getBoundingClientRect();
            // const x = e.clientX - rect.left;
            // const y = e.clientY - rect.top;

            // menu.style.top = y + 'px';
            // menu.style.left = x + 'px';
            menu.classList.toggle('container__menu--hidden');

            document.addEventListener('click', documentClickHandler);
        });

        // Hide the menu when clicking outside of it
        const documentClickHandler = function (e) {
            const isClickedOutside = !menu.contains(e.target);
            if (isClickedOutside) {
                menu.classList.add('container__menu--hidden');
                document.removeEventListener('click', documentClickHandler);
            }
        };

        const showColumn = function (index) {
            cells.filter(function (cell) {
                return cell.getAttribute('data-column-index') === index + '';
            })
                .forEach(function (cell) {
                    cell.style.display = '';
                    cell.setAttribute('data-shown', 'true');
                });

            menu.querySelectorAll('[type="checkbox"][disabled]').forEach(function (checkbox) {
                checkbox.removeAttribute('disabled');
            });
        };

        const hideColumn = function (index) {
            cells.filter(function (cell) {
                return cell.getAttribute('data-column-index') === index + '';
            })
                .forEach(function (cell) {
                    cell.style.display = 'none';
                    cell.setAttribute('data-shown', 'false');
                });
            // How many columns are hidden
            const numHiddenCols = headers.filter(function (th) {
                return th.getAttribute('data-shown') === 'false';
            }).length;
            if (numHiddenCols === numColumns - 1) {
                // There's only one column which isn't hidden yet
                // We don't allow user to hide it
                const shownColumnIndex = thead
                    .querySelector('[data-shown="true"]')
                    .getAttribute('data-column-index');

                const checkbox = menu.querySelector(
                    '[type="checkbox"][data-column-index="' + shownColumnIndex + '"]'
                );
                checkbox.setAttribute('disabled', 'true');
            }
        };

        cells.forEach(function (cell, index) {
            cell.setAttribute('data-shown', 'true');
        });
        const checks = [].slice.call(menu.querySelectorAll('input'));

        checks.forEach(function (input) {
            // Handle the event
            const index = input.getAttribute('data-column-index');
            input.setAttribute('checked', 'true');
            input.addEventListener('change', function (e) {
                e.target.checked ? showColumn(index) : hideColumn(index);
                // menu.classList.add('container__menu--hidden');
            });
        });
    });
    function seChange(selectObj) {
	var idx = selectObj.selectedIndex;
	var which = selectObj.options[idx].value;
	var st = document.getElementById("q");
	if ( "cdna" == which ) {
	    st.placeholder = "Code (e.g. c.597C>A)";
	} else {
	    st.placeholder = "Name (e.g. N199K)";
	}
    }
    </script>
  </body>
<?php }
$stmt->close();
$conn->close();
?>
</html>
