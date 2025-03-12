<?php
session_start();

$client_id = 'UR_CLIENT_ID';
$client_secret = 'UR_CLIENT_SECRET';
$redirect_uri = 'UR_REDIRECT_URI';

// Als er een 'code' is (na het inloggen)
if (isset($_GET['code'])) {
    // Stap 1: Verkrijg het access token
    $auth_url = 'https://accounts.spotify.com/api/token';
    $headers = [
        "Authorization: Basic " . base64_encode($client_id . ':' . $client_secret),
        "Content-Type: application/x-www-form-urlencoded"
    ];

    $data = [
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri,
    ];

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($auth_url, false, $context);
    $response_data = json_decode($response, true);
    $access_token = $response_data['access_token'];

    // Stap 2: Haal de meest beluisterde artiesten op
    $api_url = 'https://api.spotify.com/v1/me/top/artists?limit=5';
    $headers = [
        "Authorization: Bearer " . $access_token
    ];

    $options = [
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers)
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($api_url, false, $context);
    $top_artists = json_decode($response, true);
} else {
    // Stap 3: Toon de Spotify inlogknop als de gebruiker nog niet ingelogd is
    $login_url = 'https://accounts.spotify.com/authorize?response_type=code&client_id=' . $client_id . '&redirect_uri=' . urlencode($redirect_uri) . '&scope=user-top-read';
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify Aanbevelingen</title>
    <style>
        /* Zelfde CSS als eerder */
    </style>
</head>
<body>

<?php if (!isset($_GET['code'])): ?>
    <!-- Toon de inlogknop als de gebruiker niet ingelogd is -->
    <a href="<?= $login_url ?>">Inloggen met Spotify</a>
<?php else: ?>
    <!-- Toon de artiesten grid als de gebruiker is ingelogd -->
    <div class="container">
        <?php
        foreach ($top_artists['items'] as $artist) {
            $name = $artist['name'];
            $genres = implode(', ', $artist['genres']);
            $image_url = $artist['images'][0]['url'];
            $spotify_url = $artist['external_urls']['spotify'];
        ?>
            <div class="artist">
                <img src="<?= $image_url ?>" alt="<?= $name ?>">
                <div class="overlay">
                    <div class="artist-info">
                        <h3><?= $name ?></h3>
                        <p class="genres"><?= $genres ?></p>
                        <a href="<?= $spotify_url ?>" target="_blank">Bekijk op Spotify</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php endif; ?>

</body>
</html>
