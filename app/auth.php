<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

function loginWithGoogle() {
    global $google_client, $pdo;

    $allowedDomain = 'cedhinuevaarequipa.edu.pe';

    if (isset($_GET["code"])) {
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

        if (!isset($token['error'])) {
            $google_client->setAccessToken($token['access_token']);
            $_SESSION['access_token'] = $token['access_token'];

            $google_service = new Google\Service\Oauth2($google_client);
            $data = $google_service->userinfo->get();

            if ($data) {
                $email = $data['email'];
                $emailDomain = substr(strrchr($email, "@"), 1);

                if ($emailDomain !== $allowedDomain) {
                    logout();
                    throw new Exception("Correo no válido. Debe terminar en @{$allowedDomain}.");
                    return null;
                }

                $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = :google_id OR email = :email LIMIT 1");
                $stmt->execute([":google_id" => $data['id'], ":email" => $data['email']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $stmt = $pdo->prepare("INSERT INTO users (google_id, first_name, last_name, email, role, estado)
                                           VALUES (:google_id, :first_name, :last_name, :email, 'general_user', 'activo')");
                    $stmt->execute([
                        ":google_id" => $data['id'],
                        ":first_name" => $data['given_name'] ?? '',
                        ":last_name"  => $data['family_name'] ?? '',
                        ":email"      => $data['email']
                    ]);

                    $userId = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->execute([":id" => $userId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $stmt = $pdo->prepare("UPDATE users 
                                           SET google_id = :google_id, first_name = :first_name, 
                                               last_name = :last_name, email = :email
                                           WHERE id = :id");
                    $stmt->execute([
                        ":google_id" => $data['id'],
                        ":first_name" => $data['given_name'] ?? '',
                        ":last_name"  => $data['family_name'] ?? '',
                        ":email"      => $data['email'],
                        ":id"         => $user['id']
                    ]);

                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->execute([":id" => $user['id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                $userDataForSession = [
                    'user_id' => $user['id'],
                    'google_id' => $user['google_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'estado' => $user['estado'] ?? 'activo'
                ];

                return $userDataForSession;
            }
        }
    }
    return null;
}

function logout() {
    global $google_client;
    
    if (isset($_SESSION['access_token'])) {
        try {
            $google_client->revokeToken($_SESSION['access_token']);
        } catch (Exception $e) {
            error_log("Error revoking Google token: " . $e->getMessage());
        }
    }

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
 
    session_destroy();
}
?>