define([
  'jquery',
  'backbone'
], function ($, Backbone) {

  "use strict";

  var VideoInImageView = Backbone.View.extend({
    initialize: function () {
    },
    init: function () {
      $('a.video-link').on('click', function (event) {
        event.preventDefault();
        var container = $(this).closest('.image-video-container');
        var videoLink = $(this).attr('data-href');
        var positionForEmbed = videoLink.indexOf('.be/') > -1 ? videoLink.indexOf('.be/') + 4 : videoLink.indexOf('.com/') + 4;
        var videoId = videoLink.indexOf('watch?') > -1 ? videoLink.slice(positionForEmbed + 9) : videoLink.slice(positionForEmbed);
        var videoSrc = 'https://youtube.com/embed/' + videoId + '?rel=0&autoplay=1&enablejsapi=1';
        var videoHeight = $(this).find('img').height();
        var maxWidth = 1600; //px
        var videoWidth = window.innerWidth > maxWidth ? maxWidth : window.innerWidth;

        var iframe = $('<iframe>', {
          src: videoSrc,
          height: videoHeight,
          width: videoWidth,
          class: 'attachment-full size-full',
          frameborder: 0,
          autoplay: 1,
          allowfullscreen: 1
        });
        container.html(iframe);
      })
    }
  });
  return new VideoInImageView();
});
