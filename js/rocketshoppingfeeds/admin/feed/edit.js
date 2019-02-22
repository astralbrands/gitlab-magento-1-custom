var ByCategory = Class.create();
ByCategory.prototype = {
    initialize: function (cfg) {
        this._categoryFillArray = cfg;
        this._grid = new Array();
    },

    getGrid: function (htmlId) {
        if (this._grid[htmlId] == undefined) {
            this._grid[htmlId] = $('grid' + htmlId);
        }
        return this._grid[htmlId];
    },

    observeFillBtn: function (htmlId) {
        var mycls, val, parent_class;
        var self = this;

        this.getGrid(htmlId).select('select').each(function (obj) {
            parent_class = obj.parentNode.className;
            val = obj.options[obj.selectedIndex].value;

            if (self._categoryFillArray[val] != undefined) {
                mycls = parent_class.replace('category', 'value');
                $$('.' + mycls + ' input[type=text]').each(function (elm) {
                    elm.value = self._categoryFillArray[val];
                });
            }
        });
    },

    observeCopyBtn: function (htmlId, fromHtmlSectionId, inputNamePrefix, arrayRowObj) {

        var d, _id, mycls, parent_class, newElement, data, td, newRows = new Array();
        var tbody = new Element('tbody');
        var oldTable = this.getGrid(htmlId);
        var newTable = new Element('table', {'cellpadding': 0, 'cellspacing': 0, 'class': 'border'});

        $$(fromHtmlSectionId + ' select').each(function (obj, index) {

            d = new Date();
            _id = '_' + d.getTime() + '_' + d.getMilliseconds() + index;
            parent_class = obj.parentNode.className;
            newElement = new Element('tr').writeAttribute('id', _id);

            // order elements
            mycls = parent_class.replace('category', 'order');
            $$('.' + mycls + ' input[type=text]').each(function (elm) {
                data = elm.parentNode.cloneNode(true);
            });
            data.writeAttribute('class', _id + 'order');
            data.firstChild.name = inputNamePrefix + '[' + _id + '][order]';
            newElement.appendChild(data);

            // category elements
            data = obj.parentNode.clone(true);
            data.writeAttribute('class', _id + 'category');
            data.firstChild.name = inputNamePrefix + '[' + _id + '][category]';
            data.firstChild.selectedIndex = obj.selectedIndex;
            newElement.appendChild(data);

            // value elements
            mycls = parent_class.replace('category', 'value');
            $$('.' + mycls + ' input[type=text]').each(function (elm) {
                data = elm.parentNode.cloneNode(true);
            });
            data.writeAttribute('class', _id + 'value');
            data.firstChild.name = inputNamePrefix + '[' + _id + '][value]';
            newElement.appendChild(data);

            // action elements
            var button = new Element('button');
            var span = new Element('span');
            span.textContent = 'Delete';
            button.appendChild(span);
            button.writeAttribute('onclick', 'arrayRow' + htmlId + '.del(\'' + _id + '\')');
            button.writeAttribute('class', 'scalable delete');
            button.writeAttribute('type', 'button');

            data = new Element('td');
            data.appendChild(button);
            newElement.appendChild(data);

            // stash it
            newRows.push(newElement);
        });

        // copy over the header / footer of table
        this.getGrid(htmlId).select('table').each(function (table) {
            row_header = table.rows[0];
            row_footer = table.rows[table.rows.length - 1];
        });

        // rebuild table
        tbody.appendChild(row_header);
        newRows.each(function (row) {
            tbody.appendChild(row);
        });
        tbody.appendChild(row_footer);

        // finish build and replace the table
        newTable.appendChild(tbody);
        oldTable.select('table').each(function (o) {
            oldTable.replaceChild(newTable, o);
        });
    }
};

function getAnchor(tab, anchor) {
    var elm = $('form_tabs_rocketshoppingfeeds_'+ tab);
    elm.click();
    window.location.href = window.location.href + '#'+ anchor;
}


var FeedEdit = Class.create();
FeedEdit.prototype = {
    storeList: {},
    currentCurrency: {store: null, currency: null},
    initialize: function () {},
    getStoreList: function() {
        return this.storeList;
    },
    setStoreList: function(jsonData) {
        var list = jsonData.evalJSON();
        this.storeList = list;
    },
    getCurrencySelect: function() {
        return $$('#general_currency').first();
    },
    saveStoreChange: function(element) {
        if (this.currentCurrency.store === null) {
            this.currentCurrency.store = $(element).getValue();
            this.currentCurrency.currency = this.getCurrencySelect().getValue();
        }
    },
    setStoreChange: function(element) {
        var self = this;
        var storeId = $(element).getValue();
        var storeList = this.getStoreList();
        var currencySelect = this.getCurrencySelect();
        var newCurrencyId = null;

        if (storeId == this.currentCurrency.store) {
            newCurrencyId = this.currentCurrency.currency;
        }

        $H(storeList).each(function(item) {
            if (item[0] == storeId) {
                currencySelect.options.length = 0;

                $H(item[1]).each(function(pair) {
                    option = pair.value;
                    var newOption = new Element('option', {value: option.value}).update(option.label);
                    if (option.default && newCurrencyId === null) {
                        newCurrencyId = option.value;
                    }

                    if (option.value == newCurrencyId) {
                        newOption.selected = true
                    }
                    currencySelect.insert(newOption);

                });
                return;
            }
        });




    }
}