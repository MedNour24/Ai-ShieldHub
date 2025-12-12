<?php
require_once __DIR__.'/../controller/TournoiC.php';

header('Content-Type: application/json');

// Upload configuration
$uploadDir = __DIR__.'/../uploads/tournaments/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$tournoiController = new TournoiC();
$response = ['success' => false, 'message' => '', 'data' => null];

// Function to handle image upload with proper path resolution
function handleImageUpload($uploadDir) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes) && $_FILES['image']['size'] <= 5242880) { // 5MB max
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'tournament_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                return '../uploads/tournaments/' . $fileName;
            }
        }
    }
    return null;
}

// Function to delete image file
function deleteImageFile($imagePath) {
    if ($imagePath) {
        // Handle both relative paths
        $fullPath = __DIR__.'/../' . str_replace('../', '', $imagePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}

// Function to get proper image path for frontend
function getImagePath($dbPath) {
    if (!$dbPath) return null;
    
    // If path already starts with ../, return as is
    if (strpos($dbPath, '../') === 0) {
        return $dbPath;
    }
    
    // If path starts with uploads/, add ../
    if (strpos($dbPath, 'uploads/') === 0) {
        return '../' . $dbPath;
    }
    
    return $dbPath;
}

try {
    // ADD TOURNAMENT
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $errors = $tournoiController->validerTournoi($_POST);
        
        if (!empty($errors)) {
            $response['message'] = implode(', ', $errors);
        } else {
            // Handle image upload
            $imagePath = handleImageUpload($uploadDir);
            
            $tournoi = new Tournoi(
                null,
                htmlspecialchars($_POST['nom']),
                htmlspecialchars($_POST['theme']),
                htmlspecialchars($_POST['description'] ?? ''),
                htmlspecialchars($_POST['niveau']),
                $_POST['date_debut'],
                $_POST['date_fin'],
                $imagePath
            );
            
            if ($tournoiController->ajouterTournoi($tournoi)) {
                $response['success'] = true;
                $response['message'] = 'Tournoi ajouté avec succès';
            } else {
                // Delete uploaded image if database insert fails
                if ($imagePath) {
                    deleteImageFile($imagePath);
                }
                $response['message'] = 'Erreur lors de l\'ajout du tournoi';
            }
        }
    }

    // UPDATE TOURNAMENT
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $errors = $tournoiController->validerTournoi($_POST);
        
        if (!isset($_POST['id'])) {
            $response['message'] = 'ID manquant';
        } elseif (!empty($errors)) {
            $response['message'] = implode(', ', $errors);
        } else {
            // Get current tournament to check for existing image
            $currentTournoi = $tournoiController->getTournoiById((int)$_POST['id']);
            $currentImagePath = $currentTournoi ? $currentTournoi->getImage() : null;
            $imagePath = null;
            
            // Priority 1: Check if user explicitly wants to remove the image
            if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
                // Delete old image if exists
                if ($currentImagePath) {
                    deleteImageFile($currentImagePath);
                }
                $imagePath = null; // Set to null to remove from database
            }
            // Priority 2: Check for new image upload
            elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $newImagePath = handleImageUpload($uploadDir);
                if ($newImagePath) {
                    // Delete old image if exists
                    if ($currentImagePath) {
                        deleteImageFile($currentImagePath);
                    }
                    $imagePath = $newImagePath;
                } else {
                    // Keep current image if upload failed
                    $imagePath = $currentImagePath;
                }
            }
            // Priority 3: Keep current image (no changes)
            else {
                $imagePath = $currentImagePath;
            }
            
            $tournoi = new Tournoi(
                (int)$_POST['id'],
                htmlspecialchars($_POST['nom']),
                htmlspecialchars($_POST['theme']),
                htmlspecialchars($_POST['description'] ?? ''),
                htmlspecialchars($_POST['niveau']),
                $_POST['date_debut'],
                $_POST['date_fin'],
                $imagePath
            );
            
            if ($tournoiController->modifierTournoi($tournoi)) {
                $response['success'] = true;
                $response['message'] = 'Tournoi modifié avec succès';
            } else {
                $response['message'] = 'Erreur lors de la modification';
            }
        }
    }

    // DELETE TOURNAMENT
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'supprimer') {
        if (!isset($_POST['id'])) {
            $response['message'] = 'ID manquant';
        } else {
            // Get tournament to find image path
            $tournoi = $tournoiController->getTournoiById((int)$_POST['id']);
            
            if ($tournoiController->supprimerTournoi((int)$_POST['id'])) {
                // Delete image file if exists
                if ($tournoi && $tournoi->getImage()) {
                    deleteImageFile($tournoi->getImage());
                }
                $response['success'] = true;
                $response['message'] = 'Tournoi supprimé avec succès';
            } else {
                $response['message'] = 'Erreur lors de la suppression';
            }
        }
    }

    // GET SINGLE TOURNAMENT
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $tournoi = $tournoiController->getTournoiById((int)$_GET['id']);
        if ($tournoi) {
            $response['success'] = true;
            $response['data'] = [
                'id' => $tournoi->getId(),
                'nom' => $tournoi->getNom(),
                'theme' => $tournoi->getTheme(),
                'description' => $tournoi->getDescription(),
                'niveau' => $tournoi->getNiveau(),
                'date_debut' => $tournoi->getDateDebut(),
                'date_fin' => $tournoi->getDateFin(),
                'image' => getImagePath($tournoi->getImage())
            ];
        } else {
            $response['message'] = 'Tournoi non trouvé';
        }
    }

    // GET ALL TOURNAMENTS
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $tournois = $tournoiController->afficherTournois();
        $response['success'] = true;
        $response['data'] = array_map(function($t) {
            return [
                'id' => $t->getId(),
                'nom' => $t->getNom(),
                'theme' => $t->getTheme(),
                'description' => $t->getDescription(),
                'niveau' => $t->getNiveau(),
                'date_debut' => $t->getDateDebut(),
                'date_fin' => $t->getDateFin(),
                'image' => getImagePath($t->getImage())
            ];
        }, $tournois);
    }

} catch (Exception $e) {
    $response['message'] = 'Erreur serveur: ' . $e->getMessage();
}

echo json_encode($response);
?>