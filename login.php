<?php
session_start();


if (isset($_POST['login'])) {
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Valider les données
    $errors = [];

    // Vérifier si l'email est au bon format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // Si des erreurs sont présentes, les afficher
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    } else {
        // Toutes les données sont valides, vérifier les identifiants en base de données
        $key = "aymane";
        $cypherMethod = 'AES-256-CBC';
        $iv = "aymanegombra1234";
        $encryptedEmail = openssl_encrypt($email, $cypherMethod, $key, $options=0, $iv);
        // Effectuer la connexion à la base de données
        require 'inc/func/db_connection.php';
        $dbConnection = new DBConnection();
        $connection = $dbConnection->getConnection();

        // Préparer la requête pour récupérer l'utilisateur correspondant à l'email fourni
        $query = "SELECT * FROM users WHERE email_user = :email";
        $statement = $connection->prepare($query);
        $statement->bindParam(':email', $encryptedEmail);
        $statement->execute();

        // Vérifier si un utilisateur correspondant à l'email a été trouvé
        if ($statement->rowCount() > 0) {
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            $passwordHash = $user["password_user"];
            // Vérifier si le mot de passe correspond
            if (password_verify($password, $passwordHash)) {
                // Les identifiants sont valides, définir la session utilisateur et rediriger vers la page de la to-do list
                $_SESSION['user']['email_user'] =  $email;
                $_SESSION['user']['id_user'] = $user['id_user'];
                header('Location: todo.php');
                exit;
            }
            
        }

        
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
</head>
<body>
    <h2>Connexion</h2>
    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>"><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <?php echo isset($_POST['login']) ? '<p>Identifiants invalides</p>' : ''; ?>
        <input type="submit" name="login" value="Se connecter">
    </form>
</body>
</html>
