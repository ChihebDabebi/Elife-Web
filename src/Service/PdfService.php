<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;



use App\Entity\Facture;
class PdfService
{
    private $domPdf;

    public function __construct() {
        $this->domPdf = new Dompdf();

        // Options de DOMPDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'helvetica'); // Utiliser la même police que dans TCPDF
        $pdfOptions->set('isPhpEnabled', true); // Activer l'exécution du PHP dans le HTML
        $pdfOptions->set('isHtml5ParserEnabled', true); // Activer le support HTML5

        // Définir la taille de la page (A4 par défaut, vous pouvez ajuster selon vos besoins)
        $pdfOptions->set('defaultPaperSize', 'A4'); 

        $this->domPdf->setOptions($pdfOptions);
    }

    // Méthode pour afficher un PDF dans le navigateur
    public function showPdfFile($html) {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        $this->domPdf->stream("Facture.pdf", [
            'Attachment' => true
        ]);
    }

    // Méthode pour générer un PDF binaire
    public function generateBinaryPDF($html) {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }
    
    public function generateInvoicePDF(Facture $facture): string
{
    // Calcul du montant de la taxe (20%)
    $taxe = $facture->getMontant() * 0.20;

    $html = '<html>';
    $html .= '<head>';
    $html .= '<style>';
    $html .= '
        /* Styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; /* Police neutre et professionnelle */
            background-color: #f7f7f7; /* Couleur de fond douce */
            color: #555; /* Couleur de texte standard */
            font-size: 12px; /* Taille de police de base */
        }
        .container {
            width: 100%;
            max-width: 800px; /* Largeur maximale pour la lisibilité */
            margin: 40px auto; /* Centrage avec un espace généreux */
            background-color: #fff; /* Fond blanc pour la facture */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Ombre discrète pour le contraste */
            padding: 20px; /* Espace intérieur */
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 30px; 
        }
        .header h1, .footer h2 {
            color: #333; /* Couleur de texte plus forte pour les titres */
        }
        .section {
            margin-bottom: 15px; /* Espace entre les sections */
        }
        .section h2 {
            font-size: 16px; /* Taille de police pour les sous-titres */
            margin-bottom: 5px; /* Espace après les sous-titres */
            color: #333; /* Couleur de texte pour les sous-titres */
            border-bottom: 1px solid #eaeaea; /* Ligne de séparation subtile */
            padding-bottom: 5px; /* Espace après la ligne de séparation */
        }
        .section p {
            margin-bottom: 10px; /* Espace après les paragraphes */
        }
        .payment-info, .tax-info {
            background-color: #f9f9f9; /* Fond différent pour les sections spéciales */
            padding: 10px; /* Espace intérieur pour les sections spéciales */
            border: 1px solid #eaeaea; /* Bordure subtile pour les sections spéciales */
            margin-bottom: 20px; /* Espace après les sections spéciales */
        }
        .tax-info {
            background-color: #f0f0f0; /* Fond légèrement différent pour la section des taxes */
        }
        .footer {
            font-size: 10px; /* Taille de police plus petite pour le pied de page */
            color: #999; /* Couleur de texte discrète pour le pied de page */
        }
    ';
    $html .= '</style>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<div class="container">';
    $html .= '<div class="header">';
    $html .= '<h1>Facture</h1>';
    $html .= '</div>';
   // ... Code précédent ...

// Début de la section des informations de la facture
$html .= '<div class="section">';
$html .= '<h2>Informations sur la facture</h2>';
$html .= '<p>Numéro de la facture: ' . $facture->getNumFacture() . '</p>';
$html .= '<p>Type de la facture: ' . $facture->getType() . '</p>';
$html .= '<p>Date: ' . $facture->getDate()->format('Y-m-d') . '</p>';
$html .= '<p>Consommation: ' . $facture->getConsommation() . '</p>';
$html .= '<p>Montant: ' . $facture->getMontant() . '</p>';
$html .= '</div>'; // Fin de la section des informations de la facture

// Début de la section des informations de paiement
$html .= '<div class="section payment-info">';
$html .= '<h2>Informations de paiement</h2>';
$html .= '<p>Numéro de la carte: **** **** **** 4242</p>'; // Masquer les numéros pour la sécurité
$html .= '<p>Code Securie: ***</p>'; // Masquer les numéros pour la sécurité

$html .= '<p>Date d\'expiration: 12/34</p>';
$html .= '<p>Code postal: 123456</p>';
$html .= '</div>'; // Fin de la section des informations de paiement

// Ajout du montant de la taxe dans une nouvelle section
$html .= '<div class="section tax-info">';
$html .= '<h2>Détails de la taxe</h2>';
$html .= '<p>Taxe (20%): ' . $taxe . '</p>';

// Calcul du montant total (montant + taxe)
$montantTotal = $facture->getMontant() + $taxe;
$html .= '<p>Montant total: ' . $montantTotal . '</p>';

$html .= '</div>'; // Fin de la section des détails de la taxe

// ... Code suivant ...

    $html .= '</div>';
    $html .= '<div class="footer">';
    $html .= '<p>Merci pour votre paiement!</p>'; // Message de remerciement
    $html .= '<p>WirelessSolution</p>'; // Message de remerciement

    $html .= '<p>Généré le: ' . date('Y-m-d H:i:s') . '</p>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</body>';
    $html .= '</html>';

    // Génération du PDF à partir du HTML
    $this->domPdf->loadHtml($html);
    $this->domPdf->render();

    // Retourne le contenu PDF
    return $this->domPdf->output();
}

}
   
