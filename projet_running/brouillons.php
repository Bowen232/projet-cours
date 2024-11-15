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

// 获取用户的通知
$notificationFile = "notifications/{$user_id}.json";
$userNotifications = [];
if (file_exists($notificationFile)) {
    $userNotifications = json_decode(file_get_contents($notificationFile), true);
}

// 处理删除通知的请求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_notification'])) {
    $notificationIndex = intval($_POST['notification_index']);
    if (isset($userNotifications[$notificationIndex])) {
        unset($userNotifications[$notificationIndex]);
        // 重新索引数组
        $userNotifications = array_values($userNotifications);
        // 保存回文件
        file_put_contents($notificationFile, json_encode($userNotifications));
        $success = 'Notification supprimée avec succès.';
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
        /* 添加通知的样式 */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-message {
            flex: 1;
        }
        .notification-delete {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- 添加邮件图标 -->
    <a href="accueil_visitor.php" class="back-button">Retour</a>

    
    <div class="notification-icon">
        <a href="#" onclick="toggleNotifications(event)">
            <!-- 使用您喜欢的邮件图标 -->
            <img src="email_icon.png" alt="Notifications">
        </a>
        <!-- 通知消息弹出框 -->
        <div id="notificationPopup" class="notification-popup">
            <h3>Notifications</h3>
            <ul id="notificationList">
                <?php if (empty($userNotifications)): ?>
                    <li>Aucune nouvelle notification.</li>
                <?php else: ?>
                    <?php foreach ($userNotifications as $index => $notification): ?>
                        <li class="notification-item">
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <form method="post" action="" class="notification-delete">
                                <input type="hidden" name="notification_index" value="<?php echo $index; ?>">
                                <button type="submit" name="delete_notification" class="cancel-button">Supprimer</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- 您已有的页面内容 -->

    <script>
        function toggleNotifications(event) {
            event.preventDefault();
            var popup = document.getElementById('notificationPopup');
            if (popup.style.display === 'block') {
                popup.style.display = 'none';
            } else {
                popup.style.display = 'block';
            }
        }

        // 点击页面其他地方时，关闭通知弹出框
        window.onclick = function(event) {
            var popup = document.getElementById('notificationPopup');
            if (!event.target.closest('.notification-icon') && !event.target.closest('.notification-popup')) {
                popup.style.display = 'none';
            }
        }

        // 保持之前的代码
    </script>
</body>
</html>
