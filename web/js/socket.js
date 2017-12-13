
// $(document).keypress(function(e) {
//     if (e.keyCode === 119) { // w
//         conn.send('w');
//     } else if (e.keyCode === 97) { // a
//         conn.send('a');
//     } else if (e.keyCode === 115) { // s
//         conn.send('s');
//     } else if (e.keyCode === 100) { // d
//         conn.send('d');
//     }
// });

var bomberman_socket_request = {
    listRooms: function () {
        return JSON.stringify({
            name: 'room',
            event: 'list',
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
    }
};

var bomberman_ui = {
    init: function() {
        $('#listRooms').on('click', function (e) {
            e.preventDefault();
            bomberman_socket.connection.send(bomberman_socket_request.listRooms())
        });
        $('#createRoom').on('click', function (e) {
            e.preventDefault();
            bomberman_socket.connection.send(bomberman_socket_request.createRoom(
                $('#maxPlayer').val()
            ))
        });
    }
};

var bomberman_socket = {
    connection: null,

    init: function () {
        this.connection = new WebSocket('ws://localhost:8009');
        this.connection.onmessage = this.onMessage;
    },

    onMessage: function (e) {
        var message = JSON.parse(e.data);
        bomberman_socket.handler[message.name][message.event](message.data);
    },

    handler: {
        room_js: {
            list: function (roomList) {
                console.log(roomList);
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
            }
        }
    }


};

(function($) {
    bomberman_socket.init();
    bomberman_ui.init();
})(jQuery);
