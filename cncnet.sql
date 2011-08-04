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
    logout      DATETIME NULL,              -- time of logout
    
    pass_hash   VARCHAR(41) NOT NULL,       -- 41 = SHA-1 with null terminator
    pass_salt   VARCHAR(41) NOT NULL,       -- 41 = SHA-1 with null terminator
    sesh_key    VARCHAR(41) NULL,           -- 41 = SHA-1 with null terminator
    sesh_time   DATETIME NULL,              -- when session was created, expire for inactivity
    email       VARCHAR(255) NOT NULL       -- Probably big enough for any email
);

CREATE INDEX players_active ON players (active);
CREATE INDEX players_logout ON players (logout);

CREATE UNIQUE INDEX players_name ON players (nickname);
CREATE INDEX players_sessions ON players (sesh_key);

CREATE TABLE rooms (
    id          INTEGER PRIMARY KEY,
    game_id     INTEGER NOT NULL,
    title       TEXT NOT NULL,              -- defaults to "<nickname>'s Room" for now
    player_id   INTEGER NOT NULL,           -- the owner of the room
    max         INTEGER NOT NULL,           -- maximum number of players, cnc95 = 6, others = 8
    created     DATETIME NOT NULL, 
    started     DATETIME DEFAULT NULL,      -- start flag
    
    latestart   BOOLEAN DEFAULT FALSE,      -- late start enabled
    password    VARCHAR(41) DEFAULT "",   -- 41 SHA-1 hash of password. No need for salt here.

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

CREATE TABLE events (
    id          INTEGER PRIMARY KEY,
    player_id   INTEGER NOT NULL,           -- Destination id.
    type        VARCHAR(8) NOT NULL,        -- type of event
    time        DATETIME NOT NULL,          -- time of event
    room        INTEGER NOT NULL,           -- room event is related to
    user        INTEGER NULL,               -- user event is related to
    param       TEXT NULL,                  -- extra data
    
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (room) REFERENCES rooms(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user) REFERENCES players(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX events_player ON events (player_id);

INSERT INTO games VALUES (-1, 'Chat', '');
INSERT INTO games VALUES (NULL, 'Command & Conquer', 'cnc95');
INSERT INTO games VALUES (NULL, 'Red Alert', 'ra95');
INSERT INTO games VALUES (NULL, 'Tiberian Sun', 'ts');
INSERT INTO games VALUES (NULL, 'Red Alert 2', 'ra2');
INSERT INTO games VALUES (NULL, 'Red Alert 2: Yuri''s Revenge', 'ra2yr');

INSERT INTO rooms VALUES ( 0, -1, "Lobby", -1, -1, "2011-08-04 02:10:00", NULL, NULL, "" );
