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

function CnCNet_List(jq, click, dblclick) {
    var self = {};

    self.list = jq;
    self.click = click;
    self.dblclick = dblclick;
    self.tpl = self.list.children('li.template').clone().removeClass('template');

    self.my_click = function () {
        if (typeof self.click === 'function' && self.click(this)) {
            if (!$(this).hasClass('selected')) {
                $(this).parent().children('li').removeClass('selected');
                $(this).addClass('selected');
            }
        }

        return false;
    };

    self.my_dblclick = function () {
        if (typeof self.dblclick === 'function') {
            self.dblclick();
        }

        return false;
    };

    self.reset = function () {
        self.list.children('li').not('.template').remove();
    };

    self.append = function (data) {
        var k, li = self.tpl.clone();

        for (k in data) {
            li.find('.' + k).html(data[k]);
        }

        li.click(self.my_click);
        li.dblclick(self.my_dblclick);
        li.select(function() { alert('select'); return false; });

        self.list.append(li);

        return li;
    };

    return self;
}
