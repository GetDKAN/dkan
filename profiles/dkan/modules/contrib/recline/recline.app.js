/**
 * @file
 * Provides options for recline visualization.
 */

(function ($) {
  Drupal.behaviors.Recline = {
    attach: function (context) {
      file = Drupal.settings.recline.file;
      grid = Drupal.settings.recline.grid;
      graph = Drupal.settings.recline.graph;
      map = Drupal.settings.recline.map;
      timeline = Drupal.settings.recline.timeline;
      transform = Drupal.settings.recline.transform;

      window.dataExplorer = null;
      window.explorerDiv = $('.data-explorer-here');

      // This is some fancy stuff to allow configuring the multiview from
      // parameters in the query string
      //
      // For more on state see the view documentation.
      var state = recline.View.parseQueryString(decodeURIComponent(window.location.search));
      if (state) {
        _.each(state, function(value, key) {
          try {
            value = JSON.parse(value);
          } catch(e) {}
          state[key] = value;
        });
      } else {
        state.url = 'demo';
      }
      var dataset = null;
      if (state.dataset || state.url) {
        var datasetInfo = _.extend({
            url: state.url,
            backend: state.backend
          },
          state.dataset
        );
        dataset = new recline.Model.Dataset(datasetInfo);
      } else {
        var dataset = new recline.Model.Dataset({
           url: file,
           backend: 'csv',
        });
      }
      createExplorer(dataset, state);
    }
  }

  // make Explorer creation / initialization in a function so we can call it
  // again and again
  var createExplorer = function(dataset, state) {
    // remove existing data explorer view
    var reload = false;
    if (window.dataExplorer) {
      window.dataExplorer.remove();
      reload = true;
    }
    window.dataExplorer = null;
    var $el = $('<div />');
    $el.appendTo(window.explorerDiv);

    var views = [];
    if (grid) {
      views.push(
        {
          id: 'grid',
          label: 'Grid',
          view: new recline.View.SlickGrid({
            model: dataset
          }),
        }
      );
    }
    if (graph) {
      views.push(
      {
        id: 'graph',
        label: 'Graph',
        view: new recline.View.Graph({
          model: dataset
        }),
      }
      );
    }
    if (map) {
      views.push(
      {
        id: 'map',
        label: 'Map',
        view: new recline.View.Map({
          model: dataset
        }),
      }
      );
    }
    if (transform) {
      views.push(
      {
        id: 'transform',
        label: 'Transform',
        view: new recline.View.Transform({
          model: dataset
        })
      }
      );
    }

    window.dataExplorer = new recline.View.MultiView({
      model: dataset,
      el: $el,
      state: state,
      views: views
    });
  }

})(jQuery);
