<?php
// 开始会话，以便检查用户的登录状态
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- 您的原始 HTML 头部内容 -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Running de l'Esigelec</title>
    <style>
        /* 您的原始 CSS 样式 */
        /* ... 您提供的样式代码 ... */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #628baa;
        }
        header, footer {
            background-color: #fff;
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .logo {
            display: inline-block;
            vertical-align: middle;
        }
        .logo img {
            height: 50px;
        }
        nav {
            display: inline-block;
            vertical-align: middle;
            float: right;
        }
        nav a {
            color: #628baa;
            text-decoration: none;
            margin-left: 20px;
        }
        .sub-menu {
            background-color: #f8d7b5;
            padding: 10px 0;
            text-align: center;
            display: none;
            position: absolute;
            width: 100%;
            left: 0;
            z-index: 1000;
        }
        .sub-menu a {
            color: #628baa;
            text-decoration: none;
            margin: 0 20px;
            padding: 5px 10px;
            display: inline-block;
        }
        .sub-menu a:hover {
            background-color: #e0c0a0;
        }
        .hero {
            background-color: #fff;
            padding: 40px 0;
            text-align: center;
        }
        .hero p {
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .training-section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 40px;
        }
        .training-card {
            width: 48%;
            position: relative;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .training-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .training-card:hover img {
            transform: scale(1.1);
        }
        .training-card .overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            overflow: hidden;
            width: 100%;
            height: 0;
            transition: .5s ease;
        }
        .training-card:hover .overlay {
            height: 100%;
        }
        .training-card .text {
            color: white;
            font-size: 20px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 90%;
        }
        .training-card h2 {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: #fff;
            margin: 0;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .see-more {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px;
            background-color: #ccc;
            color: #333;
            text-decoration: none;
            margin-top: 20px;
        }
        footer {
            margin-top: 40px;
            padding: 20px 0;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .qr-code img {
            width: 100px;
            height: 100px;
        }
        .address {
            text-align: right;
        }
        .footer-logos img {
            height: 50px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="logo/Logo_ESIGELEC.svg.png" alt="ESIGELEC logo">
            </div>
            <nav>
                <a href="#">Accueil</a>
                <a href="#" id="about-running">À propos de Running</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account_detail.php">Déjà connecté</a>
                <?php else: ?>
                    <a href="connect_visitor.php">Se connecter</a>
                <?php endif; ?>
                <a href="#">Contact - Nous</a>
            </nav>
        </div>
    </header>

    <div class="sub-menu" id="sub-menu">
        <div class="container">
            <a href="#">Présentation de l'Association</a>
            <a href="#">Récompenses et Honneurs</a>
        </div>
    </div>

    <main>
        <section class="hero">
            <div class="container">
                <p>Running de l'Esigelec vous permet de vous entraîner avec vos camarades et anciens élèves, afin de favoriser une vie plus saine et un équilibre à la fois physique et mental. Rejoignez notre communauté pour des sessions d'entraînement en groupe et améliorez votre condition physique tout en renforçant vos liens sociaux.</p>
            </div>
        </section>

        <section class="container training-section">
            <!-- 示例训练卡片 -->
            <div class="training-card">
    <a href="detail_entrainement.php?id=3">
        <img src="title images/running9.jpg" alt="Cours de Yoga en Plein Air">
        <div class="overlay">
            <div class="text">Séance de yoga relaxante en plein air</div>
        </div>
        <h2>Cours de Yoga</h2>
    </a>
</div>


<div class="training-card">
    <a href="detail_entrainement.php?id=4">
        <img src="title images/running8.jpg" alt="Entraînement de Basketball">
        <div class="overlay">
            <div class="text">Entraînement intensif de basketball pour tous les niveaux</div>
        </div>
        <h2>Entraînement de Basketball</h2>
    </a>
</div>


<div class="training-card">
    <a href="detail_entrainement.php?id=5">
        <img src="title images/running7.jpg" alt="Match de Football">
        <div class="overlay">
            <div class="text">Match de football amical pour les passionnés</div>
        </div>
        <h2>Match de Football</h2>
    </a>
</div>


<div class="training-card">
    <a href="detail_entrainement.php?id=6">
        <img src="title images/running.jpg" alt="Entraînement de Natation">
        <div class="overlay">
            <div class="text">Session de natation pour améliorer votre endurance</div>
        </div>
        <h2>Entraînement de Natation</h2>
    </a>
</div>


<div class="training-card">
    <a href="detail_entrainement.php?id=7">
        <img src="title images/running5.jpg" alt="Sortie à Vélo">
        <div class="overlay">
            <div class="text">Sortie à vélo en groupe dans la campagne</div>
        </div>
        <h2>Sortie à Vélo</h2>
    </a>
</div>


<div class="training-card">
    <a href="detail_entrainement.php?id=8">
        <img src="title images/running10.jpg" alt="Entraînement de Tennis">
        <div class="overlay">
            <div class="text">Entraînement de tennis pour débutants et intermédiaires</div>
        </div>
        <h2>Entraînement de Tennis</h2>
    </a>
</div>


            <!-- 您可以根据实际情况添加更多训练卡片 -->
            <a href="#" class="see-more">Voir plus...</a>
        </section>
    </main>

    <footer>
        <div class="container footer-content">
            <div class="qr-code">
                <img src="QRcode/esigelecsite.png" alt="QR Code">
                <p>Site Officiel de l'école</p>
            </div>
            <div class="address">
                <p>Technopôle du Madrillet</p>
                <p>Avenue Galilée - BP 10024</p>
                <p>76801 Saint-Etienne du Rouvray Cedex</p>
            </div>
            <div class="footer-logos">
                <img src="logo/Logo_ESIGELEC.svg.png" alt="ESIGELEC logo">
                <img src="logo/runninglogo.png" alt="Running Sports Club logo">
            </div>
        </div>
    </footer>

    <script>
        const aboutRunning = document.getElementById('about-running');
        const subMenu = document.getElementById('sub-menu');

        let isSubMenuVisible = false;

        function showSubMenu() {
            subMenu.style.display = 'block';
            isSubMenuVisible = true;
        }

        function hideSubMenu() {
            subMenu.style.display = 'none';
            isSubMenuVisible = false;
        }

        aboutRunning.addEventListener('mouseover', showSubMenu);
        aboutRunning.addEventListener('focus', showSubMenu);

        document.addEventListener('click', (e) => {
            if (!subMenu.contains(e.target) && e.target !== aboutRunning) {
                hideSubMenu();
            }
        });

        subMenu.addEventListener('mouseleave', (e) => {
            if (!aboutRunning.contains(e.relatedTarget)) {
                hideSubMenu();
            }
        });

        // 确保子菜单链接可以被点击
        subMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.stopPropagation();
                console.log('Clicked:', link.textContent);
                // 这里可以添加导航逻辑
                hideSubMenu();
            });
        });

        // 添加键盘访问性
        aboutRunning.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (isSubMenuVisible) {
                    hideSubMenu();
                } else {
                    showSubMenu();
                }
            }
        });
    </script>
</body>
</html>
