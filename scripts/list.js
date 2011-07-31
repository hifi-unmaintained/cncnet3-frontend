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
