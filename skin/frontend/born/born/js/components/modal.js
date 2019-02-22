define([
  'jquery'
], function($) {

  function init() {}

  $(function() {
    var body = $('body');
    var overlay = $('<div class="overlay"></div>');
    overlay.appendTo(body);

    // it's local cache of modal templates
    var namesModalCache = {};

    // list of all modals on page
    var ModalsList = {};

    /**
     * @desc list of names of templates
     * @type {json} modalNames
     */
    var modalsNames = {
      'shippingReturns': {
        templateUrl: 'shippingReturns.html',
        withModel: false
      },
      'shade-guide': {
        templateUrl: 'shade-guide.html',
        withModel: false
      },
      'undertone-guide': {
        templateUrl: 'undertone-guide.html',
        withModel: false
      },
      'quickview-php': {
        templateUrl: 'modal-empty.html',
        withModel: false
      },
      'readmore-php': {
        templateUrl: 'modal-empty.html',
        withModel: false
      },
      'event-php': {
        templateUrl: 'modal-empty.html',
        withModel: false
      },
      'shareModal-php': {
        templateUrl: 'modal-empty.html',
        withModel: false
      },
      'tierpricing-modal': {
        templateUrl: 'tierpricing-modal.html',
        withModel: false
      },
      'proform-manual-modal': {
        templateUrl: 'proform-manual-modal.html',
        withModel: false
      },
      'education-modal': {
        templateUrl: 'modal-empty.html',
        withModel: false
      },
      'information-modal': {
        templateUrl: 'information-modal.html',
        withModel: false
      },
      'look': {
        templateUrl: 'look.html',
        withModel: true,
        // remove below after backend added real json
        mockData: JSON.stringify({
          user: {
            name: '@heidimakeupartist',
            avaImgUrl: '/skin/frontend/born/born/images/look-ava.png',
            date: '4 hours ago',
            message: '<p>heidimakeupartistIt\'s all in the eyes. Makeup details to come! <a alt="hash-tag">#heidimakeupartist</a></p>'
          },
          products: [
            {
              imgUrl: '/skin/frontend/born/born/images/look_product_1.jpg',
              name: '4-in-1 Pressed Mineral Powder Foundation',
              price: 35.00
            },
            {
              imgUrl: '/skin/frontend/born/born/images/look_product_2.jpg',
              name: 'Pur Intensity Gel Eyeliner',
              price: 49.99
            }
          ],
          lookImages: [
            '/skin/frontend/born/born/images/look-modal-mock.jpg',
            '/skin/frontend/born/born/images/look-modal-mock-2.jpg',
            '/skin/frontend/born/born/images/look-modal-mock-3.jpg'
          ]
        })
      }

    };

    init = function() {
      var path = '/skin/frontend/born/born/js/app/modals/';


      bindAllModals();

      function bindAllModals() {
        // find all elements in DOM and init each modal and links for open them
        $('[data-coherent-modal]').each(function(i, linkEl) {

          if (linkEl.getAttribute('data-modal-binded')) {
            return true;
          }
          /**
           *
           * @type {Modal}
           */
          var modalObj = new Modal(linkEl, i);

          /**
           * Url of template from link element attribute
           * @type {string}
           */
          var templateUrlAttr = linkEl.getAttribute('data-modal-url');

          /**
           * Name of template from link element attribute
           * @type {string}
           */
          var templateNameAttr = linkEl.getAttribute('data-modal-name');
          var name = templateUrlAttr || templateNameAttr;
          modalObj.name = name;
          // if data received from attr
          var modalJsonAttrr = linkEl.getAttribute('data-modal-json');

          // for mock data
          if (modalJsonAttrr !== null && modalsNames[templateNameAttr].withModel) {
            linkEl.setAttribute('data-modal-json', modalsNames[templateNameAttr].mockData);
          }

          // else if template from template url
          if (templateUrlAttr) {
            /**
             * @desc GET request to get a html file
             */
            $.get(path + templateUrlAttr, function(html) {
              modalObj.modal = $(html);
            });
          }

          // template from template name - from modalsNames object
          else if (templateNameAttr) {

            if (!namesModalCache[name]) {
              namesModalCache[name] = name;

              // named list of modals
              ModalsList[name] = [];
              /**
               * @desc GET request to get a html file
               */
              $.get(path + modalsNames[name].templateUrl, function(html) {
                modalsNames[name].modalTemplate = html;

                ModalsList[name].push(modalObj);

                var arrLenght = ModalsList[name].length;
                for (var i = 0; arrLenght > i; i++) {
                  ModalsList[name][i].modal = $(html);
                }

              });
            }
            else {
              if (!modalObj.modal && modalsNames[name].modalTemplate) {
                modalObj.modal = $(modalsNames[name].modalTemplate);
              }
              ModalsList[name].push(modalObj)
            }

          }

          // if template is undefined - throw an alert
          else {
            alert('Please, define url or name of modal window or remove "data-coherent-modal" attribute from element');
          }

          // bind on element (which inited modal) click - open a modal function
          $(linkEl).bind('click.modalClick', function (e) {
            modalObj.open();
          });

          linkEl.setAttribute('data-modal-binded', true);
        });
      }
    };

    /**
     * @class
     * @classdesc Creates a new Modal object
     * @param {*|jQuery|HTMLElement} linkEl
     * @returns {object} modalObj
     * @prop {*|jQuery|HTMLElement} modal - modal template
     * @prop {string} name - modal template name
     */
    function Modal(linkEl, id) {

      this.id = id;
      this.isRendered = false;
      var that = this;
      var lElem = $(linkEl);
      this.isOpened = false;

      /**
       * @desc runs once after click on link button
       * @function
       */
      this.render = function render() {
        //todo: add focus out ontap -> close modal mobile

        $(document)
          .on('click.stopPropModal', '.modal', function (e) {
            e.stopPropagation();
          })
          .on('click.closeOverlay', '.overlay', function (e) {
            if (e.target === this) {
              that.close();
            }
          });

        // _lodash parsing template here:
        lElem.trigger({
          type: 'onParse',
          Modal: this
        });


        if (overlay.has(this.modal).length === 0) {
          overlay.append(this.modal);
          this.modal.find('.close-btn:first').on('click', this.close.bind(this));
          this.modal.css('display', 'inline-block');

        }


        /**
         * @desc runs custom callback which define in some require.js module
         * and callback may be selft вas require.js module
         */

        this.isRendered = true;
        this.isOpened = true;
      };

      this.open = function open() {
        overlay.show();
        body.css('overflow', 'hidden');
        if (this.isRendered) {
          this.modal.css('display', 'inline-block');
        }
        else {
          this.render();
        }
        this.isOpened = true;

        lElem.trigger({
          type: 'mcallback',
          Modal: this
        });

        $(document).on('keyup', escClose);
        $(window).trigger('resize'); // for bxSlider
      };

      this.close = function close() {
        this.isOpened = false;
        overlay.hide();
        body.css('overflow', 'auto');
        if (this.modal) this.modal.hide();
        $(document).off('keyup', escClose);
      };

      this.getAllModals = function () {
        return ModalsList;
      };


      function escClose (evt) {
        if (evt.keyCode == 27) {
          evt.stopPropagation();
          evt.preventDefault();
          that.close();
        }
      }

    }

    // point of entry
    init();

  });

  return init;

});
