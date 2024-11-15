<?php
// 开始会话
session_start();

// 包含数据库连接文件
include 'db_connect.php';

// 设置默认时区
date_default_timezone_set('Europe/Paris');

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    // 如果未登录，重定向到 connect_visitor.php，并传递重定向参数
    header("Location: connect_visitor.php?redirect=detail_entrainement.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 初始化消息变量
$success = '';
$error = '';

// 处理报名表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll'])) {
    $entrainement_id = intval($_POST['id_entrainement']);

    try {
        // 检查用户是否已报名该活动
        $stmt = $conn->prepare("SELECT * FROM inscription WHERE id_user = :id_user AND id_entrainement = :id_entrainement");
        $stmt->execute(['id_user' => $user_id, 'id_entrainement' => $entrainement_id]);
        $isEnrolled = $stmt->fetch();

        if ($isEnrolled) {
            $error = 'Vous êtes déjà inscrit à cet entraînement.';
        } else {
            // 检查活动是否已满员
            $stmt = $conn->prepare("SELECT participants_max FROM entrainement WHERE id_entrainement = :id_entrainement");
            $stmt->execute(['id_entrainement' => $entrainement_id]);
            $entrainement = $stmt->fetch();

            if ($entrainement) {
                $participants_max = $entrainement['participants_max'];
                if ($participants_max !== null) {
                    // 获取当前报名人数
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM inscription WHERE id_entrainement = :id_entrainement");
                    $stmt->execute(['id_entrainement' => $entrainement_id]);
                    $count = $stmt->fetch();
                    $current_count = $count['count'];

                    if ($current_count >= $participants_max) {
                        $error = 'Cet entraînement est complet.';
                    } else {
                        // 开始事务
                        $conn->beginTransaction();

                        // 插入报名记录
                        $stmt = $conn->prepare("INSERT INTO inscription (id_entrainement, id_user) VALUES (:id_entrainement, :id_user)");
                        $stmt->execute(['id_entrainement' => $entrainement_id, 'id_user' => $user_id]);

                        // 提交事务
                        $conn->commit();

                        $success = 'Inscription réussie à l\'entraînement.';
                    }
                } else {
                    // 没有参与人数限制
                    // 开始事务
                    $conn->beginTransaction();

                    // 插入报名记录
                    $stmt = $conn->prepare("INSERT INTO inscription (id_entrainement, id_user) VALUES (:id_entrainement, :id_user)");
                    $stmt->execute(['id_entrainement' => $entrainement_id, 'id_user' => $user_id]);

                    // 提交事务
                    $conn->commit();

                    $success = 'Inscription réussie à l\'entraînement.';
                }
            } else {
                $error = 'Entraînement non trouvé.';
            }
        }
    } catch (PDOException $e) {
        // 如果事务失败，回滚
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $error = 'Erreur lors de l\'inscription : ' . $e->getMessage();
    }
}

// 获取所有活动信息
try {
    $stmt = $conn->query("SELECT * FROM entrainement ORDER BY date ASC, heure ASC");
    $entrainements = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Parcours des Cours d'Entraînement</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lightbox CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <style>
        .training-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }
        
        .training-card {
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .training-card:hover {
            transform: translateY(-5px);
        }
        
        .card-image {
            position: relative;
            padding-top: 60%;
            overflow: hidden;
        }
        
        .card-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .card-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .card-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #555;
        }
        
        .detail-item i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .photo-gallery img {
            width: 80px;
            height: auto;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }
        
        .photo-gallery img:hover {
            transform: scale(1.05);
        }
        
        .enroll-button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .enroll-button:hover {
            background-color: #0056b3;
        }
        
        .search-filters {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filter-select {
            width: 200px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-left: 1rem;
        }

        /* 返回按钮的样式 */
        .back-button {
            position: fixed;
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
            z-index: 1000;
        }
        .back-button:hover {
            background-color: #357ABD;
        }

        /* 消息样式 */
        .message {
            margin: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- 添加返回按钮 -->
    <a href="accueil_visitor.php" class="back-button">Retour</a>

    <header class="header py-4">
        <div class="container">
            <h1 class="text-center mb-4">Parcours des Cours d'Entraînement</h1>
            <div class="search-filters d-flex justify-content-center align-items-center">
                <input type="text" class="form-control w-auto" placeholder="Rechercher un cours...">
                <select class="form-select w-auto ms-3">
                    <option value="">Toutes les catégories</option>
                    <?php
                    $stmt_categories = $conn->prepare("SELECT DISTINCT categorie FROM entrainement WHERE categorie IS NOT NULL AND categorie != ''");
                    $stmt_categories->execute();
                    $categories = $stmt_categories->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- 显示成功或错误消息 -->
        <?php if ($success): ?>
            <div class="alert alert-success message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="training-grid">
            <?php foreach ($entrainements as $entrainement): ?>
                <?php
                $titre = htmlspecialchars($entrainement['titre']);
                $description = htmlspecialchars($entrainement['description']);
                $categorie = htmlspecialchars($entrainement['categorie']);
                $lieu = htmlspecialchars($entrainement['lieu']);
                $participants_max = !empty($entrainement['participants_max']) ? intval($entrainement['participants_max']) : null;

                // 获取活动的照片
                $stmtPhotos = $conn->prepare("SELECT * FROM photo WHERE id_entrainement = ?");
                $stmtPhotos->execute([$entrainement['id_entrainement']]);
                $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

                $date = strtotime($entrainement['date']);
                $heure = strtotime($entrainement['heure']);
                setlocale(LC_TIME, 'fr_FR.UTF-8');
                $formatted_date = strftime('%d %B %Y', $date);
                $formatted_time = date('H\hi', $heure);

                // 检查用户是否已报名该活动
                $stmt = $conn->prepare("SELECT * FROM inscription WHERE id_user = :id_user AND id_entrainement = :id_entrainement");
                $stmt->execute(['id_user' => $user_id, 'id_entrainement' => $entrainement['id_entrainement']]);
                $isEnrolled = $stmt->fetch();

                // 获取当前报名人数
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM inscription WHERE id_entrainement = :id_entrainement");
                $stmt->execute(['id_entrainement' => $entrainement['id_entrainement']]);
                $count = $stmt->fetch();
                $current_count = $count['count'];

                // 判断是否可以报名
                $canEnroll = true;
                $enrollMessage = '';
                if ($isEnrolled) {
                    $canEnroll = false;
                    $enrollMessage = 'Déjà inscrit';
                } elseif ($participants_max !== null && $current_count >= $participants_max) {
                    $canEnroll = false;
                    $enrollMessage = 'Complet';
                }
                ?>
                <div class="training-card">
                    <div class="card-image">
                        <?php if (!empty($photos)): ?>
                            <a href="<?php echo htmlspecialchars($photos[0]['url_photo']); ?>" 
                               data-lightbox="gallery-<?php echo $entrainement['id_entrainement']; ?>">
                                <img src="<?php echo htmlspecialchars($photos[0]['url_photo']); ?>" alt="Photo principale">
                            </a>
                        <?php else: ?>
                            <img src="images/default.jpg" alt="Image par défaut">
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <h2 class="card-title"><?php echo $titre; ?></h2>
                        <p class="card-category"><strong>Catégorie:</strong> <?php echo $categorie; ?></p>
                        <p class="card-description"><?php echo $description; ?></p>
                        
                        <div class="card-details">
                            <div class="detail-item">
                                <i class="bi bi-calendar"></i> <?php echo $formatted_date; ?>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-clock"></i> <?php echo $formatted_time; ?>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-geo-alt"></i> <?php echo $lieu; ?>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-people"></i> <?php echo $participants_max !== null ? 'Jusqu\'à ' . $participants_max . ' personnes' : 'Illimité'; ?>
                            </div>
                        </div>

                        <!-- 显示与该活动相关的所有照片 -->
                        <div class="photo-gallery">
                            <?php foreach ($photos as $photo): ?>
                                <a href="<?php echo htmlspecialchars($photo['url_photo']); ?>" 
                                   data-lightbox="gallery-<?php echo $entrainement['id_entrainement']; ?>" 
                                   data-title="<?php echo htmlspecialchars($titre); ?>">
                                    <img src="<?php echo htmlspecialchars($photo['url_photo']); ?>" alt="Photo supplémentaire">
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- 报名按钮 -->
                        <?php if ($canEnroll): ?>
                            <form method="post" action="" class="mt-3">
                                <input type="hidden" name="id_entrainement" value="<?php echo $entrainement['id_entrainement']; ?>">
                                <button type="submit" name="enroll" class="enroll-button">S'inscrire maintenant</button>
                            </form>
                        <?php else: ?>
                            <button class="enroll-button mt-3" disabled><?php echo $enrollMessage; ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (needed for Lightbox) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Lightbox JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    
    <script>
        // 初始化 Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': "Image %1 sur %2"
        });
        
        // 搜索功能
        document.querySelector('input[type="text"]').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.training-card').forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-description').textContent.toLowerCase();
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // 分类筛选功能
        document.querySelector('.form-select').addEventListener('change', function(e) {
            const category = e.target.value.toLowerCase();
            document.querySelectorAll('.training-card').forEach(card => {
                const cardCategory = card.querySelector('.card-category').textContent.toLowerCase();
                if (!category || cardCategory.includes(category)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
