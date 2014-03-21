/**
 * @file
 * Provides options for recline visualization.
 */

(function ($) {
    Drupal.behaviors.Recline = {
        attach: function (context) {
            delimiter = Drupal.settings.recline.delimiter;
            file = Drupal.settings.recline.file;
            grid = Drupal.settings.recline.grid;
            graph = Drupal.settings.recline.graph;
            map = Drupal.settings.recline.map;
            uuid = Drupal.settings.recline.uuid;
            dkan = Drupal.settings.recline.dkan;
            fileType = Drupal.settings.recline.fileType;

            window.dataExplorer = null;
            window.explorerDiv = $('.data-explorer');

            // This is the very basic state collection.
            var state = recline.View.parseQueryString(decodeURIComponent(window.location.hash));
            if ('#map' in state) {
                state['currentView'] = 'map';
            } else if ('#graph' in state) {
                state['currentView'] = 'graph';
            } else if ('#timeline' in state) {
                state['currentView'] = 'timeline';
            }
            // Checks if dkan_datastore is installed.
            if (dkan) {
                var DKAN_API = '/api/action/datastore/search.json';
                var url = window.location.origin + DKAN_API + '?resource_id=' + uuid;
                var DkanDatastore = false;
                var DkanApi = $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(data, status) {
                        if ('success' in data && data.success) {
                            var dataset = new recline.Model.Dataset({
                                endpoint: window.location.origin + '/api',
                                url: url,
                                id: uuid,
                                backend: 'ckan'
                            });
                            dataset.fetch();
                            return createExplorer(dataset, state);
                        }
                        else {
                            $('.data-explorer').append('<div class="messages status">Error returned from datastore: ' + data + '.</div>');
                        }

                    },
                    error: function(data, status) {
                        $('.data-explorer').append('<div class="messages status">Unable to connect to the datastore.</div>');
                    }
                });
            }
            else if (fileType == 'text/csv') {
                var options = {delimiter: delimiter};
                $.ajax({
                    url: file,
                    dataType: "text",
                    timeout: 1000,
                    success: function(data) {
                        // Converts line endings in either format to unix format.
                        data = data.replace(/(\r\n|\n|\r)/gm,"\n");
                        var dataset = new recline.Model.Dataset({
                            records: recline.Backend.CSV.parseCSV(data, options)
                        });
                        dataset.fetch();
                        var views = createExplorer(dataset, state);
                        // The map needs to get redrawn when we are delivering from the ajax
                        // call.
                        $.each(views, function(i, view) {
                            if (view.id == 'map') {
                                view.view.redraw('refresh');
                            }
                        });
                    },
                    error: function(x, t, m) {
                        if (t === "timeout") {
                            $('.data-explorer').append('<div class="messages status">File was too large or unavailable for preview.</div>');
                        } else {
                            $('.data-explorer').append('<div class="messages status">Data preview unavailable.</div>');
                        }
                    }
                });
            }
            // Checks if xls.
            else if (fileType == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || fileType == 'application/vnd.ms-excel') {
                var dataset = new recline.Model.Dataset({
                    url: file,
                    backend: 'dataproxy'
                });
                dataset.fetch();
                var views = createExplorer(dataset, state);
            }
            else {
                $('.data-explorer').append('<div class="messages status">File type ' + fileType + ' not supported for preview.</div>');
            }
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
                    })
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
                    })
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
                    })
                }
            );
        }

        Drupal.settings.recline.args = {
            model: dataset,
            el: $el,
            state: state,
            views: views
        }

        window.dataExplorer = new recline.View.MultiView(Drupal.settings.recline.args);
        $.event.trigger('createDataExplorer');
        return views;
    }
    $(".recline-embed a.embed-link").live('click', function(){
      $(this).parents('.recline-embed').find('.embed-code-wrapper').toggle();
      return false;
    });
})(jQuery);
