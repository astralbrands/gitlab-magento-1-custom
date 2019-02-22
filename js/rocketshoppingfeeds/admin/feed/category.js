var taxonomyCategory = Class.create();
taxonomyCategory.prototype = {
    config: {},
    initialize: function (cfg) {
        this.config = cfg
    },

    fieldName: '',
    fieldStrings: {},
    toggleSelect: function(element, force_value) {
        var thisClass = this;
        var input_disable = $(element).siblings().grep(new Selector('.el_disabled')).first();
        // Toogle the hidden input (checkbox), if disable_value is sent, do not take the input hidden value
        if (force_value === undefined) {
            disable_value = ($(input_disable).value == '1') ? 0 : 1;
        } else {
            disable_value = force_value;
        }
        $(input_disable).value = disable_value;


        // Toggle input enabled / disabled
        $(element).up('div.category_row').select('.input-text').each(function(item) {
            if (!disable_value) {
                item.removeClassName('disabled');
                item.addClassName('enabled');
                element.parentNode.querySelector('.priority').removeClassName('disabled');
            } else {
                element.parentNode.querySelector('.priority').addClassName('disabled');
                item.removeClassName('enabled');
                item.addClassName('disabled');
            }
        });

        // Expand all children rows only on click per row
        if (force_value === undefined) {
            var carrot = $(element).siblings().grep(new Selector('.carrot')).first();
            if (carrot !== undefined && carrot.hasClassName('icon-carrot-closed')) {
                this.toggleShow(carrot, 'show');
            }
        }

        // Toggle all children rows
        //var ul = $(element).up().siblings().grep(new Selector('.category_list')).first();
        //if (ul !== undefined) {
        //    ul.select('.status').each(function(item) {
        //        thisClass.toggleSelect(item, disable_value);
        //    });
        //}

        //var priorityElement = $(input_value).parentNode.querySelector('.priority');
        //priorityElement.focus();
        //priorityElement.blur();

        // Update the checkbox icon
        var newString = disable_value ? this.fieldStrings['row_disabled'] : this.fieldStrings['row_enabled'];
        $(element).update(newString);
    },
    toggleShow: function(element, status) {
        var ul = $(element).up().siblings().grep(new Selector('.category_list')).first();
        var showUl = false,
            parentEl = element.parentNode;
        if (element.hasClassName('icon-carrot-opened')) {
            element.removeClassName('icon-carrot-opened');
            element.addClassName('icon-carrot-closed');
            parentEl.parentNode.querySelector('.category_list').removeClassName('opened');
            parentEl.parentNode.querySelector('.category_list').addClassName('closed');
        }else {
            showUl = true;
            element.removeClassName('icon-carrot-closed');
            element.addClassName('icon-carrot-opened');
            parentEl.parentNode.querySelector('.category_list').removeClassName('closed');
            parentEl.parentNode.querySelector('.category_list').addClassName('opened');
        }

        if (ul !== undefined) {
            if (status !== undefined) {
                if (status == 'show') {
                    showUl = true;
                } else if (status == 'hide') {
                    showUl = false;
                }
            }
            if (showUl) {
                ul.show();
            } else {
                ul.hide();
            }
        }
    },
    showAll: null,
    toggleShowAll: function(parent) {
        var thisClass = this;
        $$('.category_taxonomy_category .carrot').each(function(element) {
            if (thisClass.showAll === null || thisClass.showAll === false) {
                if(element.classList.contains('icon-carrot-closed')){
                    thisClass.toggleShow(element, 'show');
                }
            } else {
                if(element.classList.contains('icon-carrot-opened')){
                    thisClass.toggleShow(element, 'hide');
                }
            }
        });
        if (thisClass.showAll === null || thisClass.showAll === false) {
            thisClass.showAll = true;
            parent.update(thisClass.fieldStrings['collapse_all']);
        } else {
            thisClass.showAll = false;
            parent.update(thisClass.fieldStrings['expand_all']);
        }
    },
    toggleSelectAll: function(parent) {
        var thisClass = this,
            rows = $$('.category_taxonomy_category .status'),
            counter = 0,
            total = parseInt($$('.end')[0].innerHTML);

        $$('.adminhtml-rocketfeed-edit')[0].querySelectorAll('.scalable.save')[0].addClassName('loading');
        $$('.adminhtml-rocketfeed-edit')[0].querySelectorAll('.scalable.save')[1].addClassName('loading');
        $('div_categories_provider_taxonomy_by_category').addClassName('loading-gif');

        $$('.category_taxonomy_category .status').each(function(element) {
            var self = this, doBind = function() {
                if (parent.hasClassName('enable')) {
                    thisClass.toggleSelect(element, 1);
                } else {
                    thisClass.toggleSelect(element, 0);
                }
                counter++;
                $('progr').querySelector('.start').update(counter);
                if(counter == total){
                    $$('.adminhtml-rocketfeed-edit')[0].querySelectorAll('.scalable.save')[0].removeClassName('loading');
                    $$('.adminhtml-rocketfeed-edit')[0].querySelectorAll('.scalable.save')[1].removeClassName('loading');
                    $('div_categories_provider_taxonomy_by_category').removeClassName('loading-gif');
                }
                element.parentNode.querySelector('.el_disabled').click();
            };
            $.queue.add(doBind, this);
        });
        if (parent.hasClassName('enable')) {
            parent.removeClassName('enable');
            parent.addClassName('disable');
            thisClass.showAll = true;
            $$('.category_taxonomy_category_all').first().update(thisClass.fieldStrings['collapse_all']);
            $(parent).update(thisClass.fieldStrings['disable_all']);
        } else {
            parent.removeClassName('disable');
            parent.addClassName('enable');
            $(parent).update(thisClass.fieldStrings['enable_all']);
        }

    },
    autoFillChildren: function(element, input_class) {
        var text = $(element).value;
        var ul = $(element).up('.category_row').siblings().grep(new Selector('.category_list')).first();
        if (ul !== undefined) {
            $(ul).select('.input-text').each(function(field) {
                if ($(field).classList.contains(input_class) && !$(field).disabled && $(field).value == '') {
                    $(field).value = text;
                    changeTaxonomyData($(field).parentNode.parentNode.parentNode.parentNode.querySelector('.el_category').value, input_class, $(field));
                }
            });
        }
    }

};

$.queue = {
    _timer: null,
    _queue: [],
    add: function(fn, context, time) {
        var setTimer = function(time) {
            $.queue._timer = setTimeout(function() {
                time = $.queue.add();
                if ($.queue._queue.length) {
                    setTimer(time);
                }
            }, time || 2);
        }

        if (fn) {
            $.queue._queue.push([fn, context, time]);
            if ($.queue._queue.length == 1) {
                setTimer(time);
            }
            return;
        }

        var next = $.queue._queue.shift();
        if (!next) {
            return 0;
        }
        next[0].call(next[1] || window);
        return next[2];
    },
    clear: function() {
        clearTimeout($.queue._timer);
        $.queue._queue = [];
    }
};
