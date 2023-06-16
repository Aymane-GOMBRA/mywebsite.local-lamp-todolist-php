<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
if (isset($_POST["todo"])){
    $titre = htmlspecialchars(trim($_POST["titre"]));
    $desc = htmlspecialchars(trim($_POST["desc"]));
    $dateRendu = htmlspecialchars(trim($_POST["rendu"]));
    $prio = htmlspecialchars(trim($_POST["prio"]));
    $errors = [];
    
    if (empty($titre)) {
        $errors[] = "Le titre est obligatoire.";
    }
    if (empty($prio)) {
        $errors[] = "La priorité est obligatoire.";
    }
    require 'inc/func/functions.php';
    if (!validateDate($dateRendu)) {
        $errors[] = "Le format de la date n'est pas bon.";
    }
    if (!validateSelect($prio, ['Faible', 'Moyenne', 'Forte'])) {
        $errors[] = "La valeur sélectionnée est pas valide.";
    }
    
    $key = "aymane";
    $cypherMethod = 'AES-256-CBC';
    $iv = "aymanegombra1234";
    $encryptedEmail = openssl_encrypt($_SESSION['user']['email_user'], $cypherMethod, $key, $options=0, $iv);
    // Effectuer la connexion à la base de données
    require_once 'inc/func/db_connection.php';
    $dbConnection = new DBConnection();
    $connection = $dbConnection->getConnection();

    // Préparer la requête pour récupérer l'utilisateur correspondant à l'email fourni
    $query = "SELECT * FROM users WHERE email_user = :email";
    $statement = $connection->prepare($query);
    $statement->bindParam(':email', $encryptedEmail);
    $statement->execute();
    if ($statement->rowCount() > 0) {

    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }else{
        $key = "aymane";
        $cypherMethod = 'AES-256-CBC';
        $iv = "aymanegombra1234";
        $encryptedEmail = openssl_encrypt($_SESSION['user']['email_user'], $cypherMethod, $key, $options=0, $iv);
        // Effectuer la connexion à la base de données
        $connection = $dbConnection->getConnection();

        // Préparer la requête pour récupérer l'utilisateur correspondant à l'email fourni
        $query = "SELECT * FROM users WHERE email_user = :email";
        $statement = $connection->prepare($query);
        $statement->bindParam(':email', $encryptedEmail);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            $id_user = $user["id_user"];
        }
        require_once 'inc/func/db_connection.php';
        $dbConnection = new DBConnection();
        $connection = $dbConnection->getConnection();
        // Préparer la requête d'insertion
        // $query = "INSERT INTO tasks (titre_task,users_id_user) VALUES (:titre_task, :users_id_user)";
        // $statement = $connection->prepare($query);
        // $statement->bindParam(':titre_task', $titre);
        // $statement->bindParam(':users_id_user', $id_user);


        $statut_task = "à faire";
        $date_creation_task = date('Y-m-d');  
        $query = "INSERT INTO tasks (titre_task,desc_task,statut_task,date_creation_task, date_rendu_voulu,prio_task,users_id_user) VALUES (:titre_task,:desc_task,:statut_task,:date_creation_task,:date_rendu_voulu,:prio_task,:users_id_user)";
        $statement = $connection->prepare($query);
        $statement->bindParam(':titre_task', $titre);
        $statement->bindParam(':desc_task', $desc);
        $statement->bindParam(':statut_task', $statut_task);
        $statement->bindParam(':date_creation_task', $date_creation_task);
        $statement->bindParam(':date_rendu_voulu', $dateRendu);
        $statement->bindParam(':prio_task', $prio);
        $statement->bindParam(':users_id_user', $id_user);
        if ($statement->execute()) {
            header('Location: todo.php');
        } else {
            echo "Une erreur s'est produite lors de l'enregistrement de la task.";
        }
    }
    $connection = $dbConnection->disconnect();
}
if (isset($_POST["update"])) {
    $taskId = $_POST["task_id"]; // Récupérer l'ID de la tâche à mettre à jour

    // Récupérer les nouvelles valeurs des champs du formulaire
    $titre = htmlspecialchars(trim($_POST["titre"]));
    $desc = htmlspecialchars(trim($_POST["desc"]));
    $dateRendu = htmlspecialchars(trim($_POST["rendu"]));
    $prio = htmlspecialchars(trim($_POST["prio"]));
    $statut = htmlspecialchars(trim($_POST["statut_task"]));
    $dateModif = date('Y-m-d H:i:s');
    $errors = [];

    // Valider les données du formulaire
    // ...

    if (empty($errors)) {
        // Effectuer la mise à jour de la tâche dans la base de données
        require_once 'inc/func/db_connection.php';
        $dbConnection = new DBConnection();
        $connection = $dbConnection->getConnection();

        $query = "UPDATE tasks SET titre_task = :titre_task, desc_task = :desc_task, statut_task = :statut_task,date_modif_task = :date_modif_task, date_rendu_voulu = :date_rendu_voulu, prio_task = :prio_task WHERE id_task = :task_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(':titre_task', $titre);
        $statement->bindParam(':desc_task', $desc);
        $statement->bindParam(':statut_task', $statut);
        $statement->bindParam(':date_modif_task', $dateModif);
        $statement->bindParam(':date_rendu_voulu', $dateRendu);
        $statement->bindParam(':prio_task', $prio);
        $statement->bindParam(':task_id', $taskId);

        if ($statement->execute()) {
            // La mise à jour a réussi
            echo "La tâche a été mise à jour avec succès.";
            header('Location: todo.php');
        } else {
            // Une erreur s'est produite lors de la mise à jour
            echo "Une erreur s'est produite lors de la mise à jour de la tâche.";
        }
    } else {
        // Afficher les erreurs de validation
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }
}




?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>To Do List</h1>
    <form method="post" action="">
        <label for="titre">Titre:</label>
        <input type="text" name="titre" placeholder="Titre" required><br>

        <label for="desc">Description:</label>
        <input type="text" name="desc" placeholder="Description"><br>

        <label for="rendu">Date de rendu voulu:</label>
        <input type="date" name="rendu" id="rendu"><br>

        <label for="prio">Priorité:</label>
        <select name="prio" id="prio">
            <option value="">--Selectionner une priorité--</option>
            <option value="Faible">Faible</option>
            <option value="Moyenne">Moyenne</option>
            <option value="Forte">Forte</option>
        </select><br>
        <input type="submit" name="todo" value="Enregistrer">
    </form>

    <p><?= "Liste des tâches de {$_SESSION['user']['email_user']} :";?></p>
    <?php
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    require_once 'inc/func/db_connection.php';
    $dbConnection = new DBConnection();
    $connection = $dbConnection->getConnection();
    $query = "SELECT * FROM tasks WHERE users_id_user = :id_user";
    $statement = $connection->prepare($query);
    $statement->bindParam(':id_user', $_SESSION['user']['id_user']);
    $statement->execute();
    if ($statement->rowCount() > 0) {
        $user = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        
    }
    if(!isset($user)){
        $user = null;
    }
    ?>
    <?php if ($user === null): ?>
    <p>Aucune tâche à afficher.</p>
    <?php else: ?>
    <table>
    <tr>
        <th>Titre</th>
        <th>Description</th>
        <th>Statut</th>
        <th>Date de création</th>
        <th>Date de modification</th>
        <th>Date de rendu voulu</th>
        <th>Priorité</th>
    </tr>

    <?php foreach ($user as $task): ?>
        <tr>
            <td><?php echo $task['titre_task']; ?></td>
            <td><?php echo $task['desc_task']; ?></td>
            <td><?php echo $task['statut_task']; ?></td>
            <td><?php echo $task['date_creation_task']; ?></td>
            <td><?php echo $task['date_modif_task']; ?></td>
            <td><?php echo $task['date_rendu_voulu']; ?></td>
            <td><?php echo $task['prio_task']; ?></td>
            <td>
                <a href="?action=edit&id=<?php echo $task['id_task']; ?>">Modifier</a>
                <a href="?action=delete&id=<?php echo $task['id_task']; ?>">Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php
if (isset($_GET["action"]) && isset($_GET["id"])) {
    $action = $_GET["action"];
    $id = $_GET["id"];

    if ($action == "edit") {
         // Récupérer les informations de la tâche à modifier depuis la base de données
         require_once 'inc/func/db_connection.php';
         $dbConnectionn = new DBConnection();
        $connection = $dbConnectionn->getConnection();
        $query = "SELECT * FROM tasks WHERE id_task = :id";
        $statement = $connection->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();

    if ($statement->rowCount() > 0) {
        $task = $statement->fetch(PDO::FETCH_ASSOC);

        // Afficher le formulaire de modification avec les informations pré-remplies
        ?>
        <form method="post" action="">
            <input type="hidden" name="task_id" value="<?php echo $task['id_task']; ?>">
            <label for="titre">Titre:</label>
            <input type="text" name="titre" value="<?php echo $task['titre_task']; ?>" required><br>

            <label for="desc">Description:</label>
            <input type="text" name="desc" value="<?php echo $task['desc_task']; ?>"><br>

            <label for="statut_task">Statut:</label>
            <select name="statut_task">
                <option value="à faire" <?php if ($task['statut_task'] == 'à faire') echo 'selected'; ?>>à faire</option>
                <option value="en cours" <?php if ($task['statut_task'] == 'en cours') echo 'selected'; ?>>en cours</option>
                <option value="terminée" <?php if ($task['statut_task'] == 'terminée') echo 'selected'; ?>>terminée</option>
                <option value="en pause" <?php if ($task['statut_task'] == 'en pause') echo 'selected'; ?>>en pause</option>
            </select><br>

            <label for="rendu">Date de rendu voulu:</label>
            <input type="date" name="rendu" value="<?php echo $task['date_rendu_voulu']; ?>"><br>

            <label for="prio">Priorité:</label>
            <select name="prio">
                <option value="Faible" <?php if ($task['prio_task'] == 'Faible') echo 'selected'; ?>>Faible</option>
                <option value="Moyenne" <?php if ($task['prio_task'] == 'Moyenne') echo 'selected'; ?>>Moyenne</option>
                <option value="Forte" <?php if ($task['prio_task'] == 'Forte') echo 'selected'; ?>>Forte</option>
            </select><br>

            <input type="submit" name="update" value="Modifier"><a href="todo.php">Annuler</a>
        </form>
        
        <?php
    } else {
        echo "La tâche n'existe pas.";
    }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $taskId = $_GET['id'];

        // Effectuer la connexion à la base de données
        require_once 'inc/func/db_connection.php';
        $dbConnection = new DBConnection();
        $connection = $dbConnection->getConnection();

        // Suppression de la tâche dans la base de données
        $query = "DELETE FROM tasks WHERE id_task = :task_id";
        $statement = $connection->prepare($query);
        $statement->bindParam(':task_id', $taskId);

        if ($statement->execute()) {
            // Redirection vers la page principale ou affichage d'un message de succès
            header('Location: todo.php');
            exit;
        } else {
            echo "Une erreur s'est produite lors de la suppression de la tâche.";
        }
    }
}
?>
    <button id="deconnexionBtn">Déconnexion</button>
    <script src="inc/js/script.js"></script>
</body>
</html>
