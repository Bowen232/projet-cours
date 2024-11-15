<?php
// 包含数据库连接文件
include 'db_connect.php';

// 开始会话
session_start();

// 初始化错误消息
$error = '';

// 检查是否存在会话过期的参数
if (isset($_GET['session_expired'])) {
    $error = 'Votre session a expiré. Veuillez vous reconnecter.';
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取表单数据，并进行基本的输入清理
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // 准备查询，检查用户是否存在（根据用户名）
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE nom = :nom");
        $stmt->execute(['nom' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 验证密码
            if (password_verify($password, $user['mot_de_passe'])) {
                // 检查用户类型是否为 admin
                if ($user['type_user'] === 'admin') {
                    // 登录成功，存储用户信息到会话
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['username'] = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['type_user'] = $user['type_user'];

                    // 存储登录时间
                    $_SESSION['login_time'] = time();

                    // 重定向到 accueil_administrateur.php
                    header('Location: accueil_administrateur.php');
                    exit();
                } else {
                    $error = 'Accès refusé. Vous n\'êtes pas un administrateur.';
                }
            } else {
                $error = 'Mot de passe incorrect.';
            }
        } else {
            $error = 'Aucun compte trouvé avec ce nom d\'utilisateur.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la connexion : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- 保留您的原始 HTML 头部内容 -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Running ESIGELEC - Admin Login</title>
    <style>
        /* 保留您的原始 CSS 样式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #0047AB;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .login-box {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 300px;
            position: relative; /* 为了定位返回按钮 */
        }
        
        h1 {
            text-align: center;
            color: #0047AB;
            margin-bottom: 30px;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px 20px;
            margin-bottom: 20px;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #0047AB;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        /* 错误消息的样式 */
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }

        /* 返回按钮的样式 */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #0047AB;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #003080;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <!-- 添加返回按钮 -->
        <a href="accueil_visitor.php" class="back-button">Retour</a>

        <h1>Running ESIGELEC</h1>

        <!-- 显示错误消息 -->
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se Connecter</button>
        </form>
    </div>
</body>
</html>
