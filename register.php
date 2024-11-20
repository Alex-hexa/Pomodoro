<?php
require 'db.php'; // Inclure le fichier de connexion
use \Firebase\JWT\JWT; // Importer la classe JWT
use PHPMailer\PHPMailer\PHPMailer; // Importer PHPMailer
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Inclure les dépendances de PHPMailer

$my_email = $_ENV['MY_EMAIL_ID']; // Remplacer par votre adresse email
$my_email_mdp = $_ENV['MY_EMAIL_MDP']; // Remplacer par le mot de passe de votre adresse email

$client = getMongoClient();
$collection = $client->myDatabase->users;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hacher le mot de passe
    $uniq_id = uniqid(); // Générer un identifiant unique

    // Vérifier si l'email existe déjà
    $existingUser = $collection->findOne(['email' => $email]);
    if ($existingUser) {
        echo json_encode(['error' => 'Cet email est déjà enregistré.']);
        exit();
    }

    // Insérer dans la base de données
    $result = $collection->insertOne([
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'uniq_id' => $uniq_id
    ]);

    if ($result->getInsertedCount() === 1) {
        // Créer le token
        $payload = [
            'username' => $username,
            'email' => $email,
            'userId' => (string)$result->getInsertedId(),
            'uniq_id' => $uniq_id,
            'iat' => time(), // Date de création
            'exp' => time() + (12 * 60 * 60) // Expire dans 12 heures
        ];

        $jwt = JWT::encode($payload, getJwtSecret(), 'HS512'); // Générer le token JWT

        // Envoyer l'email
        $mail = new PHPMailer(true);

        try {
            // Configurer PHPMailer
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com'; // Remplacez par votre serveur SMTP
            $mail->SMTPAuth = true;
            $mail->Username = $my_email; // Votre adresse email
            $mail->Password = $my_email_mdp; // Mot de passe de l'email
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8'; // Configurer l'encodage UTF-8

            // Configurer l'email
            $mail->setFrom($my_email, 'Pomodoro App');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue sur notre application';
            $mail->Body = "<h1>Bienvenue, $username!</h1><p>Votre compte a été créé avec succès.</p>";

            $mail->send();
        } catch (Exception $e) {
            // En cas d'erreur d'envoi d'email
            error_log('Erreur d\'envoi de l\'email : ' . $mail->ErrorInfo);
        }

        // Retourner le token en tant que réponse JSON
        echo json_encode(['token' => $jwt]);
        exit();
    } else {
        echo json_encode(['error' => 'Erreur lors de l\'inscription.']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="icon" href="/image/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-hH+g0XrzRX1sKHTNB5Q8dNhZAz0eCQK2CLZ2VGxE9BiNeF3D79HLKyg6sTyvUOLjYF6+xNSkG7b9DW3VBpFGOw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <nav class="navbar sticky-top navbar-expand-lg row border-bottom border-dark mx-4">
        <div class="col">
            <a class="navbar-brand mr-5 pl-2 text-danger" href="/index.php"><i class="fas fa-chess-queen"><span class="ml-2">Pomodoro App</span></i></a>
        </div>
    </nav>
    <div class="container">
        <h2 class="mt-5">Créer un compte</h2>
        <form id="registrationForm" method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        <p class="mt-3">Déjà un compte ? <a href="login.php">Connectez-vous</a></p>
    </div>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', async (event) => {
            event.preventDefault(); // Empêche le rechargement par défaut

            const formData = new FormData(event.target);

            try {
                const response = await fetch('', { // Envoie les données au même fichier
                    method: 'POST',
                    body: formData
                });
            } catch (error) {
                alert('Une erreur est survenue. Veuillez réessayer.');
            }
        });
    </script>
    <script src="./js/register.js"></script>
</body>

</html>