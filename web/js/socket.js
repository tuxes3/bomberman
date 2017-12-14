(function($) {

    var bomberman_socket_request = {
        listRooms: function () {
            return JSON.stringify({
                name: 'room',
                event: 'getAll',
                data: null
            });
        },
        createRoom: function (maxPlayers) {
            return JSON.stringify({
                name: 'room',
                event: 'create',
                data: {
                    maxPlayers: maxPlayers
                }
            });
        },
        joinRoom: function (uniqueId) {
            return JSON.stringify({
                name: 'room',
                event: 'join',
                data: {
                    uniqueId: uniqueId
                }
            });
        },
        movePlayer: function (direction) {
            return JSON.stringify({
                name: 'player',
                event: 'move',
                data: {
                    direction: direction
                }
            });
        },
        plantBomb: function () {
            return JSON.stringify({
                name: 'player',
                event: 'plant',
                data: null
            });
        }
    };

    var bomberman_ui = {

        nextMovement: null,
        lastWantedMovement: null,
        waitingForNextMove: null,

        init: function() {
            $('#createRoom').on('click', bomberman_ui.createRoom);
            $(document).keypress(bomberman_ui.onKeyPres);
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

        onKeyPres: function (e) {
            var char = String.fromCharCode(e.which);
            var _ = bomberman_ui;
            if (['w', 'a', 's', 'd'].indexOf(char) >= 0) {
                var now = Date.now();
                if (_.nextMovement === null || _.nextMovement <= now) {
                    if (_.waitingForNextMove !== null) {
                        clearTimeout(_.waitingForNextMove);
                        _.waitingForNextMove = null;
                    }
                    bomberman_socket.send(bomberman_socket_request.movePlayer(char));
                } else {
                    _.lastWantedMovement = char;
                    if (_.waitingForNextMove == null) {
                        _.waitingForNextMove = setTimeout(function() {
                            bomberman_socket.send(bomberman_socket_request.movePlayer(bomberman_ui.lastWantedMovement));
                            bomberman_ui.waitingForNextMove = null;
                        }, _.nextMovement - now)
                    }
                }
            } else if ([' '].indexOf(char) >= 0) {
                bomberman_socket.send(bomberman_socket_request.plantBomb());
            }
        }
    };

    var bomberman_socket = {
        connection: null,

        init: function () {
            this.connection = new WebSocket('ws://localhost:8009');
            this.connection.onmessage = this.onMessage;
            this.connection.onopen = this.onOpen;
        },

        onOpen: function (e) {
            this.send(bomberman_socket_request.listRooms());
        },

        onMessage: function (e) {
            var message = JSON.parse(e.data);
            bomberman_socket.handler[message.name][message.event](message.data);
        },

        send: function (request) {
            this.connection.send(request);
        },

        handler: {
            room_js: {
                list: function (roomList) {
                    var roomListDiv = $('#roomList');
                    roomListDiv.empty();
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
                    console.log(field);
                    var fieldDiv = $('#field');
                    fieldDiv.empty();
                    for (var i = 0; i < field.cells.length; i++) {
                        for (var j = 0; j < field.cells[i].length; j++) {
                            var inCells = field.cells[i][j].inCells;
                            var onField = $('<div class="block"></div>');
                            for (var r = 0; r < inCells.length; r++) {
                                // TODO: priority
                                onField.css('background-color', inCells[r].class === 'player' ? 'blue' : inCells[r].class === 'bomb' ? 'black' : inCells[r].class === 'explosion' ? 'yellow' : 'brown');
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
