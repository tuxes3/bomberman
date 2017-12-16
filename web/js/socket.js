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

        createRoom: function (maxPlayers) {
            return this.prepare({
                name: 'room',
                event: 'create',
                data: {
                    maxPlayers: maxPlayers
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

        nextMovement: null,
        lastWantedMovement: null,
        waitingForNextMove: null,

        init: function () {
            $('#createRoom').on('click', bomberman_ui.createRoom);
            $(document).keydown(bomberman_ui.onKeyDown);
        },

        createRoom: function (e) {
            e.preventDefault();
            bomberman_socket.send(bomberman_socket_request.createRoom(
                $('#maxPlayer').val()
            ));
        },

        joinRoom: function (e) {
            e.preventDefault();
            bomberman_socket.send(bomberman_socket_request.joinRoom(
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
                //console.log("dir:" + dir);

                var now = Date.now();
                if (_.nextMovement === null || _.nextMovement <= now) {
                    if (_.waitingForNextMove !== null) {
                        clearTimeout(_.waitingForNextMove);
                        _.waitingForNextMove = null;
                    }
                    bomberman_socket.send(bomberman_socket_request.movePlayer(dir));
                } else {
                    _.lastWantedMovement = dir;
                    if (_.waitingForNextMove == null) {
                        _.waitingForNextMove = setTimeout(function() {
                            bomberman_socket.send(bomberman_socket_request.movePlayer(bomberman_ui.lastWantedMovement));
                            bomberman_ui.waitingForNextMove = null;
                        }, _.nextMovement - now)
                    }
                }
            } else if (keycode == 32) {
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
                    $('#lobby').hide();
                },

                finished: function (data) {
                    console.log('finished');
                    $('#lobby').show();
                    $('#field').empty();
                    var text = 'You ' + (data.won ? 'won' : 'lose') + '!';
                    alert(text);
                }
            },

            room_js: {
                list: function (roomList) {
                    var roomListDiv = $('#roomList');
                    roomListDiv.empty();
                    console.log(roomList);
                    for (var i = 0; i < roomList.length; i++) {
                        roomListDiv.append($(
                           '<a href="#" data-unique-id="'+roomList[i].uniqueId+'">#'+i+' Room ('+roomList[i].connectedPlayers+'/'+roomList[i].maxPlayers+')</a>'
                        ).on('click', bomberman_ui.joinRoom));
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
                    fieldDiv.empty();
                    for (var i = 0; i < field.cells.length; i++) {
                        for (var j = 0; j < field.cells[i].length; j++) {
                            var inCells = field.cells[i][j].inCells;
                            var onField = $('<div class="block"></div>');
                            for (var r = 0; r < inCells.length; r++) {
                                // TODO: priority
                                // TODO LUKAS
                                onField.css('background-color', inCells[r].class === 'player' ? 'blue' : inCells[r].class === 'bomb' ? 'black' : inCells[r].class === 'fixblock' ? 'violet' : inCells[r].class === 'explosion' ? 'yellow' : inCells[r].class === 'bombitem' ? 'pink' : inCells[r].class === 'shoeitem' ? 'lightblue' : inCells[r].class === 'explosionradiusitem' ? 'orange' : 'brown');

                                if (inCells[r].class === 'player' && !inCells[r].alive) {
                                    onField.text('RIP');
                                }
                            }
                            fieldDiv.append(onField);
                        }
                        fieldDiv.append($('<div class="clear">'));
                    }
                }
            },

            player_js: {
                nextMovement: function (timestamp) {
                    bomberman_ui.nextMovement = timestamp;
                }
            }
        }
    };

    bomberman_socket.init();
    bomberman_ui.init();
})(jQuery);
