define([
  'jquery',
  'lodash'
], function ($, _) {

  // we have acceess to cerrent Modal from element.Modal
  // we have acceess to cerrent linkElem / linkEl from element.currentTarget

  return {
    lookCallback: lookCallback,
    lookParseTemplate: lookParseTemplate
  };

  // execute AFTER append modal into DOM
  function lookCallback(element) {
    try {
      jQuery(element.Modal.modal[0]).find('.looks').bxSlider({
        controls: true,
        pager: false,
        swipeThreshold: 100,
        nextText: '',
        prevText: ''
      });
    } catch (err) {
      console.log(err);
    }
  }

  // execute BEFORE append modal into DOM
  function lookParseTemplate(element) {
    var modelJson = JSON.parse(element.currentTarget.getAttribute('data-modal-json'));
    var htmlString = element.Modal.modal[0].outerHTML;

    element.Modal.modal = $(_.template(htmlString, {
      'imports': {
        '$': $,
        'Modal': element.Modal
      }
    })(modelJson));
  }

});
