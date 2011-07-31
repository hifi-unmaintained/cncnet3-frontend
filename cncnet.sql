PRAGMA foreign_keys = ON;

CREATE TABLE games (
    id          INTEGER PRIMARY KEY,
    name        TEXT NOT NULL,
    protocol    TEXT NOT NULL
);

CREATE TABLE players (
    id          INTEGER PRIMARY KEY,
    nickname    TEXT NOT NULL,
    ip          TEXT NOT NULL,
    port        INTEGER NOT NULL,           -- port defaults to 8054 for now
    created     DATETIME NOT NULL, 
    active      DATETIME NOT NULL,          -- last message received
    logout      DATETIME NULL               -- time of logout
);

CREATE INDEX players_active ON players (active);
CREATE INDEX players_logout ON players (logout);

CREATE TABLE rooms (
    id          INTEGER PRIMARY KEY,
    game_id     INTEGER NOT NULL,
    title       TEXT NOT NULL,              -- defaults to "<nickname>'s Room" for now
    player_id   INTEGER NOT NULL,           -- the owner of the room
    max         INTEGER NOT NULL,           -- maximum number of players, cnc95 = 6, others = 8
    created     DATETIME NOT NULL, 
    started     DATETIME DEFAULT NULL,      -- start flag

    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX rooms_created ON rooms (created);
CREATE INDEX rooms_started ON rooms (started);

CREATE TABLE room_players (
    room_id     INTEGER NOT NULL,
    player_id   INTEGER NOT NULL,
    ready       INTEGER NOT NULL DEFAULT 0,

    PRIMARY KEY (room_id, player_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO games VALUES (NULL, 'Command & Conquer', 'cnc95');
INSERT INTO games VALUES (NULL, 'Red Alert', 'ra95');
INSERT INTO games VALUES (NULL, 'Tiberian Sun', 'ts');
INSERT INTO games VALUES (NULL, 'Red Alert 2', 'ra2');
INSERT INTO games VALUES (NULL, 'Red Alert 2: Yuri''s Revenge', 'ra2yr');
