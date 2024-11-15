<?php
header('Content-Type: application/json');
include 'db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];
$message = "";

try {
    switch ($method) {
        case 'GET':
            // 获取所有活动
            $stmt = $conn->query("SELECT * FROM entrainement");
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($activities);
            break;

        case 'POST':
            // 处理图片上传
            $chemin_image = '';
            if (isset($_FILES['chemin_image']) && $_FILES['chemin_image']['error'] == 0) {
                $file = $_FILES['chemin_image'];
                $allowedMimeTypes = ['image/jpeg'];
                $maxFileSize = 2.5 * 1024 * 1024; // 2.5MB

                // 检查文件类型
                if (!in_array($file['type'], $allowedMimeTypes)) {
                    throw new Exception('Le fichier doit être au format JPEG.');
                }

                // 检查文件大小
                if ($file['size'] > $maxFileSize) {
                    throw new Exception('La taille de l\'image doit être inférieure à 2,5 Mo.');
                }

                // 定义文件存储路径
                $uploadDir = 'image_entrainement/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // 生成唯一的文件名以防止文件名冲突
                $fileName = uniqid() . '-' . basename($file['name']);
                $uploadPath = $uploadDir . $fileName;

                // 移动文件到指定目录
                if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    throw new Exception('Erreur lors du téléchargement de l\'image.');
                }

                // 使用文件路径来保存到数据库
                $chemin_image = $uploadPath;
            }

            // 添加新活动
            $titre = $_POST['titre'];
            $description = $_POST['description'];
            $categorie = $_POST['categorie'];
            $date = $_POST['date'];
            $heure = $_POST['heure'];
            $lieu = $_POST['lieu'];
            $participants_max = $_POST['participants_max'];
            $cree_par = 1; // 当前管理员 ID，确保该用户存在于 `utilisateur` 表中

            $sql = "INSERT INTO entrainement (titre, description, categorie, date, heure, lieu, chemin_image, participants_max, cree_par)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$titre, $description, $categorie, $date, $heure, $lieu, $chemin_image, $participants_max, $cree_par]);

            echo json_encode(['status' => 'success']);
            break;

        case 'PUT':
            // 更新活动
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("UPDATE entrainement SET titre = :titre, description = :description, categorie = :categorie, date = :date,
                                    heure = :heure, lieu = :lieu, chemin_image = :chemin_image, participants_max = :participants_max
                                    WHERE id_entrainement = :id");
            $stmt->execute([
                ':titre' => $data['titre'],
                ':description' => $data['description'],
                ':categorie' => $data['categorie'],
                ':date' => $data['date'],
                ':heure' => $data['heure'],
                ':lieu' => $data['lieu'],
                ':chemin_image' => $data['chemin_image'],
                ':participants_max' => $data['participants_max'],
                ':id' => $data['id'],
            ]);

            echo json_encode(['status' => 'success']);
            break;

        case 'DELETE':
            // 删除活动
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("DELETE FROM entrainement WHERE id_entrainement = :id");
            $stmt->execute([':id' => $data['id']]);
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'method not allowed']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
