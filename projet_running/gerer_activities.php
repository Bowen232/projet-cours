<?php
include 'db_connect.php';

$message = ""; // Used to store feedback messages

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['add_activity'])) {
            // Add new activity
            $titre = $_POST['titre'];
            $description = $_POST['description'];
            $categorie = $_POST['categorie'];
            $date = $_POST['date'];
            $heure = $_POST['heure'];
            $lieu = $_POST['lieu'];
            $participants_max = $_POST['participants_max'];
            $cree_par = 1; // Current admin ID, ensure this user exists in utilisateur table

            $sql = "INSERT INTO entrainement (titre, description, categorie, date, heure, lieu, participants_max, cree_par)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$titre, $description, $categorie, $date, $heure, $lieu, $participants_max, $cree_par]);

            $activityId = $conn->lastInsertId();

            // Handle image uploads (up to 4 photos)
            for ($i = 0; $i < 4; $i++) {
                if (isset($_FILES['chemin_image']['name'][$i]) && $_FILES['chemin_image']['error'][$i] == 0) {
                    $file = $_FILES['chemin_image'];
                    $allowedMimeTypes = ['image/jpeg'];
                    $maxFileSize = 2.5 * 1024 * 1024; // 2.5MB

                    // Check file type
                    if (!in_array($file['type'][$i], $allowedMimeTypes)) {
                        throw new Exception('Le fichier doit être au format JPEG.');
                    }

                    // Check file size
                    if ($file['size'][$i] > $maxFileSize) {
                        throw new Exception('La taille de l\'image doit être inférieure à 2,5 Mo.');
                    }

                    // Define file storage path
                    $uploadDir = 'image_entrainement/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Generate a unique file name to avoid conflicts
                    $fileName = uniqid() . '-' . basename($file['name'][$i]);
                    $uploadPath = $uploadDir . $fileName;

                    // Move file to the specified directory
                    if (!move_uploaded_file($file['tmp_name'][$i], $uploadPath)) {
                        throw new Exception('Erreur lors du téléchargement de l\'image.');
                    }

                    // Insert photo record into database
                    $sqlPhoto = "INSERT INTO photo (id_entrainement, url_photo) VALUES (?, ?)";
                    $stmtPhoto = $conn->prepare($sqlPhoto);
                    $stmtPhoto->execute([$activityId, $uploadPath]);
                }
            }

            $message = "Nouvelle activité ajoutée avec succès!";
        } elseif (isset($_POST['edit_activity'])) {
            // Edit activity
            $id = $_POST['id_entrainement'];
            $titre = $_POST['titre'];
            $description = $_POST['description'];
            $categorie = $_POST['categorie'];
            $date = $_POST['date'];
            $heure = $_POST['heure'];
            $lieu = $_POST['lieu'];
            $participants_max = $_POST['participants_max'];

            $sql = "UPDATE entrainement SET titre = ?, description = ?, categorie = ?, date = ?, heure = ?, lieu = ?, participants_max = ?
                    WHERE id_entrainement = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$titre, $description, $categorie, $date, $heure, $lieu, $participants_max, $id]);

            // Handle new image uploads (up to 4 photos)
            for ($i = 0; $i < 4; $i++) {
                if (isset($_FILES['chemin_image']['name'][$i]) && $_FILES['chemin_image']['error'][$i] == 0) {
                    $file = $_FILES['chemin_image'];
                    $allowedMimeTypes = ['image/jpeg'];
                    $maxFileSize = 2.5 * 1024 * 1024; // 2.5MB

                    // Check file type
                    if (!in_array($file['type'][$i], $allowedMimeTypes)) {
                        throw new Exception('Le fichier doit être au format JPEG.');
                    }

                    // Check file size
                    if ($file['size'][$i] > $maxFileSize) {
                        throw new Exception('La taille de l\'image doit être inférieure à 2,5 Mo.');
                    }

                    // Define file storage path
                    $uploadDir = 'image_entrainement/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Generate a unique file name to avoid conflicts
                    $fileName = uniqid() . '-' . basename($file['name'][$i]);
                    $uploadPath = $uploadDir . $fileName;

                    // Move file to the specified directory
                    if (!move_uploaded_file($file['tmp_name'][$i], $uploadPath)) {
                        throw new Exception('Erreur lors du téléchargement de l\'image.');
                    }

                    // Insert photo record into database
                    $sqlPhoto = "INSERT INTO photo (id_entrainement, url_photo) VALUES (?, ?)";
                    $stmtPhoto = $conn->prepare($sqlPhoto);
                    $stmtPhoto->execute([$id, $uploadPath]);
                }
            }

            $message = "Activité mise à jour avec succès!";
        } elseif (isset($_POST['cancel_activity'])) {
            // Cancel activity
            $id = $_POST['id_entrainement'];
            $stmt = $conn->prepare("SELECT * FROM entrainement WHERE id_entrainement = ?");
            $stmt->execute([$id]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($activity) {
                // 在 description 字段前添加 [ANNULÉ]
                $newDescription = '[ANNULÉ] ' . $activity['description'];
                $stmt = $conn->prepare("UPDATE entrainement SET description = ? WHERE id_entrainement = ?");
                $stmt->execute([$newDescription, $id]);

                $message = "Activité annulée avec succès ! Les participants seront notifiés.";
            } else {
                $message = "Activité non trouvée.";
            }
        } elseif (isset($_POST['delete_activity'])) {
            // Delete activity
            $id = $_POST['id_entrainement'];

            try {
                // Start transaction
                $conn->beginTransaction();

                // Delete inscriptions
                $stmt = $conn->prepare("DELETE FROM inscription WHERE id_entrainement = ?");
                $stmt->execute([$id]);

                // Delete photos and photo records
                $stmtPhotos = $conn->prepare("SELECT url_photo FROM photo WHERE id_entrainement = ?");
                $stmtPhotos->execute([$id]);
                $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

                foreach ($photos as $photo) {
                    if (file_exists($photo['url_photo'])) {
                        unlink($photo['url_photo']);
                    }
                }

                $stmt = $conn->prepare("DELETE FROM photo WHERE id_entrainement = ?");
                $stmt->execute([$id]);

                // Delete the activity
                $stmt = $conn->prepare("DELETE FROM entrainement WHERE id_entrainement = ?");
                $stmt->execute([$id]);

                // Commit transaction
                $conn->commit();

                $message = "Activité supprimée avec succès!";
            } catch (PDOException $e) {
                $conn->rollBack();
                $message = "Erreur lors de la suppression de l'activité : " . $e->getMessage();
            }
        } elseif (isset($_POST['delete_photo'])) {
            // Delete single photo
            $photoId = $_POST['id_photo'];
            $sql = "SELECT url_photo FROM photo WHERE id_photo = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$photoId]);
            $photo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($photo) {
                // Delete photo file
                if (file_exists($photo['url_photo'])) {
                    unlink($photo['url_photo']);
                }

                // Delete photo record from database
                $sql = "DELETE FROM photo WHERE id_photo = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$photoId]);

                $message = "Photo supprimée avec succès!";
            }
        }
    }
} catch (PDOException $e) {
    $message = "Erreur: " . $e->getMessage();
} catch (Exception $e) {
    $message = "Erreur: " . $e->getMessage();
}

// Query all activities
$stmt = $conn->query("SELECT * FROM entrainement");
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Activités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container my-5">
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Add return to main page button -->
    <div class="mb-3">
        <a href="accueil_administrateur.php" class="btn btn-secondary">&larr; Retour au Tableau de Bord</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Gérer les Activités</h2>
        </div>
        <div class="card-body">
            <h3 id="form-title">Ajouter une Activité</h3>
            <form method="post" action="" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="add_activity" value="1" id="form-action">
                <input type="hidden" name="id_entrainement" id="activity-id">
                <div class="col-md-6">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" name="titre" id="titre" required>
                </div>
                <div class="col-md-6">
                    <label for="categorie" class="form-label">Catégorie</label>
                    <input type="text" class="form-control" name="categorie" id="categorie" required>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" id="date" required>
                </div>
                <div class="col-md-4">
                    <label for="heure" class="form-label">Heure</label>
                    <input type="time" class="form-control" name="heure" id="heure" required>
                </div>
                <div class="col-md-4">
                    <label for="lieu" class="form-label">Lieu</label>
                    <input type="text" class="form-control" name="lieu" id="lieu" required>
                </div>
                <div class="col-md-12">
                    <label for="chemin_image" class="form-label">Photos (JPEG, Max 2.5MB, 4 photos max)</label>
                    <input type="file" class="form-control" name="chemin_image[]" id="chemin_image" accept="image/jpeg" multiple>
                    <small class="text-muted">Veuillez télécharger jusqu'à quatre photos en JPEG, chaque taille ne doit pas dépasser 2,5 Mo.</small>
                </div>
                <div class="col-md-6">
                    <label for="participants_max" class="form-label">Participants Max</label>
                    <input type="number" class="form-control" name="participants_max" id="participants_max" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" id="form-submit">Ajouter l'Activité</button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity list -->
    <div class="card mt-5">
        <div class="card-header">
            <h3>Liste des Activités</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Lieu</th>
                        <th>Participants Max</th>
                        <th>Photos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['titre']); ?></td>
                            <td><?php echo htmlspecialchars($activity['description']); ?></td>
                            <td><?php echo htmlspecialchars($activity['categorie']); ?></td>
                            <td><?php echo htmlspecialchars($activity['date']); ?></td>
                            <td><?php echo htmlspecialchars($activity['heure']); ?></td>
                            <td><?php echo htmlspecialchars($activity['lieu']); ?></td>
                            <td><?php echo htmlspecialchars($activity['participants_max']); ?></td>
                            <td>
                                <?php
                                $stmtPhotos = $conn->prepare("SELECT * FROM photo WHERE id_entrainement = ?");
                                $stmtPhotos->execute([$activity['id_entrainement']]);
                                $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($photos as $photo):
                                ?>
                                    <div>
                                        <img src="<?php echo htmlspecialchars($photo['url_photo']); ?>" alt="Photo" style="max-width: 100px; height: auto;">
                                        <form method="post" action="" style="display:inline-block;">
                                            <input type="hidden" name="id_photo" value="<?php echo $photo['id_photo']; ?>">
                                            <input type="submit" name="delete_photo" value="Supprimer" class="btn btn-danger btn-sm">
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="editActivity(<?php echo htmlspecialchars(json_encode($activity)); ?>)">Modifier</button>
                                <form method="post" action="" style="display:inline-block;">
                                    <input type="hidden" name="id_entrainement" value="<?php echo $activity['id_entrainement']; ?>">
                                    <input type="submit" name="cancel_activity" value="Annuler" class="btn btn-warning btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette activité ?');">
                                </form>
                                <form method="post" action="" style="display:inline-block;">
                                    <input type="hidden" name="id_entrainement" value="<?php echo $activity['id_entrainement']; ?>">
                                    <input type="submit" name="delete_activity" value="Supprimer" class="btn btn-danger btn-sm" onclick="return confirm('ATTENTION: Cette action supprimera définitivement l\'activité. Continuer ?');">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // JavaScript function to edit activity
    function editActivity(activity) {
        document.getElementById('form-title').innerText = 'Modifier l\'Activité';
        document.getElementById('form-action').name = 'edit_activity';
        document.getElementById('activity-id').value = activity.id_entrainement;
        document.getElementById('titre').value = activity.titre;
        document.getElementById('description').value = activity.description.replace('[ANNULÉ] ', '');
        document.getElementById('categorie').value = activity.categorie;
        document.getElementById('date').value = activity.date;
        document.getElementById('heure').value = activity.heure;
        document.getElementById('lieu').value = activity.lieu;
        document.getElementById('participants_max').value = activity.participants_max;
        document.getElementById('form-submit').innerText = 'Mettre à Jour l\'Activité';
    }

    function resetForm() {
        document.getElementById('form-title').innerText = 'Ajouter une Activité';
        document.getElementById('form-action').name = 'add_activity';
        document.getElementById('activity-id').value = '';
        document.getElementById('titre').value = '';
        document.getElementById('description').value = '';
        document.getElementById('categorie').value = '';
        document.getElementById('date').value = '';
        document.getElementById('heure').value = '';
        document.getElementById('lieu').value = '';
        document.getElementById('participants_max').value = '';
        document.getElementById('form-submit').innerText = 'Ajouter l\'Activité';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
