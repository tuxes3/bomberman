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

            if(keycode == 1){
                keycode = e.data;
            }

            // See
            // https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/which
            // https://www.w3schools.com/jsref/tryit.asp?filename=tryjsref_event_key_keycode
            // https://www.key-shortcut.com/schriftsysteme/35-symbole/pfeile/
            if ([87,65,83,68,40,38,37,39].indexOf(keycode) >= 0) {
                if(keycode ==87|| keycode ==38){
                    // UP
                    dir = '\u2191';
                }
                if(keycode ==65|| keycode ==37){
                    // LEFT
                    dir = '\u2190';
                }
                if(keycode == 83 || keycode ==40){
                    // DOWN
                    dir = '\u2193';
                }
                if(keycode == 68 || keycode ==39){
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
            } else if (keycode == 32) {
                bomberman_socket.send(bomberman_socket_request.plantBomb());
            }
        },

        moveAnimate: function (element, newParent) {
            element = $(element);
            newParent= $(newParent);
            var oldXY = element.parent().data('x-y').split('|');
            var newXY = newParent.data('x-y').split('|');
            var animateProperty = null;
            if (oldXY[0] - newXY[0] === 0 && oldXY[1] - newXY[1] === 1) {
                animateProperty = 'left';
            } else if (oldXY[0] - newXY[0] === 0 && oldXY[1] - newXY[1] === -1) {
                animateProperty = 'right';
            } else if (oldXY[0] - newXY[0] === 1 && oldXY[1] - newXY[1] === 0) {
                animateProperty = 'top';
            } else if (oldXY[0] - newXY[0] === -1 && oldXY[1] - newXY[1] === 0) {
                animateProperty = 'bottom';
            }
            element.appendTo(newParent);
            var tempCss = {};
            tempCss[animateProperty] = '100%';
            element.css(tempCss);
            var anim = {};
            anim[animateProperty] = '0px';
            if (element.is(':animated')) {
                element.finish();
            }
            // value minus 100 to allow a lag up to 100 milliseconds.
            var lagFixer = 100;
            element.animate(anim, element.is('.player')
                ? bomberman_ui.movementSpeed - lagFixer
                : bomberman_ui.bombMovementSpeed - lagFixer, function(){
                element.css(animateProperty, '');
            });
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
                    $('#field').empty();
                    $('#field').show();
                    if(bomberman_ui.isTouchDevice()){
                        bomberman_ui.setMinViewPort(data);
                        $('#arrowControlls').show();
                    }
                },

                finished: function (data) {
                    var endSound;
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
                    }
                    window.setTimeout(function(){
                        swal(text);
                        $('#roomcontrols').show();
                        $('#roomList').show();
                        $('#field').hide();
                        $('#arrowControlls').hide();
                        if(!bomberman_storage.getMuted()){
                            endSound.play();
                        }
                    }, 700);  // give the player some time to realize he died
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
                    var fieldDiv = $('#field');
                    if (fieldDiv.find('div').length === 0) {
                        // init map creation
                        for (var i = 0; i < field.cells.length; i++) {
                            for (var j = 0; j < field.cells[i].length; j++) {
                                fieldDiv.append($('<div data-x-y="' + i + '|' + j + '" class="fieldCell"></div>'));
                            }
                            fieldDiv.append($('<div class="clear">'));
                        }
                    }

                    var minWidth = (11 + ((field.players-2)*2))*32+180;
                    if(field.players > 2){
                        $('#main').css('min-width', minWidth+'px');
                    }

                    $('.block_').addClass('delete');
                    var hueRotate = 0;
                    for (i = 0; i < field.cells.length; i++) {
                        for (j = 0; j < field.cells[i].length; j++) {
                            var inCells = field.cells[i][j].inCells;
                            for (var r = 0; r < inCells.length; r++) {
                                var inCell = inCells[r];
                                var inCellDom = $('div[data-id="'+inCell.id+'"]');
                                // first creation of inCell
                                if (inCellDom.length === 0) {
                                    inCellDom = $('<div class="block_ '+inCell.class+'" data-id="'+inCell.id+'"></div>');
                                    if(inCell.class === 'player'){
                                        hueRotate = hueRotate+(360/field.players);
                                        inCellDom.css('filter', 'hue-rotate('+hueRotate +'deg)');
                                    }
                                    if(inCell.class === 'explosion'){
                                        if(!bomberman_storage.getMuted()){
                                            // so that we have mutliple explosion ;)
                                            bomberman_ui.bombAudio.cloneNode(true).play();
                                        }
                                    }
                                    inCellDom.css('z-index', inCell.displayPriority);
                                    inCellDom.appendTo('div.fieldCell[data-x-y="'+i+'|'+j+'"]');
                                }
                                if (inCell.class === 'player' && !inCell.alive) {
                                    inCellDom.css('background-image', 'url("./img/rip.gif")');
                                    if (!inCellDom.data('deadPlayed')) {
                                        if(!bomberman_storage.getMuted()) {
                                            bomberman_ui.deadAudio.play();
                                        }
                                        inCellDom.data('deadPlayed', true);
                                    }
                                }
                                if (!inCellDom.parent().is('div[data-x-y="'+i+'|'+j+'"]')) {
                                    bomberman_ui.moveAnimate(inCellDom, $('div.fieldCell[data-x-y="'+i+'|'+j+'"]'));
                                }
                                inCellDom.removeClass('delete');
                            }
                        }
                    }
                    $('.delete').remove();
                }
            },

            player_js: {
                movementSpeed: function (movementSpeed) {
                    bomberman_ui.movementSpeed = movementSpeed;
                }
            }
        }
    };

    bomberman_socket.init();
    bomberman_ui.init();
})(jQuery);