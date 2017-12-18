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
        }
    };

    var bomberman_ui = {

        bombAudio: new Audio('./sound/bomb.mp3'),
        deadAudio: new Audio('./sound/dead.mp3'),

        lastMoved: null,
        movementSpeed: null,
        lastWantedMovement: null,
        waitingForNextMove: null,

        init: function () {
            $('#createRoom').on('click', bomberman_ui.createRoom);
            $(document).keydown(bomberman_ui.onKeyDown);
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
            //console.log("keycode:" + keycode);

            // See
            // https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/which
            // https://www.w3schools.com/jsref/tryit.asp?filename=tryjsref_event_key_keycode
            // https://www.key-shortcut.com/schriftsysteme/35-symbole/pfeile/
            if ([87,65,83,68,40,38,37,39].indexOf(keycode) >= 0) {
                if(keycode == 87 || keycode ==38){
                    // UP
                    dir = '\u2191';
                }
                if(keycode == 65 || keycode ==37){
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
            var width = element.width();
            element.appendTo(newParent);
            var tempCss = {
                'position': 'relative'
            };
            tempCss[animateProperty] = width+'px';
            element.css(tempCss);
            var anim = {};
            anim[animateProperty] = '0px';
            element.animate(anim, element.is('.player')
                ? bomberman_ui.movementSpeed === null ? 270 : bomberman_ui.movementSpeed - 30
                : 570, function(){
                element.css('position', '');
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
        },

        onOpen: function (e) {
            this.send(bomberman_socket_request.init());
        },

        onMessage: function (e) {
            var message = JSON.parse(e.data);
            bomberman_socket.handler[message.name][message.event](message.data);
        },

        send: function (request) {
            this.connection.send(request);
        },

        handler: {
            game_js: {
                started: function (data) {
                    console.log('started');
                    $('#roomcontrols').hide();
                    $('#roomList').hide();
                    $('#field').empty();
                    $('#field').show();
                },

                finished: function (data) {
                    console.log('finished');
                    $('#roomcontrols').show();
                    $('#roomList').show();
                    $('#field').hide();
                    // null: close due to inactivity
                    if (data !== null) {
                        var text = 'You ' + (data.won ? 'won' : 'lose') + '!';
                        console.log(text);
                    }
                }
            },

            room_js: {
                list: function (roomList) {
                    var roomListDiv = $('#roomList');
                    roomListDiv.empty();
                    console.log(roomList);
                    for (var i = 0; i < roomList.length; i++) {
                        roomListDiv.append($(
                            '<a href="#" data-unique-id="'+roomList[i].uniqueId+'">Room #'+i+': '+roomList[i].name+' ('+roomList[i].connectedPlayers+'/'+roomList[i].maxPlayers+')</a>'
                        ).on('click', bomberman_ui.joinRoom));
                        roomListDiv.append($('<span> - </span>'));
                        roomListDiv.append($(
                            '<a href="#" data-unique-id="'+roomList[i].uniqueId+'">Leave</a>'
                        ).on('click', bomberman_ui.leaveRoom));
                        roomListDiv.append('<br />');
                    }
                }
            },

            message_js: {
                warning: function (message) {
                    console.log(message);
                },
                info: function (message) {
                    console.log(message);
                }
            },

            field_js: {
                update: function (field) {
                    console.log({event: 'field_js.update', field: field});
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
                    $('.block').addClass('delete');
                    for (i = 0; i < field.cells.length; i++) {
                        for (j = 0; j < field.cells[i].length; j++) {
                            var inCells = field.cells[i][j].inCells;
                            for (var r = 0; r < inCells.length; r++) {

                                var inCell = inCells[r];
                                var inCellDom = $('div[data-id="'+inCell.id+'"]');
                                // first creation of inCell
                                if (inCellDom.length === 0) {
                                    var image = null;
                                    var color = null;
                                    if(inCell.class === 'player' && inCell.alive){
                                        image = 'url("./img/man.gif")';
                                    } else
                                    if( inCell.class === 'bomb'){
                                        image = 'url("./img/bomb.gif")';
                                    } else
                                    if(inCell.class === 'fixblock'){
                                        image = 'url("./img/fixBlock.gif")';
                                    } else
                                    if(inCell.class === 'explosion'){
                                        image = 'url("./img/explosion.gif")';
                                        bomberman_ui.bombAudio.play();
                                    } else
                                    if(inCell.class === 'bombitem'){
                                        image = 'url("./img/twobomb.gif")';
                                        color = '#c5ffbc';
                                    } else
                                    if(inCell.class === 'shoeitem'){
                                        image = 'url("./img/shoe.gif")';
                                        color = '#c5ffbc';
                                    } else
                                    if(inCell.class === 'explosionradiusitem'){
                                        image = 'url("./img/bombsize_lvlup.gif")';
                                        color = '#c5ffbc';
                                    } else
                                    if (inCell.class === 'block') {
                                        image = 'url("./img/block.gif")';
                                    } else
                                    if (inCell.class === 'movebombitem') {
                                        image = 'url("./img/kickitemg.gif")';
                                        color = '#c5ffbc';
                                    }
                                    inCellDom = $('<div class="block" data-id="'+inCell.id+'"></div>');
                                    inCellDom.addClass(inCell.class);
                                    inCellDom.css('background-image', image);
                                    inCellDom.css('z-index', inCell.displayPriority);
                                    if(color != null){
                                        inCellDom.css('background-color', color);
                                    }
                                    inCellDom.appendTo('div.fieldCell[data-x-y="'+i+'|'+j+'"]');
                                }
                                if (inCell.class === 'player' && !inCell.alive) {
                                    inCellDom.css('background-image', 'url("./img/rip.gif")');
                                    if (!inCellDom.data('deadPlayed')) {
                                        bomberman_ui.deadAudio.play();
                                        inCellDom.data('deadPlayed', true);
                                    }
                                }
                                if (!inCellDom.parent().is('div[data-x-y="'+i+'|'+j+'"]')) {
                                    bomberman_ui.moveAnimate(inCellDom, $('div.fieldCell[data-x-y="'+i+'|'+j+'"]'));
                                    // inCellDom.detach();
                                    // inCellDom.appendTo('div.fieldCell[data-x-y="'+i+'|'+j+'"]');
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
