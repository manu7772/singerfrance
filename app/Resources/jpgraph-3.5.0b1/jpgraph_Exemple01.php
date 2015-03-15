<?php 
header('Content-Type: image/png');
// header('Content-type: application/png');
header('Content-Disposition: attachment; filename="image.png"');

include ("src/jpgraph.php");
include ("src/jpgraph_line.php");

$ydata1 = array(8,3,16,2,7,25,16);
$ydata2 = array(3,12,25,3,3,16,19);

// Creation du graphique
$graph = new Graph(500,300); 
$graph->SetScale("textlin");

// Création du système de points
$lineplot1 = new LinePlot($ydata1);
$lineplot2 = new LinePlot($ydata2);

$graph->img->SetMargin(40,20,20,40);
$graph->title->Set('Données de test pour JDN Dév.');
$graph->xaxis->title->Set('CA 2000 ');
$graph->yaxis->title->Set('Paramètre fictif... ');

// Antialiasing
$graph->img->SetAntiAliasing();
// On rajoute les points au graphique
$graph->Add($lineplot1);
$graph->Add($lineplot2);
// Affichage
$graph->Stroke();
?>