define([
  'jquery',
  'backbone',
  'eventModalPHP',
  'modal'
], function ($, Backbone, eventModalPHP, modal) {

  "use strict";
  var that;

  var BlogEventsView = Backbone.View.extend({

    initialize: function () {
    },

    initModals: function () {
      $('[data-modal-name = "event-php"]')
        .off('onParse')
        .off('click')
        .on('click', function (e) {
          e.preventDefault();
        })
        .on('onParse', eventModalPHP.bind($(this)));
        modal();
    },

    rsvp: function (link) {
      $.get(link.data('url'), function (data) {
       if (data.status === 200) {
         link.addClass('rsvp-success');
       } else if (data.redirect) {
         link.find('.validation-advice').html(data.message);
         location.href = data.redirect;
       }

        that.bindEvents();
      });
    },

    reset: function (url) {
      $.get(url, function (data) {
        $('#events').parent().html(data);

        //Select styler
        if (jQuery('select').styler) {
          jQuery('select').styler();
        }
        that.bindEvents();
      });
    },

    findEvents: function (params) {
      $.get("/blog/event", params, function (data) {
        $('#events').parent().html(data);

        //Select styler
        if (jQuery('select').styler) {
          jQuery('select').styler();
        }
        that.bindEvents();
        window.innerWidth < 768 ? $('#events').addClass('mobile-view') :  $('#events').removeClass('mobile-view');
      });
    },

    getView: function (param, isWeekView, isListView) {
      var that = this;
      $.get("/blog/event/?" + param, function (data) {
        })
        .done(function (data) {
          $('#events').parent().html(data);
          $('[data-coherent-modal]').each(function(i, linkEl) {
            $(linkEl).removeAttr('data-coherent-modal');
          });
          if (isListView) {
            $('.events-table-list').find('[data-modal-name = "event-php"]').attr('data-coherent-modal', '');
            $('#events').addClass('list-view');
            $('.list-view').addClass('active');
            $('.calendar-view').removeClass('active');
          } else {
            $('.list-view').removeClass('active');
            $('.calendar-view').addClass('active');
          }
          if (isWeekView) {
            $('.events-table-calendar').find('[data-modal-name = "event-php"]').attr('data-coherent-modal', '');
            $('#events').removeClass('month-view');
            that.setGrid();
          }
          else {
            $('#events').addClass('month-view');
          }
            //is event exist
            $('.day-content').each(function (i, element) {
              if ($(element).find('.event-description').children().length !== 0) {
                $(element).find('.event-day').addClass('event-exist');
              }
            });
            $('.day-name').each(function (i, element) {
              if ($(element).find('.event-description').children().length !== 0) {
                $(this).addClass('event-exist');
              }
            });

          window.innerWidth < 768 ? $('#events').addClass('mobile-view') :  $('#events').removeClass('mobile-view');
          that.bindEvents();
          //if (!$('#events').addClass('list-view'))
          that.initModals();
        })
        .fail(function () {
        });
    },

    bindEvents: function () {
      $('.link-nav-next').on('click', function (e) {
        e.preventDefault();
        if ($('#events').hasClass('list-view')) {
          $('#events').hasClass('month-view') ? that.getView("pg=1&view=month", false, true) : that.getView("pg=1", true, true);
        } else {
          $('#events').hasClass('month-view') ? that.getView("pg=1&view=month", false, false) : that.getView("pg=1", true, false);
        }
      });
      $('.link-nav-prev').on('click', function (e) {
        e.preventDefault();
        if ($('#events').hasClass('list-view')) {
          $('#events').hasClass('month-view') ? that.getView("pg=-1&view=month", false, true) : that.getView("pg=-1", true, true);
        } else {
          $('#events').hasClass('month-view') ? that.getView("pg=-1&view=month", false, false) : that.getView("pg=-1", true, false);
        }
      });
      $('.link-month-view').on('click', function (e) {
        e.preventDefault();
        $('#events').hasClass('list-view') ? that.getView("view=month", false, true) : that.getView("view=month", false, false);
      });
      $('.link-week-view').on('click', function (e) {
        e.preventDefault();
        $('#events').hasClass('list-view') ? that.getView("view=week", true, true) : that.getView("view=week", true, false);
      });

      //Btn -find-
      $('.btn-find-events').on('click', function (e) {
        e.preventDefault();
        //lat=40.7410705&lng=-73.9997129&country=&state=&zipcode
        var country = $('select[name="country"] option:selected').val();
        var state = $('select[name="state"] option:selected').val();
        var zipcode = $('input[name="zipcode"]').val();

          var params = {
            country: country,
            state: state,
            zipcode: zipcode
          };
          that.findEvents(params);

      });

      //Btn -reset-
      $('.btn-reset').on('click', function (e) {
        e.preventDefault();
        that.reset($(this).data('url'));
      });

      //List view - Calendar view
      $('a.list-view').on('click', function (e) {
        $('[data-coherent-modal]').each(function(i, linkEl) {
          $(linkEl).removeAttr('data-coherent-modal');
          $(linkEl).removeAttr('data-modal-binded');
        });
        e.preventDefault();
        $('.events-table-list').find('[data-modal-name = "event-php"]').attr('data-coherent-modal', '');
        $('.show-events-container').empty();
        $("#events").addClass('list-view');
        $('.calendar-view').removeClass('active');
        $(this).addClass('active');
        that.initModals();
        $('.event-description').removeAttr('style');
      });
      $('a.calendar-view').on('click', function (e) {
        e.preventDefault();
        $("#events").removeClass('list-view');
        $('.list-view').removeClass('active');
        $(this).addClass('active');
        that.setGrid();
      });

      //Show events on month view (under table)
      var allExistEvents = $('.events-table-calendar').find('.event-exist');
      allExistEvents.on('click', function () {
        $('[data-coherent-modal]').each(function(i, linkEl) {
            $(linkEl).removeAttr('data-coherent-modal');
        });
        $('.show-events-container').empty();
        var eventExist = $(this);
        eventExist.css({
          'background': '#000',
          'color': '#fff'
        });

        var eventDescriptionClone = eventExist.next('.event-description').clone();
        eventDescriptionClone.data('coherent-modal');
        eventDescriptionClone.find('[data-modal-name = "event-php"]').attr('data-coherent-modal', '');
        eventDescriptionClone.appendTo('.show-events-container');
        allExistEvents.not($(this)).css({'background': '#e6e6e6', 'color': '#000'});
        that.initModals();

        $('[data-modal-name = "event-php"]')
          .on('mcallback', function(e) {
            //Btn -rsvp-
            $('.rsvp').off('click.rsvp');

            $('.rsvp').on('click.rsvp', function (e) {
              e.preventDefault();
              that.rsvp($(this));
            });
          });

      });

      //Select styler
      if (jQuery('select').styler) {
        jQuery('select').styler();
      }
    },

    setGrid: function () {
      //Set height table cell (for week view)
      var maxChildCount = Math.max.apply(Math, $(".event-description").map(function () {
        return $(this).children().length;
      }));
      for (var i = 0; i < maxChildCount; i++) {
        var maxChildHeight = Math.max.apply(Math, $(".event-description").map(function () {
          return $($(this).children()[i]).height();
        }));
        $($('.event-description').children()[i]).height(maxChildHeight);
      }
      var max = Math.max.apply(Math, $(".event-description").map(function () {
        return $(this).height();
      }));
      $('.event-description').height(max);
    },

    init: function () {
      that = this;
      this.bindEvents();
      that.initModals();
    }
  });
  return new BlogEventsView();
});
