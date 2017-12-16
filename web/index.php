<?php
/*
 * This file is part of the bomberman project.
 *
 * @author Nicolo Singer tuxes3@outlook.com
 * @author Lukas Müller computer_bastler@hotmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bomberman</title>
    <meta name="description" content="Bomberman Web Game" />
    <meta name="keywords" content="Bomberman Web Projekt BFH" />
    <meta name="author" content="Singer Nicolo & Mueller, Lukas" />
    <meta name="Robots" content="index, follow">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
</head>

<body>

    <noscript>
        <div class="js_error">
            <p>This page will only work if you enable Javascript and use a modern browser!</p>
        </div>
    </noscript>

    <header id="header">
        <h1>Bomberman - Web Project BTI7054</h1>
    </header>

    <main id="main">
        <div id ="roomcontrols">
            <h3>Create room</h3>
            Room Name: <input type="text" id="name" value="name" /> <br />
            Max Player: &nbsp;&nbsp;  <input type="number" id="maxPlayer" value="1" size="2" /> <br />
            <script></script>
            <a id="createRoom" href="#">Create Room</a>
        </div>

        <div id="field">

        </div>

        <div id="roomList">

        </div>


        <script type="text/javascript">
            const BOMBERMAN_WEBSOCKET_URL = '<?php
            $webSocketPath = isset($_SERVER['WEBSOCKET_PATH']) ? $_SERVER['WEBSOCKET_PATH'] : ':8009';
            $host = $_SERVER['HTTP_HOST'];
            if (strpos($host, ':') !== false) {
                $host = explode(':', $host)[0];
            }
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                echo 'wss://' . $host . $webSocketPath;
            } else {
                echo 'ws://' . $host . $webSocketPath;
            }
            ?>';
        </script>

        <script
                src="https://code.jquery.com/jquery-3.2.1.min.js"
                integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
                crossorigin="anonymous"></script>
        <script src="js/socket.js">
        </script>

    </main>

    <footer id="footer">
        &copy; Nicolo Singer & Lukas Müller <br />
        Kindly hosted by <a href="https://whatwedo.ch">whatwedo.ch</a>
    </footer>

</body>
</html>
