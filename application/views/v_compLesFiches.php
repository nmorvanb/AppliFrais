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
			foreach( $mesFiches as $uneFiche)
			{
				$pdfLink = anchor('c_comptable/imprimeFiche/'.$uneFiche['id'], 'imprimer',  'title="Imprimer la fiche en PDF"');
				$signeLink = anchor('c_comptable/signeFiche/'.$uneFiche['id'], 'signer',  'title="Signer la fiche"  onclick="return confirm(\'Voulez-vous vraiment signer cette fiche ?\');"');
				$refuseLink = anchor('c_comptable/signeFiche/'.$uneFiche['id'], 'refuser',  'title="Refuser la fiche"  onclick="return confirm(\'Voulez-vous vraiment refuser cette fiche ?\');"');

				echo
				'<tr>
					<td class="date">'.anchor('c_comptable/voirFiche/'.$uneFiche['idVisiteur'].'/'.$uneFiche['id]', $uneFiche['id'],  'title="Consulter la fiche"').'</td>
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
