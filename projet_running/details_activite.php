<?php
// 开始会话并检查管理员权限
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['type_user'] !== 'admin') {
    header('Location: connect_administrateur.php');
    exit();
}

// 包含数据库连接文件
include 'db_connect.php';

// 检查是否传递了活动ID
if (!isset($_GET['id'])) {
    echo "Aucun ID d'activité fourni.";
    exit();
}

$activity_id = intval($_GET['id']);

try {
    // 获取活动的详细信息
    $stmt = $conn->prepare("SELECT * FROM entrainement WHERE id_entrainement = :id");
    $stmt->execute(['id' => $activity_id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) {
        echo "Activité non trouvée.";
        exit();
    }

    // 获取已报名用户列表
    $stmt = $conn->prepare("
        SELECT u.id_user, u.nom, u.email
        FROM inscription i
        JOIN utilisateur u ON i.id_user = u.id_user
        WHERE i.id_entrainement = :id
    ");
    $stmt->execute(['id' => $activity_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur lors de la récupération des données : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'Activité</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f6fa;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 800px;
            padding: 20px;
            margin: 20px;
        }
        
        .header {
            display: flex;
            align-items: center;
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border-radius: 8px 8px 0 0;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.5em;
            flex: 1;
        }
        
        .activity-details, .participants-section {
            padding: 20px;
        }

        .activity-details h2 {
            font-size: 1.3em;
            color: #007bff;
            margin-bottom: 10px;
        }

        .activity-details p, .participants-section p {
            margin: 5px 0;
        }

        .participants-section h3 {
            font-size: 1.2em;
            color: #333;
            margin-top: 20px;
        }
        
        .participants-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .participants-table th, .participants-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .participants-table th {
            background-color: #f0f0f0;
            color: #333;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Détails de l'Activité</h1>
        </div>
        <div class="activity-details">
            <h2><?php echo htmlspecialchars($activity['titre']); ?></h2>
            <p><strong>Date :</strong> <?php echo htmlspecialchars($activity['date']); ?></p>
            <p><strong>Heure :</strong> <?php echo htmlspecialchars($activity['heure']); ?></p>
            <p><strong>Lieu :</strong> <?php echo htmlspecialchars($activity['lieu']); ?></p>
            <p><strong>Description :</strong> <?php echo htmlspecialchars($activity['description']); ?></p>
            <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($activity['categorie']); ?></p>
            <p><strong>Participants Max :</strong> <?php echo htmlspecialchars($activity['participants_max']); ?></p>
        </div>
        
        <div class="participants-section">
            <h3>Liste des Participants</h3>
            <?php if (empty($participants)): ?>
                <p>Aucun participant pour cette activité.</p>
            <?php else: ?>
                <table class="participants-table">
                    <tr>
                        <th>ID Utilisateur</th>
                        <th>Nom</th>
                        <th>Email</th>
                    </tr>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($participant['id_user']); ?></td>
                            <td><?php echo htmlspecialchars($participant['nom']); ?></td>
                            <td><?php echo htmlspecialchars($participant['email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
        
        <a href="accueil_administrateur.php" class="back-button">← Retour à la liste des activités</a>
    </div>
</body>
</html>
