Checker-Bomberman Game
======================

This game was developed as an assignement for the module "BTI7054 - Web programming" at the Berner Fachhochschule (BFH).

It consists of a backend (written in PHP) and a  frontend(HTML5 + CSS with help of jQuery and javascript).  
They communicate with each other through websockets.

The game is in the style of the original NES Bomberman game. (Which we both never played, since we are too young :P ).

Our version supports multiplayer up to 10 players.   
The goal is to be the last player alive. 

How to play
-----------
1. Create room with the desired amount of players.
2. Wait for the other players to join. The game starts as soon as the room is full.
3. Use WASD or arrow keys to navigate and space to plant a bomb. (Or arrow buttons on touch enabled devices)
4. Grey blocks are solid, blue blocks can be destroyed and they will randomly release an item.  
  (Which enables you to move faster/ have a bigger explosion / have multiple bombs or kick the bomb)
5. Players die if they come in contact with the explosion (beware of the short afterglow!).
6. Last player alive wins.

Inactive rooms are automatically deleted after a while.

Screenshot
----------
![screenshot](https://i.imgur.com/9AP2En3.png)


Demo
-----
A working demo can be found at https://bomberman.functions.ch

Installation
------------
Checkout project:  `git clone https://github.com/Tuxes3/bomberman.git`

Prepare workspace:  `make install`

Start servers: `make server`, `make websocket` 

License
-------
As long as you retain this notice you can do whatever you want with this stuff.   
If we meet some day, and you think this stuff is worth it, you can buy us a beer in return.  
Nicolo Singer, Lukas Müller.


