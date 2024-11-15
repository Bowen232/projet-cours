<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    // 销毁会话
    session_unset();
    session_destroy();
    // 重定向到登录页面
    header('Location: connect_administrateur.php');
    exit();
}

// 检查用户是否已登录且是管理员
if (!isset($_SESSION['user_id']) || $_SESSION['type_user'] !== 'admin') {
    // 未登录或不是管理员，重定向到登录页面
    header('Location: connect_administrateur.php');
    exit();
}
// 检查用户是否已登录且是管理员
if (!isset($_SESSION['user_id']) || $_SESSION['type_user'] !== 'admin') {
    // 未登录或不是管理员，重定向到登录页面
    header('Location: connect_administrateur.php');
    exit();
}

// 定义会话持续时间（以秒为单位），这里设置为一小时
$session_duration = 1800; // 1小时 = 3600秒

// 检查会话是否已过期
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $session_duration)) {
    // 会话已过期，销毁会话并重定向到登录页面
    session_unset();
    session_destroy();
    header('Location: connect_administrateur.php?session_expired=1');
    exit();
} else {
    // 会话未过期，更新登录时间，延长会话
    $_SESSION['login_time'] = time();
}
date_default_timezone_set('Europe/Paris');
include 'db_connect.php';

// 获取当前日期
$currentDate = date('Y-m-d');
$weekEndDate = date('Y-m-d', strtotime('+7 days'));

// 获取一周内的活动数量
$activityCount = 0;
if (isset($conn)) {
    $stmt = $conn->prepare("SELECT COUNT(*) as activity_count 
                            FROM Entrainement 
                            WHERE date >= ? AND date <= ?");
    $stmt->execute([$currentDate, $weekEndDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $activityCount = $result['activity_count'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_type'])) {
    $user_id = intval($_POST['id_user']);
    $new_type = $_POST['change_type'];

    // 验证新类型是否为 'admin' 或 'membre'
    if (in_array($new_type, ['admin', 'membre'])) {
        try {
            $stmt = $conn->prepare("UPDATE utilisateur SET type_user = ? WHERE id_user = ?");
            $stmt->execute([$new_type, $user_id]);
            echo "<script>alert('Type d\'utilisateur mis à jour avec succès.'); window.location.reload();</script>";
        } catch (PDOException $e) {
            echo "<script>alert('Erreur lors de la mise à jour : " . $e->getMessage() . "');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion de l'Association - Admin</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --background-color: #ecf0f1;
            --text-color: #333;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            height: 100vh;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            font-size: 1.2em;
        }

        .menu {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .menu li {
            margin-bottom: 5px;
        }

        .menu a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .menu a:hover, .menu a.active {
            background-color: var(--secondary-color);
        }

        .submenu {
            display: none;
            list-style-type: none;
            padding-left: 20px;
            background-color: rgba(0,0,0,0.1);
        }

        .submenu li a {
            padding: 8px 20px;
            font-size: 0.9em;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px 40px;
            overflow-y: auto;
            height: 100vh;
        }

        h1, h2 {
            color: var(--secondary-color);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin-top: 0;
            color: var(--accent-color);
        }

        .btn {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: var(--secondary-color);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .logout-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        .logout-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            Système de Gestion Admin
        </div>
        <ul class="menu">
            <li><a href="#dashboard" class="active">Tableau de bord</a></li>
            <li>
                <a href="#activites">Gestion des Activités</a>
                <ul class="submenu">
                    <li><a href="#liste-activites">Liste des activités</a></li>
                    <li><a href="#ajouter-activite">Ajouter une activité</a></li>
                    <li><a href="#modifier-activite">Modifier une activité</a></li>
                </ul>
            </li>
            <li><a href="#membres">Gestion des Membres</a></li>
            <li><a href="#inscriptions">Inscriptions</a></li>
            <li><a href="#photos">Gestion des Photos</a></li>
            <li><a href="#rapports">Rapports et Statistiques</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Tableau de Bord Administrateur</h1>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3>Activités à venir</h3>
                <p><?php echo htmlspecialchars($activityCount); ?> activités prévues cette semaine</p>
                <a href="gerer_activities.php" class="btn">Gérer les activités</a>
            </div>
            
            <div class="card">
                <h3>Nouvelles inscriptions</h3>
                <p>12 nouvelles inscriptions aujourd'hui</p>
                <a href="#" class="btn">Voir les détails</a>
            </div>
            
            <div class="card">
                <h3>Photos en attente</h3>
                <p>8 photos en attente de validation</p>
                <a href="#" class="btn">Modérer les photos</a>
            </div>
            
            <div class="card">
                <h3>Rapports mensuels</h3>
                <p>Le rapport de juin est prêt</p>
                <a href="#" class="btn">Générer le rapport</a>
            </div>
        </div>
        
        <h2>Liste de Toutes les Activités</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Activité</th>
                    <th>Lieu</th>
                    <th>Inscrits</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn)) {
                    echo "<tr><td colspan='5'>Erreur de connexion à la base de données.</td></tr>";
                } else {
                    $stmt = $conn->prepare("
                    SELECT e.id_entrainement, e.date, e.heure, e.titre, e.lieu, 
                        (SELECT COUNT(*) FROM Inscription WHERE id_entrainement = e.id_entrainement) as inscrits, 
                        e.participants_max 
                    FROM Entrainement e 
                    ORDER BY e.date DESC, e.heure DESC
                ");
                
                    $stmt->execute();

                        $currentDateTime = new DateTime();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $eventDateTime = new DateTime($row['date'] . ' ' . $row['heure']);
                        $status = '';

                        if ($eventDateTime > $currentDateTime) {
                            $status = 'À venir';
                        } else {
                            $status = 'Expiré';
                        }
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['date']) . " " . htmlspecialchars($row['heure']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['titre']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['lieu']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['inscrits']) . "/" . htmlspecialchars($row['participants_max']) . "</td>";
                        echo "<td>" . $status . "</td>";
                        echo "<td>
                                    <a href='gerer_activities.php" .  "' class='btn'>Modifier</a>
                                    <a href='details_activite.php?id=" . $row['id_entrainement'] . "' class='btn'>Voir les détails</a>
                              </td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
        <h3>Gestion des Membres</h3>
<table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 20px;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Type d'utilisateur</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // 获取所有用户信息
        $stmt = $conn->prepare("SELECT id_user, nom, email, type_user FROM utilisateur ORDER BY id_user ASC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 遍历用户列表
        foreach ($users as $user) {
            echo "<tr>
                <td>" . htmlspecialchars($user['id_user']) . "</td>
                <td>" . htmlspecialchars($user['nom']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td>" . htmlspecialchars($user['type_user']) . "</td>
                <td>
                    <form method='post' style='display: inline;'>
                        <input type='hidden' name='id_user' value='" . $user['id_user'] . "'>
                        <button type='submit' name='change_type' value='admin'>Admin</button>
                        <button type='submit' name='change_type' value='membre'>Membre</button>
                    </form>
                </td>
            </tr>";
        }
        ?>
    </tbody>
</table>

    </div>

    <script>
        // Toggle submenus
        document.querySelectorAll('.menu > li > a').forEach(item => {
            item.addEventListener('click', event => {
                const submenu = event.target.nextElementSibling;
                if (submenu && submenu.classList.contains('submenu')) {
                    submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                }
            });
        });
    </script>
    <form method="post" action="" style="display: inline;">
        <button type="submit" name="logout" class="logout-button">Se déconnecter</button>
    </form>
</body>
</html>
