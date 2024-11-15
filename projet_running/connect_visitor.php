<?php
// 包含数据库连接文件
include 'db_connect.php';

// 开始会话
session_start();

// 初始化错误和成功消息
$error = '';
$success = '';

// 获取重定向参数
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// 如果用户已经登录，检查是否有重定向参数
if (isset($_SESSION['user_id'])) {
    if (!empty($redirect)) {
        header("Location: $redirect");
    } else {
        header('Location: accueil_visitor.php');
    }
    exit();
}

// 处理登录和注册表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // 处理登录
        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            // 准备查询，检查用户是否存在（根据邮箱）
            $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 验证密码
                if (password_verify($password, $user['mot_de_passe'])) {
                    // 登录成功，存储用户信息到会话
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['username'] = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['type_user'] = $user['type_user'];

                    // 重定向到之前的页面或主页
                    if (!empty($redirect)) {
                        header("Location: $redirect");
                    } else {
                        header('Location: accueil_visitor.php');
                    }
                    exit();
                } else {
                    $error = 'Mot de passe incorrect.';
                }
            } else {
                $error = 'Aucun compte trouvé avec cet email.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la connexion : ' . $e->getMessage();
        }
    } elseif (isset($_POST['register'])) {
        // 处理注册
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // 检查密码是否匹配
        if ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            try {
                // 检查邮箱是否已被使用
                $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    $error = 'Cet email est déjà utilisé.';
                } else {
                    // 默认用户类型为 'membre'
                    $type_user = 'membre';

                    // 插入新用户
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO utilisateur (nom, email, mot_de_passe, type_user) VALUES (:nom, :email, :mot_de_passe, :type_user)");
                    $stmt->execute([
                        'nom' => $username,
                        'email' => $email,
                        'mot_de_passe' => $hashed_password,
                        'type_user' => $type_user
                    ]);
                    $success = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
                }
            } catch (PDOException $e) {
                $error = 'Erreur lors de la création du compte : ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- 您的原始 HTML 头部内容 -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface de Connexion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        /* 您的原始 CSS 样式 */
        /* ... 您提供的样式代码 ... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            position: relative; /* 为了定位返回按钮 */
        }

        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.5rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
        }

        .tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1rem;
            color: #666;
        }

        .tab.active {
            color: #1a73e8;
            border-bottom: 2px solid #1a73e8;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        .input-group {
            position: relative;
            margin-bottom: 1rem;
        }

        .input-group i:not(.password-toggle) {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .input-group input:focus {
            outline: none;
            border-color: #1a73e8;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            background: none;
            border: none;
            padding: 0;
            font-size: 1rem;
        }

        .password-toggle:hover {
            color: #1a73e8;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        .remember-forgot a {
            color: #1a73e8;
            text-decoration: none;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button[type="submit"]:hover {
            background-color: #1557b0;
        }

        /* 添加错误和成功消息的样式 */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
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
    </style>
</head>
<body>
    <!-- 添加返回按钮 -->
    <a href="accueil_visitor.php" class="back-button">Retour</a>

    <div class="container">
        <!-- 显示错误或成功消息 -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="header">
            <h1>Bienvenue</h1>
            <p>Connectez-vous ou créez un compte</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('login')">Connexion</button>
            <button class="tab" onclick="switchTab('register')">Inscription</button>
        </div>

        <div id="login-form" class="form-container active">
            <form method="post" action="">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="password-input" placeholder="Mot de passe" required>
                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>
                    <a href="#">Mot de passe oublié ?</a>
                </div>
                <button type="submit" name="login">Se connecter</button>
            </form>
        </div>

        <div id="register-form" class="form-container">
            <form method="post" action="">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="password-input" placeholder="Mot de passe" required>
                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" class="password-input" placeholder="Confirmer le mot de passe" required>
                    <button type="button" class="password-toggle" onclick="togglePassword(this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <button type="submit" name="register">S'inscrire</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.form-container').forEach(f => f.classList.remove('active'));
            
            if (tab === 'login') {
                document.querySelector('.tab:first-child').classList.add('active');
                document.getElementById('login-form').classList.add('active');
            } else {
                document.querySelector('.tab:last-child').classList.add('active');
                document.getElementById('register-form').classList.add('active');
            }
        }

        function togglePassword(button) {
            const input = button.parentElement.querySelector('.password-input');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
