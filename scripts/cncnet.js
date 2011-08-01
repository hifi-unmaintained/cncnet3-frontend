/*
 * Copyright (c) 2011 Toni Spets <toni.spets@iki.fi>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

$(document).ready(function() {
    $('.cncnet-dialog button').click(function() {
        $('#cncnet-overlay').show();

        var params = new Object();

        /* get all possible input elements to params */
        $(this).closest('.cncnet-view').find('input').each(function() {
            params[$(this).attr('name')] = $(this).val();
        });

        var controller = ($(this).closest('.cncnet-view').attr('id').match('[^-]+$'));
        var action = $(this).val();

        if (controller == 'error') {
            controller = 'index';
            action = 'init';
            params = {};
        }

        cncnet_ajax(controller, action, params);
    });

    $('#cncnet-rooms ul.cncnet-rooms').data('list', CnCNet_List($('#cncnet-rooms ul.cncnet-rooms'), function(el) {
        $('#cncnet-rooms input[name=room]').val($(el).data('id'));
        var list = $('#cncnet-rooms ul.cncnet-players').data('list');
        var players = $(el).data('players');
        list.reset();
        for (var i in players) {
            var player = players[i];
            var ready = '';
            if (player.ready) {
                ready = 'READY';
            }
            var li = list.append({
                nickname: player.nickname,
                ready: ready
            });
            if (typeof player.owner !== 'undefined' && player.owner) {
                li.addClass('owner');
            }
        }
        return true;
    }, function(el) {
        $('#cncnet-rooms button[value=join]').click();
    }));

    $('#cncnet-rooms ul.cncnet-players').data('list', CnCNet_List($('#cncnet-rooms ul.cncnet-players')));

    $('#cncnet-room ul.cncnet-players').data('list', CnCNet_List($('#cncnet-room ul.cncnet-players'), function(el) {
        $('#cncnet-room input[name=player]').val($(el).data('id'));
        return true;
    }));

    cncnet_ajax('index', 'init');
});

function cncnet_view(name, data)
{
    $('.cncnet-view, #cncnet-overlay').hide();

    if (typeof data == 'object' && typeof data.refresh != 'undefined') {
        $('.cncnet-dialog').data('poll', setTimeout(function() { cncnet_ajax(name); }, data.refresh * 1000));
    }

    if (name == 'player' && typeof data == 'object') {
        $('#cncnet-player input[name=port]').val(data.port);
    }
    else if (name == 'rooms' && typeof data == 'object') {
        $('#cncnet-rooms ul.cncnet-players').data('list').reset();
        var list = $('#cncnet-rooms ul.cncnet-rooms').data('list');
        list.reset();
        for (var i in data.rooms) {
            var room = data.rooms[i];
            var li = list.append({
                title: room.title,
                cur: room.players.length,
                max: room.max
            });
            li.addClass(room.game);
            if (room.started) {
                li.addClass('closed');
            }
            li.data('id', room.id);
            li.data('players', room.players);
        }
        var selected = $('#cncnet-rooms input[name=room]').val();
        if (selected) {
            $('#cncnet-rooms ul.cncnet-rooms li').each(function() {
                if ($(this).data('id') == selected) {
                    $(this).trigger('click');
                }
            });
        }
    }
    else if (name == 'game' && typeof data == 'object') {
        $('#cncnet-game p.icons').empty();
        for (var i in data.games) {
            var game = data.games[i];
            var button = $('<button class="'+game.protocol+'" type="button" name="game" value="'+game.id+'"></button>');
            button.click(function() {
                $('#cncnet-overlay').show();
                cncnet_ajax('game', 'select', { game_id: $(this).val() });
            });
            $('#cncnet-game p.icons').append(button);
        }
    }
    else if (name == 'room' && typeof data == 'object') {
        $('#cncnet-room h3').html(data.room.title);
        $('#cncnet-room h3').removeClass('ra95').removeClass('cnc95').removeClass('ts').removeClass('ra2').removeClass('ra2yr');
        $('#cncnet-room h3').addClass(data.room.game);
        $('#cncnet-room ul.cncnet-players').data('list').reset();
        var list = $('#cncnet-room ul.cncnet-players').data('list');
        list.reset();
        for (var i in data.room.players) {
            var player = data.room.players[i];
            var ready = '';
            if (player.ready) {
                ready = 'READY';
            }
            var li = list.append({
                nickname: player.nickname,
                ready: ready
            });
            if (typeof player.owner != 'undefined' && player.owner) {
                li.addClass('owner');
            }
            li.data('id', player.id);
        }

        var selected = $('#cncnet-room input[name=player]').val();
        if (selected) {
            $('#cncnet-room ul.cncnet-players li').each(function() {
                if ($(this).data('id') == selected) {
                    $(this).trigger('click');
                }
            });
        }
    }
    else if (name == 'launch' && typeof data == 'object') {
        $('#cncnet-launch a').attr('href', data.uri);
    }

    $('#cncnet-'+name).show();
}

function cncnet_ajax(controller, action, params)
{
    var path = controller;
    if (typeof action === 'string') {
        path += '/' + action;
    }
    clearTimeout($('.cncnet-dialog').data('poll'));
    $.ajax(path, {
        dataType: 'json',
        data: params,
        success: function(data) {
            cncnet_view(data.view, data);
        },
        error: function() {
            cncnet_view('error');
        }
    });
}
