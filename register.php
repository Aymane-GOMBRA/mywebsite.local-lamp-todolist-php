<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (isset($_POST['register'])) {
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Valider les données
    $errors = [];

    // Vérifier si l'email est au bon format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // Vérifier si le mot de passe est assez long (minimum 6 caractères)
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    // Si des erreurs sont présentes, les afficher
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    } else {
        // Toutes les données sont valides, enregistrer l'utilisateur en base de données

        // Encrypter l'email
        $key = "aymane";
        $cypherMethod = 'AES-256-CBC';
        $iv = "aymanegombra1234";
        $encryptedEmail = openssl_encrypt($email, $cypherMethod, $key, $options=0, $iv);

        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Effectuer la connexion à la base de données
        require 'inc/func/db_connection.php';
        $dbConnection = new DBConnection();
        $connection = $dbConnection->getConnection();

        // Préparer la requête d'insertion
        $query = "INSERT INTO users (email_user, password_user) VALUES (:email, :password)";
        $statement = $connection->prepare($query);
        $statement->bindParam(':email', $encryptedEmail);
        $statement->bindParam(':password', $hashedPassword);

        // Exécuter la requête
        if ($statement->execute()) {
            // Rediriger vers la page de connexion
            header('Location: login.php');
            exit;
        } else {
            echo "Une erreur s'est produite lors de l'enregistrement de l'utilisateur.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
</head>
<body>
    <h2>Inscription</h2>
    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <input type="submit" name="register" value="S'inscrire">
    </form>
</body>
</html>
