<?php
	$this->load->helper('url');
?>
<div id="contenu">
	<h2>Liste des fiches de frais</h2>

	<?php if(!empty($notify)) echo '<p id="notify" >'.$notify.'</p>';?>

	<table class="listeLegere">
		<thead>
			<tr>
				<th >Visiteur</th>
				<th >Montant</th>
				<th >Date modif.</th>
				<th  colspan="3">Actions</th>
			</tr>
		</thead>
		<tbody>

		<?php
			foreach( $lesFiches as $uneFiche)
			{
				$pdfLink = anchor('c_comptable/imprimeFiche/'.$uneFiche['idVisiteur'].'/'.$uneFiche['mois'], 'imprimer',  'title="Imprimer la fiche en PDF"');
				$valideLink = anchor('c_comptable/valideFiche/'.$uneFiche['idVisiteur'].'/'.$uneFiche['mois'], 'valider',  'title="Valider la fiche"  onclick="return confirm(\'Voulez-vous vraiment valider cette fiche ?\');"');
				$refuseLink = anchor('c_comptable/refuseFiche/'.$uneFiche['idVisiteur'].'/'.$uneFiche['mois'], 'refuser',  'title="Refuser la fiche"  onclick="return confirm(\'Voulez-vous vraiment refuser cette fiche ?\');"');

				echo
				'<tr>
					<td class="date">'.anchor('c_comptable/voirFiche/'.$uneFiche['idVisiteur'].'/'.$uneFiche['mois'], $uneFiche['mois'],  'title="Consulter la fiche"').'</td>
					<td class="montant">'.$uneFiche['montantValide'].'</td>
					<td class="date">'.$uneFiche['dateModif'].'</td>
					<td class="action">'.$valideLink.'</td>
					<td class="action">'.$refuseLink.'</td>
					<td class="action">'.$pdfLink.'</td>
				</tr>';
			}
		?>
		</tbody>
    </table>

</div>
