<?php
	$this->load->helper('url');
?>
<div id="contenu">
	<h2>Liste de mes fiches de frais</h2>

	<?php if(!empty($notify)) echo '<p id="notify" >'.$notify.'</p>';?>

	<table class="listeLegere">
		<thead>
			<tr>
				<th >Mois</th>
				<th >Etat</th>
				<th >Montant</th>
				<th >Date modif.</th>
				<th  colspan="3">Actions</th>
			</tr>
		</thead>
		<tbody>

		<?php
			foreach( $mesFiches as $uneFiche)
			{
				$modLink = '';
				$signeLink = '';
				$pdfLink = '';

				$dateNow = date("Ym");
				$dateLimit = "";
				if(date("m")==12) {
					$dateLimit = date("Y")."01";
				}
				else {
					$dateLimit = (date("Y")-1).(date("m")+1);
				}
				if($dateLimit <= $uneFiche['mois'] && $uneFiche['mois'] <= $dateNow) {
					$pdfLink = anchor('c_visiteur/imprimeFiche/'.$uneFiche['mois'], 'imprimer',  'title="Imprimer la fiche en PDF"');
				}

				if ($uneFiche['id'] == 'CR') {
					$modLink = anchor('c_visiteur/modFiche/'.$uneFiche['mois'], 'modifier',  'title="Modifier la fiche"');
					$signeLink = anchor('c_visiteur/signeFiche/'.$uneFiche['mois'], 'signer',  'title="Signer la fiche"  onclick="return confirm(\'Voulez-vous vraiment signer cette fiche ?\');"');
				}

				echo
				'<tr>
					<td class="date">'.anchor('c_visiteur/voirFiche/'.$uneFiche['mois'], $uneFiche['mois'],  'title="Consulter la fiche"').'</td>
					<td class="libelle">'.$uneFiche['libelle'].'</td>
					<td class="montant">'.$uneFiche['montantValide'].'</td>
					<td class="date">'.$uneFiche['dateModif'].'</td>
					<td class="action">'.$modLink.'</td>
					<td class="action">'.$signeLink.'</td>
					<td class="action">'.$pdfLink.'</td>
				</tr>';
			}
		?>
		</tbody>
    </table>

</div>
