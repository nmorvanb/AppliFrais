<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A_comptable extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();

		// chargement du modèle d'accès aux données qui est utile à toutes les méthodes
		$this->load->model('dataAccess');
    }

	/**
	 * Accueil du visiteur
	 * La fonction intègre un mécanisme de contrôle d'existence des
	 * fiches de frais sur les 6 derniers mois.
	 * Si l'une d'elle est absente, elle est créée
	*/
	public function accueil()
	{	// TODO : Contrôler que toutes les valeurs de $unMois sont valides (chaine de caractère dans la BdD)

		// chargement du modèle contenant les fonctions génériques
		$this->load->model('functionsLib');

		// obtention de la liste des 6 derniers mois (y compris celui ci)
		$lesMois = $this->functionsLib->getSixDerniersMois();

		// obtention de l'id de l'utilisateur mémorisé en session
		$idVisiteur = $this->session->userdata('idUser');

		// contrôle de l'existence des 6 dernières fiches et création si nécessaire
		foreach ($lesMois as $unMois){
			if(!$this->dataAccess->ExisteFiche($idVisiteur, $unMois)) $this->dataAccess->creeFiche($idVisiteur, $unMois);
		}
		// envoie de la vue accueil du visiteur
		$this->templates->load('t_comptable', 'v_compAccueil');
	}

	/**
	 * Liste les fiches existantes du visiteur connecté et
	 * donne accès aux fonctionnalités associées
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $message : message facultatif destiné à notifier l'utilisateur du résultat d'une action précédemment exécutée
	*/
	public function lesFiches ()
	{

		$data['lesFiches'] = $this->dataAccess->getFichesVisiteurs();
		$this->templates->load('t_comptable', 'v_compLesFiches', $data);
	}

	/**
	 * Présente le détail de la fiche sélectionnée
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : le mois de la fiche à modifier
	*/
	public function voirFiche($idVisiteur, $mois)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
    $data['numAnnee'] = substr( $mois,0,4);
		$data['numMois'] = substr( $mois,4,2);
    $data['idVisiteur'] = $idVisiteur;
		$data['lesFraisHorsForfait'] = $this->dataAccess->getLesLignesHorsForfait($idVisiteur,$mois);
		$data['lesFraisForfait'] = $this->dataAccess->getLesLignesForfait($idVisiteur,$mois);

		$this->templates->load('t_comptable', 'v_compVoirListeFrais', $data);
	}

  /**
   * Valider une fiche
   *
   * @param $idVisiteur : l'id du visiteur de la fiche
   * @param $mois : le mois de la fiche à valider
  */
  public function valideFiche($idVisiteur, $mois)
  {
    $this->dataAccess->valideFiche($idVisiteur, $mois);
  }

  /**
   * Refuser une fiche
   *
   * @param $idVisiteur : l'id du visiteur de la fiche
   * @param $mois : le mois de la fiche à refuser
  */
  public function refuseFiche($idVisiteur, $mois)
  {
    $this->dataAccess->refuseFiche($idVisiteur, $mois);
  }

  //fonction permettant l'impression des fiches
	public function imprimeFiche($idVisiteur, $mois)
	{
		$lesFraisHorsForfait = $this->dataAccess->getLesLignesHorsForfait($idVisiteur, $mois);
		$lesFraisForfait = $this->dataAccess->getLesLignesForfait($idVisiteur, $mois);
    $totalFrais = $this->dataAccess->totalFiche($idVisiteur, $mois);

		require('application/fpdf/fpdf.php');

		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(0,0,'Fiche de frais du mois '.substr( $mois,4,2).'-'.substr( $mois,0,4).' de '.$this->session->userdata('prenom').' '.$this->session->userdata('nom').' :');
		$pdf->Ln();

    // Forfait
		$pdf->SetXY(10,25);
		$pdf->Cell(0,0,utf8_decode('Eléments forfaitisés :'));
		$pdf->SetXY(10,35);
    // Titre
		$titres = array(utf8_decode('Libellé'), utf8_decode('Quantité'));
		$w = array(90, 90);
		// En-tête
		for($i=0;$i<count($titres);$i++)
			$pdf->Cell($w[$i],7,$titres[$i],1,0,'C');
		$pdf->Ln();
		// Données
		foreach($lesFraisForfait as $row)
		{
			$pdf->Cell($w[0],6,utf8_decode($row['libelle']),'LR');
			$pdf->Cell($w[1],6,$row['quantite'],'LR');
			$pdf->Ln();
		}
		// Total et trait de terminaison
    $totalHF = 0;
    foreach($lesFraisHorsForfait as $row)
		{
			$totalHF += $row['montant'];
		}
    $pdf->Cell($w[0],6,'Total','LR');
    $pdf->Cell($w[1],6,$totalFrais-$totalHF.' euros','LR');
    $pdf->Ln();
    $pdf->Cell(array_sum($w),0,'','T');

		// Hors forfait
		$pdf->SetXY(10,85);
		$pdf->Cell(0,0,utf8_decode('Eléments hors forfait :'));
		$pdf->SetXY(10,95);
    // Titre
		$titres = array(utf8_decode('Libellé'), 'Date', 'Montant');
		$w = array(100, 40, 40);
		// En-tête
		for($i=0;$i<count($titres);$i++)
			$pdf->Cell($w[$i],7,$titres[$i],1,0,'C');
		$pdf->Ln();
		// Données et calcul du total

		foreach($lesFraisHorsForfait as $row)
		{
			$pdf->Cell($w[0],6,utf8_decode($row['libelle']),'LR');
			$pdf->Cell($w[1],6,$row['date'],'LR');
			$pdf->Cell($w[2],6,$row['montant'].' euros','LR');
			$pdf->Ln();
		}
		// Total et trait de terminaison
    $pdf->Cell($w[0],6,'Total','LR');
    $pdf->Cell($w[1],6,'Mois '.$mois,'LR');
    $pdf->Cell($w[1],6,$totalHF.' euros','LR');
    $pdf->Ln();
		$pdf->Cell(array_sum($w),0,'','T');

    // Total de la fiche
    $pdf->SetXY(10,155);
    $pdf->Cell(0,0,'Total des frais : '.$totalFrais.' euros');

		$pdf->Output('I','fiche_frais_'.$mois.'_'.$this->session->userdata('nom').'_'.$this->session->userdata('prenom').'.pdf');
	}
}
