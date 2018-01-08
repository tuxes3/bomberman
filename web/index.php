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
    <title>Checker-Bomberman</title>
    <meta name="description" content="Checker-Bomberman Web Game" />
    <meta name="keywords" content="Checker Bomberman Web Projekt BFH" />
    <meta name="author" content="Singer Nicolo & Mueller, Lukas" />
    <meta name="Robots" content="index, follow">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.3.0/sweetalert2.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <meta id="Viewport"name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>

    <noscript>
        <div class="js_error">
            <p>This page will only work if you enable Javascript and use a modern browser!</p>
        </div>
    </noscript>

    <header id="header">
        <h1>Checker-Bomberman <small id="you-are" style="display: none">You are <span id="your-color">&nbsp;&nbsp;&nbsp;&nbsp;</span></small></h1>
        <h4>Web Project BTI7054</h4>
    </header>

    <main id="main">
        <div id ="roomcontrols">
            <h3>Create room</h3>
            Room Name: <input type="text" id="roomName" value="name" /> <br />
            Max Player: &nbsp;&nbsp;<input type="range" id="maxPlayerInput" name="maxPlayerInput" min="1" max="10" value="2" oninput="maxPlayer.value=maxPlayerInput.value">
            <output name="maxPlayer" id="maxPlayer" for="rangeInput">2</output> <br />

            <script></script>
            <a id="createRoom" href="#">Create Room</a>
        </div>

        <div id="field" style="display: none;">

        </div>

        <div id="arrowControlls">
            <button id="buttonUp" >&#8593;</button><br> <!-- up -->
            <button id="buttonLeft">&#8592;</button> <!-- left -->
            <button id="buttonBomb">&#128163;</button> <!-- bomb -->
            <button id="buttonRight" >&#8594;</button><br> <!-- right -->
            <button id="buttonDown">&#8595;</button> <!-- down -->
        </div>



        <div id="roomList">
        </div>

        <div id="connectionLost">
            <img height="500px" src="img/bomb-loading.svg" alt="bouncing bomb" />
            <h1>Reconnecting ...</h1>
        </div>
        <div id="loading">
            <img height="500px" src="img/bomb-loading.svg" alt="bouncing bomb" />
            <h1>Loading <span id="loading-percent">0</span>% ...</h1>
        </div>
    </main>

    <a href="#" id="speaker">
        <span></span>
    </a>

    <footer id="footer">
        <div id="footer-main">
            &copy; Nicolo Singer & Lukas Müller <br />
            Kindly hosted by <a href="https://whatwedo.ch">whatwedo.ch</a>
        </div>
        <span id="github"><a target="_blank" href="https://github.com/tuxes3/bomberman"><img src="img/github.png"></a></span>
    </footer>

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
    <!-- pixi -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/4.5.1/pixi.min.js"></script>
    <script src="js/plugin/pixi-layers.js"></script>
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>
    <!-- sweetalert 2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.3.0/sweetalert2.all.min.js"></script>
    <!-- json patch -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fast-json-patch/2.0.6/fast-json-patch.min.js"></script>
    <!-- bomberman app -->
    <script src="js/bomberman.js"></script>
</body>
</html>
