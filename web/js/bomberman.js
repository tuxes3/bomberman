/*
 * This file is part of the bomberman project.
 *
 * @author Nicolo Singer tuxes3@outlook.com
 * @author Lukas Müller computer_bastler@hotmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
(function($) {

    var bomberman_socket_request = {
        prepare: function (request) {
            request.uuid = bomberman_storage.getUuid();
            return JSON.stringify(request);
        },

        init: function () {
            return this.prepare({
                name: 'player',
                event: 'init',
                data: null
            });
        },

        createRoom: function (maxPlayers, name) {
            return this.prepare({
                name: 'room',
                event: 'create',
                data: {
                    maxPlayers: maxPlayers,
                    name: name
                }
            });
        },

        joinRoom: function (uniqueId) {
            return this.prepare({
                name: 'room',
                event: 'join',
                data: {
                    uniqueId: uniqueId
                }
            });
        },

        leaveRoom: function (uniqueId) {
            return this.prepare({
                name: 'room',
                event: 'leave',
                data: {
                    uniqueId: uniqueId
                }
            });
        },

        movePlayer: function (direction) {
            return this.prepare({
                name: 'player',
                event: 'move',
                data: {
                    direction: direction
                }
            });
        },

        plantBomb: function () {
            return this.prepare({
                name: 'player',
                event: 'plant',
                data: null
            });
        }
    };

    var bomberman_storage = {
        getUuid: function () {
            if (localStorage.userId == null) {
                localStorage.userId = Math.random().toString(36).substring(2) + (new Date()).getTime().toString(36); // uuid
            }
            return localStorage.userId;
        },

        getMuted: function () {
            if (localStorage.muted == null) {
                localStorage.muted = false;
            }
            return localStorage.muted === 'true';
        },

        setMuted: function (muted) {
            localStorage.muted = muted;
        }
    };

    var bomberman_stage = {

        // const
        BASE_SIZE: 64,

        // Aliases
        Application: PIXI.Application,
        loader: PIXI.loader,
        resources: PIXI.loader.resources,
        Sprite: PIXI.Sprite,
        Stage: PIXI.display.Stage,
        Group: PIXI.display.Group,
        Layer: PIXI.display.Layer,
        Container: PIXI.Container,

        app: null,
        parentGroup: null,
        sprites: {},
        elementContainer: null,
        animations: [],

        init: function () {
            this.app = new bomberman_stage.Application({
                width: 576,
                height: 576,
                transparent: true
            });
            $('#field').append(this.app.view);
            this.app.stage = new bomberman_stage.Stage();
            this.app.stage.group.enableSort = true;
            this.parentGroup = new bomberman_stage.Group(0, true);
            this.elementContainer = new bomberman_stage.Container();
            this.app.stage.addChild(new bomberman_stage.Layer(this.parentGroup));
            this.app.stage.addChild(bomberman_stage.elementContainer);
            this.app.ticker.add(bomberman_stage.update);
            this.loader
                .add('block', 'img/block.gif')
                .add('bomb', 'img/bomb.gif')
                .add('explosionradiusitem', 'img/bombsize_lvlup.gif')
                .add('explosion', 'img/explosion.gif')
                .add('fixblock', 'img/fixBlock.gif')
                .add('movebombitem', 'img/kickitemg.gif')
                .add('player', 'img/man.gif')
                .add('rip', 'img/rip.gif')
                .add('shoeitem', 'img/shoe.gif')
                .add('bombitem', 'img/twobomb.gif')
                .on('progress', bomberman_ui.loadProgressHandler)
                .load(bomberman_stage.setup);
        },

        setup: function () {
            $('#loading').hide();
            bomberman_socket.init();
            bomberman_ui.init();
        },

        update: function (delta) {
            for (var id in bomberman_stage.animations) {
                if (bomberman_stage.animations.hasOwnProperty(id)) {
                    var anim = bomberman_stage.animations[id];
                    var diff = null;
                    if (anim.sprite.x === anim.newX) {
                        diff = Math.abs(anim.sprite.y - anim.newY);
                    } else {
                        diff = Math.abs(anim.sprite.x - anim.newX);
                    }
                    if (anim.lastDiff < diff || diff < 5) {
                        anim.sprite.x = anim.newX;
                        anim.sprite.y = anim.newY;
                        bomberman_stage.animations.splice(id, 1);
                    } else {
                        anim.sprite.x += anim.stepX;
                        anim.sprite.y += anim.stepY;
                        anim.lastDiff = diff;
                    }
                }
            }
        },

        moveAnimate: function (id, sprite, newX, newY, duration) {
            var stepX = (newX - sprite.x) / (duration / (1000 / 60));
            var stepY = (newY - sprite.y) / (duration / (1000 / 60));
            if (id in bomberman_stage.animations) {
                var anim = bomberman_stage.animations[id];
                anim.sprite.x = anim.newX;
                anim.sprite.y = anim.newY;
                bomberman_stage.animations.splice(id, 1);
            }
            bomberman_stage.animations[id] = {
                sprite: sprite,
                newX: newX,
                newY: newY,
                stepX: stepX,
                stepY: stepY,
                lastDiff: 10000
            };
        }
    };

    var bomberman_ui = {

        bombAudio: null,
        deadAudio: null,
        winAudio: null,
        loseAudio: null,

        bombMovementSpeed: 600,     // some init value. both will be overwritten
        movementSpeed: 300,         //
        lastMoved: null,
        lastWantedMovement: null,
        waitingForNextMove: null,

        init: function () {
            $('#createRoom').on('click', bomberman_ui.createRoom);
            $(document).keydown(bomberman_ui.onKeyDown);
            $('#buttonLeft').on('click touch', 37, bomberman_ui.onKeyDown);
            $('#buttonRight').on('click touch',39, bomberman_ui.onKeyDown);
            $('#buttonDown').on('click touch',40, bomberman_ui.onKeyDown);
            $('#buttonUp').on('click touch', 38, bomberman_ui.onKeyDown);
            $('#buttonBomb').on('click touch', 32, bomberman_ui.onKeyDown);
            $('#arrowControlls').hide(); //hide the controlls at start -->  show them when the game starts
            this.bombAudio = bomberman_ui.initSound('./sound/bomb.mp3');
            this.deadAudio = bomberman_ui.initSound('./sound/dead.mp3');
            this.winAudio = bomberman_ui.initSound('./sound/tada.mp3');
            this.loseAudio = bomberman_ui.initSound('./sound/lose.mp3');
            var speaker = $('#speaker');
            speaker.on('click touch', bomberman_ui.toggleMute);
            if (bomberman_storage.getMuted()) {
                speaker.addClass('mute');
            }
        },

        loadProgressHandler: function (loader, resource) {
            $('#loading-percent').text(loader.progress);
        },

        setMinViewPort: function (players) {
            var ww = ($(window).width() < window.screen.width) ? $(window).width() : window.screen.width; //get proper width
            var mw = 200 + (players * 100); // min width of site
            var ratio = ww / mw; //calculate ratio
            if (ww < mw) { //smaller than minimum size
                $('#Viewport').attr('content', 'initial-scale=' + ratio + ', maximum-scale=' + ratio + ', minimum-scale=' + ratio + ', user-scalable=yes, width=' + ww);
            } else { //regular size
                $('#Viewport').attr('content', 'initial-scale=1.0, maximum-scale=2, minimum-scale=1.0, user-scalable=yes, width=' + ww);
            }
        },

        initSound: function (path) {
            var sound = new Audio();
            var source = document.createElement('source');
            source.type = 'audio/mpeg';
            source.src = path;
            sound.appendChild(source);
            return sound;
        },

        isTouchDevice: function () {
            return 'ontouchstart' in window        // works on most browsers
                || navigator.maxTouchPoints;       // works on IE10/11 and Surface
        },

        toggleMute: function (e) {
            e.preventDefault();
            bomberman_storage.setMuted(!bomberman_storage.getMuted());
            $('#speaker').toggleClass('mute');
        },

        createRoom: function (e) {
            e.preventDefault();
            bomberman_socket.send(bomberman_socket_request.createRoom(
                $('#maxPlayer').val(), $('#roomName').val()
            ));
        },

        joinRoom: function (e) {
            e.preventDefault();
            bomberman_socket.send(bomberman_socket_request.joinRoom(
                $(this).data('unique-id')
            ));
        },

        leaveRoom: function (e) {
            e.preventDefault();
            bomberman_socket.send(bomberman_socket_request.leaveRoom(
                $(this).data('unique-id')
            ));
        },

        onKeyDown: function (e) {
            var dir = '\u2191';
            var keycode = e.which || e.keyCode;
            var _ = bomberman_ui;

            if(keycode === 1){
                keycode = e.data;
            }

            // See
            // https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/which
            // https://www.w3schools.com/jsref/tryit.asp?filename=tryjsref_event_key_keycode
            // https://www.key-shortcut.com/schriftsysteme/35-symbole/pfeile/
            if ([87,65,83,68,40,38,37,39].indexOf(keycode) >= 0) {
                if (keycode === 87 || keycode === 38) {
                    // UP
                    dir = '\u2191';
                } else if (keycode === 65 || keycode === 37) {
                    // LEFT
                    dir = '\u2190';
                } else if (keycode === 83 || keycode === 40) {
                    // DOWN
                    dir = '\u2193';
                } else if (keycode === 68 || keycode === 39) {
                    // RIGHT
                    dir = '\u2192';
                }

                var now = Date.now();
                if (_.lastMoved === null || _.lastMoved + _.movementSpeed <= now) {
                    if (_.waitingForNextMove !== null) {
                        clearTimeout(_.waitingForNextMove);
                        _.waitingForNextMove = null;
                    }
                    bomberman_socket.send(bomberman_socket_request.movePlayer(dir));
                    _.lastMoved = now;
                } else {
                    _.lastWantedMovement = dir;
                    if (_.waitingForNextMove == null) {
                        _.waitingForNextMove = setTimeout(function() {
                            bomberman_socket.send(bomberman_socket_request.movePlayer(bomberman_ui.lastWantedMovement));
                            bomberman_ui.waitingForNextMove = null;
                        }, _.lastMoved + _.movementSpeed - now)
                    }
                }
            } else if (keycode === 32) {
                bomberman_socket.send(bomberman_socket_request.plantBomb());
            }
        }

    };

    var bomberman_socket = {
        connection: null,

        init: function () {
            this.connection = new WebSocket(BOMBERMAN_WEBSOCKET_URL);
            this.connection.onmessage = this.onMessage;
            this.connection.onopen = this.onOpen;
            this.connection.onclose = this.onClose;
        },

        onOpen: function (e) {
            this.send(bomberman_socket_request.init());
        },

        onMessage: function (e) {
            var message = JSON.parse(e.data);
            bomberman_socket.handler[message.name][message.event](message.data);
        },

        onClose: function (e) {
            $('#connectionLost').css('display', 'block');
            setTimeout(function () {
                bomberman_socket.init();
            }, 5000);
        },

        send: function (request) {
            this.connection.send(request);
        },

        handler: {
            game_js: {
                started: function (data) {
                    swal.close();
                    $('#roomcontrols').hide();
                    $('#roomList').hide();
                    $('#field').show();
                    if(bomberman_ui.isTouchDevice()){
                        bomberman_ui.setMinViewPort(data);
                        $('#arrowControlls').show();
                    }
                },

                finished: function (data) {
                    var endSound;
                    var _call = function(text, sound){
                        if (text != null) {
                            swal(text);
                        }
                        $('#roomcontrols').show();
                        $('#roomList').show();
                        $('#field').hide();
                        $('#arrowControlls').hide();
                        if(!bomberman_storage.getMuted() && sound != null){
                            endSound.play();
                        }
                    };
                    // null: close due to inactivity
                    if (data !== null) {
                        var text = 'You ';
                        if(data.won){
                            text = text + 'win!';
                            endSound = bomberman_ui.winAudio;
                        }else{
                            text = text + 'lose!';
                            endSound = bomberman_ui.loseAudio
                        }
                        window.setTimeout(function(){_call(text, endSound);}, 700); // give the player some time to realize he died
                    } else {
                        _call(null, null);
                    }

                },

                bombMovementSpeed: function (bombMovementSpeed) {
                    bomberman_ui.bombMovementSpeed = bombMovementSpeed;
                }
            },

            room_js: {
                list: function (roomList) {
                    // connection is back
                    $('#connectionLost').css('display', 'none');
                    var roomListDiv = $('#roomList');
                    roomListDiv.empty();
                    var ul = $('<ul></ul>');
                    for (var i = 0; i < roomList.length; i++) {
                        var li = $('<li></li>');
                        li.append($(
                            '<a href="#" data-unique-id="'+roomList[i].uniqueId+'">Room #'+i+': '+roomList[i].name+' ('+roomList[i].connectedPlayers+'/'+roomList[i].maxPlayers+')</a>'
                        ).on('click', bomberman_ui.joinRoom));
                        var showLeave = false;
                        for (var key in roomList[i].players){
                            if (roomList[i].players.hasOwnProperty(key)) {
                                showLeave = showLeave || roomList[i].players[key] === bomberman_storage.getUuid();
                            }
                        }
                        if (showLeave) {
                            li.append($('<span> - </span>'));
                            li.append($(
                                '<a href="#" class="leave" data-unique-id="'+roomList[i].uniqueId+'">Leave</a>'
                            ).on('click', bomberman_ui.leaveRoom));
                        }
                        ul.append(li);
                    }
                    roomListDiv.append(ul);
                }
            },

            message_js: {
                warning: function (message) {
                    swal(message);
                },
                info: function (message) {
                    swal(message);
                }
            },

            field_js: {
                update: function (field) {
                    var doNotDelete = [];
                    for (var i = 0; i < field.cells.length; i++) {
                        for (var j = 0; j < field.cells[i].length; j++) {
                            var inCells = field.cells[i][j].inCells;
                            for (var r = 0; r < inCells.length; r++) {
                                var inCell = inCells[r];
                                var sprite = bomberman_stage.sprites[inCell.id];
                                doNotDelete.push(inCell.id);
                                var newX = i * bomberman_stage.BASE_SIZE;
                                var newY = j * bomberman_stage.BASE_SIZE;
                                if (typeof sprite === 'undefined') {
                                    sprite = new bomberman_stage.Sprite(
                                        bomberman_stage.resources[inCell.class].texture
                                    );
                                    sprite.zIndex = inCell.displayPriority;
                                    sprite.displayOrder = inCell.displayPriority;
                                    bomberman_stage.sprites[inCell.id] = sprite;
                                    sprite.parentGroup = bomberman_stage.parentGroup;
                                    bomberman_stage.elementContainer.addChild(sprite);
                                    sprite.x = newX;
                                    sprite.y = newY;
                                }
                                if (sprite.vx !== newX || sprite.vy !== newY) {
                                    sprite.vx = newX;
                                    sprite.vy = newY;
                                    bomberman_stage.moveAnimate(inCell.id, sprite, newX, newY, bomberman_ui.movementSpeed);
                                }
                            }
                        }
                    }
                    var currentlyOnField = Object.keys(bomberman_stage.sprites);
                    for (var i = 0; i < currentlyOnField.length; i++) {
                        if (doNotDelete.indexOf(currentlyOnField[i]) === -1) {
                            bomberman_stage.elementContainer.removeChild(
                                bomberman_stage.sprites[currentlyOnField[i]]
                            );
                        }
                    }
                }
            },

            player_js: {
                movementSpeed: function (movementSpeed) {
                    bomberman_ui.movementSpeed = movementSpeed;
                }
            }
        }
    };

    bomberman_stage.init();
})(jQuery);
