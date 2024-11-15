<?php
// 包含数据库连接文件
include 'db_connect.php';

// 开始会话
session_start();

// 处理登出请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    // 销毁会话
    session_unset();
    session_destroy();
    // 重定向到登录页面或主页
    header('Location: accueil_visitor.php');
    exit();
}

// 设置默认时区
date_default_timezone_set('Europe/Paris');

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    // 如果未登录，重定向到 accueil_visitor.php
    header('Location: accueil_visitor.php');
    exit();
}

// 获取用户ID
$user_id = $_SESSION['user_id'];

// 初始化错误和成功消息
$error = '';
$success = '';

// 处理取消报名表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel'])) {
    $entrainement_id = intval($_POST['id_entrainement']);

    try {
        // 开始事务
        $conn->beginTransaction();

        // 删除报名记录
        $stmt = $conn->prepare("DELETE FROM inscription WHERE id_user = :id_user AND id_entrainement = :id_entrainement");
        $stmt->execute(['id_user' => $user_id, 'id_entrainement' => $entrainement_id]);

        // 提交事务
        $conn->commit();

        $success = 'Inscription annulée avec succès.';
    } catch (PDOException $e) {
        // 如果事务失败，回滚
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = 'Erreur lors de l\'annulation de l\'inscription : ' . $e->getMessage();
    }
}
// 处理删除历史记录请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_history'])) {
    try {
        // 开始事务
        $conn->beginTransaction();

        // 删除用户的历史记录
        $stmt = $conn->prepare("
            DELETE FROM inscription
            WHERE id_user = :id_user AND id_entrainement IN (
                SELECT id_entrainement FROM entrainement WHERE CONCAT(date, ' ', heure) < NOW() - INTERVAL 30 MINUTE
            )
        ");
        $stmt->execute(['id_user' => $user_id]);

        // 提交事务
        $conn->commit();

        $success = 'Historique supprimé avec succès.';
    } catch (PDOException $e) {
        // 如果事务失败，回滚
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = 'Erreur lors de la suppression de l\'historique : ' . $e->getMessage();
    }
}
// 获取用户已报名的活动
try {
    $stmt = $conn->prepare("
        SELECT e.*
        FROM inscription i
        JOIN entrainement e ON i.id_entrainement = e.id_entrainement
        WHERE i.id_user = :id_user
        ORDER BY e.date DESC, e.heure DESC
    ");
    $stmt->execute(['id_user' => $user_id]);
    $user_entrainements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Erreur lors de la récupération des inscriptions : ' . $e->getMessage();
    exit();
}

// 分离活动为 "Déjà Rejoigné" 和 "Historique"
$already_joined = [];
$history = [];
$current_time = time();

foreach ($user_entrainements as $entrainement) {
    $entrainement_datetime = strtotime($entrainement['date'] . ' ' . $entrainement['heure']);
    if ($current_time > $entrainement_datetime + 1800) { // 超过开始时间半小时
        $history[] = $entrainement;
    } else {
        $already_joined[] = $entrainement;
    }
}

// 获取用户已报名的被取消的活动
$canceled_activities = [];
foreach ($user_entrainements as $entrainement) {
    if (strpos($entrainement['description'], '[ANNULÉ]') === 0) {
        $canceled_activities[] = $entrainement;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte</title>
    <style>
        /* 您的原始 CSS 样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            position: relative; /* 为了定位返回按钮 */
        }
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative; /* 为了定位返回按钮 */
        }
        .profile-header {
            background-color: #4A90E2;
            color: white;
            padding: 20px;
            position: relative;
        }
        .profile-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .user-type {
            font-size: 14px;
            opacity: 0.9;
        }
        .profile-content {
            padding: 20px;
        }
        .info-group {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .info-group:last-child {
            border-bottom: none;
        }
        .info-label {
            width: 200px;
            color: #666;
            font-size: 14px;
        }
        .info-value {
            flex: 1;
            color: #333;
            font-size: 16px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .edit-button {
            background-color: #4A90E2;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .edit-button:hover {
            background-color: #357ABD;
        }
        .logout-button:hover {
            background-color: #c0392b;
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #4A90E2;
            margin-bottom: 15px;
        }
        /* 弹出框的样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }
        .close-button {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        .close-button:hover {
            color: #000;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            color: #666;
            margin-bottom: 5px;
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        /* 消息的样式 */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        /* 返回按钮的样式 */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #4A90E2;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #357ABD;
        }

        /* 新增栏目样式 */
        .section {
            margin-top: 40px;
        }
        .section h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .section .entrainement-list {
            list-style: none;
            padding: 0;
        }
        .section .entrainement-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section .entrainement-details {
            flex: 1;
            margin-right: 20px;
        }
        .section .entrainement-details h4 {
            margin-bottom: 5px;
            color: #4A90E2;
        }
        .section .entrainement-details p {
            margin-bottom: 5px;
            color: #666;
        }
        .section .cancel-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .section .cancel-button:hover {
            background-color: #c0392b;
        }

        /* 通知图标样式 */
        .notification-icon {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .notification-icon img {
            width: 30px;
            height: 30px;
            cursor: pointer;
        }

        /* 通知弹出框样式 */
        .notification-popup {
            display: none;
            position: absolute;
            top: 60px;
            right: 20px;
            width: 300px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px;
            z-index: 1000;
        }

        .notification-popup h3 {
            margin-top: 0;
        }

        .notification-popup ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notification-popup li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .notification-popup li:last-child {
            border-bottom: none;
        }

        /* 已取消活动的样式 */
        .canceled {
            background-color: #f8d7da;
        }

        .canceled-badge {
            background-color: #dc3545;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 12px;
        }

        .canceled-text {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- 添加返回按钮 -->
    <a href="accueil_visitor.php" class="back-button">Retour</a>

    <!-- 添加邮件图标 -->
    <div class="notification-icon">
        <a href="#" onclick="toggleNotifications(event)">
            <!-- 使用您喜欢的邮件图标 -->
            <img src="logo/email_icon.png" alt="Notifications">
        </a>
        <!-- 通知消息弹出框 -->
        <div id="notificationPopup" class="notification-popup">
            <h3>Notifications</h3>
            <ul id="notificationList">
                <!-- 通知消息将通过JavaScript插入 -->
            </ul>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
            <h1>Mon Compte</h1>
            <div class="user-type"><?php echo htmlspecialchars($_SESSION['type_user']); ?></div>
        </div>
        <div class="profile-content">
            <!-- 显示错误或成功消息 -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="info-group">
                <div class="info-label">ID d'utilisateur</div>
                <div class="info-value"><?php echo htmlspecialchars($user_id); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Nom d'utilisateur</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Email</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
            </div>
            <div class="info-group">
                <div class="info-label">Type de compte</div>
                <div class="info-value"><?php echo htmlspecialchars($_SESSION['type_user']); ?></div>
            </div>
            <div class="button-group">
                <button class="edit-button" onclick="openModal()">Modifier les informations</button>
                <form method="post" action="" style="display: inline;">
                    <button type="submit" name="logout" class="logout-button">Se déconnecter</button>
                </form>
            </div>

            <!-- 已经加入的活动 -->
            <div class="section">
                <h2>Déjà Rejoigné</h2>
                <?php if (empty($already_joined)): ?>
                    <p>Aucune inscription en cours.</p>
                <?php else: ?>
                    <ul class="entrainement-list">
                        <?php foreach ($already_joined as $entrainement): ?>
                            <?php
                            $entrainement_id = $entrainement['id_entrainement'];
                            $titre = htmlspecialchars($entrainement['titre']);
                            $date = date('d M Y', strtotime($entrainement['date']));
                            $heure = date('H:i', strtotime($entrainement['heure']));
                            $lieu = htmlspecialchars($entrainement['lieu']);
                            $isCanceled = strpos($entrainement['description'], '[ANNULÉ]') === 0;
                            ?>
                            <li class="entrainement-item <?php echo $isCanceled ? 'canceled' : ''; ?>">
                                <div class="entrainement-details">
                                    <h4><?php echo $titre; ?> <?php if ($isCanceled) echo '<span class="badge canceled-badge">Annulé</span>'; ?></h4>
                                    <p>Date: <?php echo $date; ?></p>
                                    <p>Heure: <?php echo $heure; ?></p>
                                    <p>Lieu: <?php echo $lieu; ?></p>
                                </div>
                                <?php if (!$isCanceled): ?>
                                    <form method="post" action="">
                                        <input type="hidden" name="id_entrainement" value="<?php echo $entrainement_id; ?>">
                                        <button type="submit" name="cancel" class="cancel-button">Annuler</button>
                                    </form>
                                <?php else: ?>
                                    <span class="canceled-text">Cet événement a été annulé.</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- 历史活动 -->
            <div class="section">
                <h2>Historique</h2>
                <?php if (empty($history)): ?>
                    <p>Aucune activité historique.</p>
                <?php else: ?>
                    <!-- 添加“删除历史记录”按钮 -->
                    <form method="post" action="">
                        <button type="submit" name="delete_history" class="cancel-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre historique ? Cette action est irréversible.');">Supprimer l'historique</button>
                    </form>
                    <ul class="entrainement-list">
                        <?php foreach ($history as $entrainement): ?>
                            <?php
                            $titre = htmlspecialchars($entrainement['titre']);
                            $date = date('d M Y', strtotime($entrainement['date']));
                            $heure = date('H:i', strtotime($entrainement['heure']));
                            $lieu = htmlspecialchars($entrainement['lieu']);
                            ?>
                            <li class="entrainement-item">
                                <div class="entrainement-details">
                                    <h4><?php echo $titre; ?></h4>
                                    <p>Date: <?php echo $date; ?></p>
                                    <p>Heure: <?php echo $heure; ?></p>
                                    <p>Lieu: <?php echo $lieu; ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 弹出框，用于编辑个人信息 -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <h2>Modifier les informations</h2>
            <form method="post" action="">
                <div class="input-group">
                    <label for="nom">Nom d'utilisateur</label>
                    <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                </div>
                <!-- 如果您不希望用户修改账户类型，可以隐藏或移除以下字段 -->
                <div class="input-group">
                    <label for="type_user">Type de compte</label>
                    <select name="type_user" id="type_user" disabled>
                        <option value="membre" <?php if ($_SESSION['type_user'] == 'membre') echo 'selected'; ?>>Membre</option>
                    </select>
                </div>
                <button type="submit" name="update_info" class="edit-button">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <!-- 添加通知弹出框的 JavaScript 代码 -->
    <script>
        // 获取被取消的活动列表（从 PHP 传递过来）
        var canceledActivities = <?php echo json_encode($canceled_activities); ?>;

        function toggleNotifications(event) {
            event.preventDefault();
            var popup = document.getElementById('notificationPopup');
            if (popup.style.display === 'block') {
                popup.style.display = 'none';
            } else {
                popup.style.display = 'block';

                // 清空通知列表
                var list = document.getElementById('notificationList');
                list.innerHTML = '';

                if (canceledActivities.length === 0) {
                    var li = document.createElement('li');
                    li.textContent = 'Aucune nouvelle notification.';
                    list.appendChild(li);
                } else {
                    canceledActivities.forEach(function(activity) {
                        var li = document.createElement('li');
                        li.innerHTML = '<strong>' + activity.titre + '</strong> a été annulé.';
                        list.appendChild(li);
                    });
                }
            }
        }

        // 点击页面其他地方时，关闭通知弹出框
        window.onclick = function(event) {
            var popup = document.getElementById('notificationPopup');
            if (!event.target.closest('.notification-icon') && !event.target.closest('.notification-popup')) {
                popup.style.display = 'none';
            }
        }

        // 打开弹出框
        function openModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        // 关闭弹出框
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // 点击弹出框外部区域关闭弹出框
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
