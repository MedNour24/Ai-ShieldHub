<?php
require_once __DIR__.'/../controller/TournoiC.php';

// Handle AJAX requests
header('Content-Type: application/json');

$tournoiController = new TournoiC();
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    // CREATE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $errors = $tournoiController->validerTournoi($_POST);
        
        if (!empty($errors)) {
            $response['message'] = implode(', ', $errors);
        } else {
            $tournoi = new Tournoi(
                null,
                htmlspecialchars($_POST['nom']),
                htmlspecialchars($_POST['theme']),
                htmlspecialchars($_POST['niveau']),
                $_POST['date_debut'],
                $_POST['date_fin']
            );
            
            if ($tournoiController->ajouterTournoi($tournoi)) {
                $response['success'] = true;
                $response['message'] = 'Tournoi ajouté avec succès';
            } else {
                $response['message'] = 'Erreur lors de l\'ajout du tournoi';
            }
        }
    }

    // UPDATE
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $errors = $tournoiController->validerTournoi($_POST);
        
        if (!isset($_POST['id'])) {
            $response['message'] = 'ID manquant';
        } elseif (!empty($errors)) {
            $response['message'] = implode(', ', $errors);
        } else {
            $tournoi = new Tournoi(
                (int)$_POST['id'],
                htmlspecialchars($_POST['nom']),
                htmlspecialchars($_POST['theme']),
                htmlspecialchars($_POST['niveau']),
                $_POST['date_debut'],
                $_POST['date_fin']
            );
            
            if ($tournoiController->modifierTournoi($tournoi)) {
                $response['success'] = true;
                $response['message'] = 'Tournoi modifié avec succès';
            } else {
                $response['message'] = 'Erreur lors de la modification';
            }
        }
    }

    // DELETE
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
        if (!isset($_POST['id'])) {
            $response['message'] = 'ID manquant';
        } else {
            if ($tournoiController->supprimerTournoi((int)$_POST['id'])) {
                $response['success'] = true;
                $response['message'] = 'Tournoi supprimé avec succès';
            } else {
                $response['message'] = 'Erreur lors de la suppression';
            }
        }
    }

    // READ ONE
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $tournoi = $tournoiController->getTournoiById((int)$_GET['id']);
        if ($tournoi) {
            $response['success'] = true;
            $response['data'] = [
                'id' => $tournoi->getId(),
                'nom' => $tournoi->getNom(),
                'theme' => $tournoi->getTheme(),
                'niveau' => $tournoi->getNiveau(),
                'date_debut' => $tournoi->getDateDebut(),
                'date_fin' => $tournoi->getDateFin()
            ];
        } else {
            $response['message'] = 'Tournoi non trouvé';
        }
    }

    // READ ALL
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $tournois = $tournoiController->afficherTournois();
        $response['success'] = true;
        $response['data'] = array_map(function($t) {
            return [
                'id' => $t->getId(),
                'nom' => $t->getNom(),
                'theme' => $t->getTheme(),
                'niveau' => $t->getNiveau(),
                'date_debut' => $t->getDateDebut(),
                'date_fin' => $t->getDateFin()
            ];
        }, $tournois);
    }

} catch (Exception $e) {
    $response['message'] = 'Erreur serveur: ' . $e->getMessage();
}

echo json_encode($response);
?>