<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A_visiteur extends CI_Model {

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
		$this->templates->load('t_visiteur', 'v_visAccueil');
	}

	/**
	 * Liste les fiches existantes du visiteur connecté et
	 * donne accès aux fonctionnalités associées
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $message : message facultatif destiné à notifier l'utilisateur du résultat d'une action précédemment exécutée
	*/
	public function mesFiches ($idVisiteur, $message=null)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session

		$idVisiteur = $this->session->userdata('idUser');

		$data['notify'] = $message;
		$data['mesFiches'] = $this->dataAccess->getFiches($idVisiteur);
		$this->templates->load('t_visiteur', 'v_visMesFiches', $data);
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
		$data['lesFraisHorsForfait'] = $this->dataAccess->getLesLignesHorsForfait($idVisiteur,$mois);
		$data['lesFraisForfait'] = $this->dataAccess->getLesLignesForfait($idVisiteur,$mois);

		$this->templates->load('t_visiteur', 'v_visVoirListeFrais', $data);
	}

	/**
	 * Présente le détail de la fiche sélectionnée et donne
	 * accés à la modification du contenu de cette fiche.
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : le mois de la fiche à modifier
	 * @param $message : message facultatif destiné à notifier l'utilisateur du résultat d'une action précédemment exécutée
	*/
	public function modFiche($idVisiteur, $mois, $message=null)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session

		$data['notify'] = $message;
		$data['numAnnee'] = substr( $mois,0,4);
		$data['numMois'] = substr( $mois,4,2);
		$data['lesFraisHorsForfait'] = $this->dataAccess->getLesLignesHorsForfait($idVisiteur,$mois);
		$data['lesFraisForfait'] = $this->dataAccess->getLesLignesForfait($idVisiteur,$mois);

		$this->templates->load('t_visiteur', 'v_visModListeFrais', $data);
	}

	/**
	 * Signe une fiche de frais en changeant son état
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : le mois de la fiche à signer
	*/
	public function signeFiche($idVisiteur, $mois)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
		// TODO : intégrer une fonctionnalité d'impression PDF de la fiche

	    $this->dataAccess->signeFiche($idVisiteur, $mois);
	}

	/**
	 * Modifie les quantités associées aux frais forfaitisés dans une fiche donnée
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : le mois de la fiche concernée
	 * @param $lesFrais : les quantités liées à chaque type de frais, sous la forme d'un tableau
	*/
	public function majForfait($idVisiteur, $mois, $lesFrais)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
		// TODO : valider les données contenues dans $lesFrais ...

		$this->dataAccess->majLignesForfait($idVisiteur,$mois,$lesFrais);
		$this->dataAccess->recalculeMontantFiche($idVisiteur,$mois);
	}

	/**
	 * Ajoute une ligne de frais hors forfait dans une fiche donnée
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : le mois de la fiche concernée
	 * @param $lesFrais : les quantités liées à chaque type de frais, sous la forme d'un tableau
	*/
	public function ajouteFrais($idVisiteur, $mois, $uneLigne)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session
		// TODO : valider la donnée contenues dans $uneLigne ...
        $this->load->model('functionsLib');

        $dateFrais = $uneLigne['dateFrais'];
		    $libelle = $uneLigne['libelle'];
		    $montant = $uneLigne['montant'];

        $moisdate = $this->functionsLib->estMoisValide($dateFrais);
        // Vérification montant en valeur numérique et date correct a l'aide des fonctions dans functionsLib.
        if ($montant <= 0)
        {
          $montant = "";
          return 0;
        }
        else {
          if($moisdate != $mois) {
            return 0;

          }
          else {
            //actualisation du montant de la date sur la page de liste fiche.
            $this->dataAccess->creeLigneHorsForfait($idVisiteur,$mois,$libelle,$dateFrais,$montant);
            $this->dataAccess->recalculeMontantFiche($idVisiteur,$mois);
            return 1;
          }
        }
	}

	/**
	 * Supprime une ligne de frais hors forfait dans une fiche donnée
	 *
	 * @param $idVisiteur : l'id du visiteur
	 * @param $mois : le mois de la fiche concernée
	 * @param $idLigneFrais : l'id de la ligne à supprimer
	*/
	public function supprLigneFrais($idVisiteur, $mois, $idLigneFrais)
	{	// TODO : s'assurer que les paramètres reçus sont cohérents avec ceux mémorisés en session et cohérents entre eux

	  $this->dataAccess->supprimerLigneHorsForfait($idLigneFrais);
      $this->dataAccess->recalculeMontantFiche($idVisiteur,$mois);
	}

  //fonction permettant l'impression des fiches. 
	public function imprimeFiche($idVisiteur, $mois)
	{
		$lesFraisHorsForfait = $this->dataAccess->getLesLignesHorsForfait($idVisiteur,$mois);
		$lesFraisForfait = $this->dataAccess->getLesLignesForfait($idVisiteur,$mois);

		require('application/fpdf/fpdf.php');

		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(0,0,'Fiche de frais du mois '.substr( $mois,4,2).'-'.substr( $mois,0,4).' :');
		$pdf->Ln();
		$pdf->SetXY(10,25);
		$pdf->Cell(0,0,utf8_decode('Eléments forfaitisés :'));
		$pdf->SetXY(10,35);

		// forfait
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
		// Trait de terminaison
		$pdf->Cell(array_sum($w),0,'','T');

		// hors forfait
		$pdf->SetXY(10,85);
		$pdf->Cell(0,0,utf8_decode('Eléments hors forfait :'));
		$pdf->SetXY(10,95);

		$titres = array(utf8_decode('Libellé'), 'Date', 'Montant');
		$w = array(100, 40, 40);
		// En-tête
		for($i=0;$i<count($titres);$i++)
			$pdf->Cell($w[$i],7,$titres[$i],1,0,'C');
		$pdf->Ln();
		// Données
		foreach($lesFraisHorsForfait as $row)
		{
			$pdf->Cell($w[0],6,utf8_decode($row['libelle']),'LR');
			$pdf->Cell($w[1],6,$row['date'],'LR');
			$pdf->Cell($w[2],6,$row['montant'],'LR');
			$pdf->Ln();
		}
		// Trait de terminaison
		$pdf->Cell(array_sum($w),0,'','T');

		$pdf->Output('D','fiche_frais_'.$mois.'.pdf');
	}
}
