(function ($) {

    Drupal.leaflet_widget = Drupal.leaflet_widget || {};

    Drupal.behaviors.geofield_widget = {
        attach: attach
    };

    function attach(context, settings) {
        $('.leaflet-widget').once().each(function(i, item) {
            var id = $(item).attr('id'),
            options = settings.leaflet_widget_widget[id];
            if (options.toggle) {
              $('#' + id + '-input').before('<div class="map" style="cursor: pointer;" id="' + id + '-toggle">Add data manually</div>');
              $('#' + id + '-toggle').click(function () {
                $(item).toggle();
                if ($(this).hasClass('map')) {
                  $(this).text('Use map');
                  $(this).removeClass('map');
                  $('#' + id + '-input').get(0).type = 'text';
                }
                else {
                  $(this).text('Add data manually');
                  $('#' + id + '-input').get(0).type = 'hidden';
                  $(this).addClass('map');
                }
              });

            }
            var map = L.map(id, options.map);

            L.tileLayer(options.map.base_url).addTo(map);

            var current = $('#' + id + '-input').val();
            current = JSON.parse(current);
            var layers = Array();
            if (current.features.length) {
              var geojson = L.geoJson(current)
              for (var key in geojson._layers) {
                layers.push(geojson._layers[key]);
               }
            }

            var Items = new L.FeatureGroup(layers).addTo(map);
            // Autocenter if that's cool.
            if (options.map.auto_center) {
              if (current.features.length) {
                map.fitBounds(Items.getBounds());
              }
            }

            var drawControl = new L.Control.Draw({
                autocenter: true,
                draw: {
                  position: 'topleft',
                  polygon: options.draw.tools.polygon,
                  circle: options.draw.tools.circle,
                  marker: options.draw.tools.marker,
                  rectangle: options.draw.tools.rectangle,
                  polyline: options.draw.tools.polyline
                },
                edit: {
                  featureGroup: Items
                }

              });

              map.addControl(drawControl);

              map.on('draw:created', function (e) {
                var type = e.layerTypee,
                  layer = e.layer;
                // Remove already created layers. We only want to save one
                // per field.
                leafletWidgetLayerRemove(map._layers, Items);
                // Add new layer.
                Items.addLayer(layer);
              });

              $(item).parents('form').submit(function(event){
                if ($('#' + id + '-toggle').hasClass('map')) {
                  leafletWidgetFormWrite(map._layers, id)
                }
              });

            Drupal.leaflet_widget[id] = map;
        });
    }

    /**
     * Writes layer to input field if there is a layer to write.
     */
    function leafletWidgetFormWrite(layers, id) {
      var write  = Array();
      for (var key in layers) {
        if (layers[key]._latlngs || layers[key]._latlng) {
          write.push(layerToGeometry(layers[key]));
        }
      }
      // Only save if there is a value.
      if (write.length) {
        $('#' + id + '-input').val(write);
      }
    }

    /**
     * Removes layers that are already on the map.
     */
    function leafletWidgetLayerRemove(layers, Items) {
      for (var key in layers) {
        if (layers[key]._latlngs || layers[key]._latlng) {
          Items.removeLayer(layers[key]);
        }
      }
    }

    // This will all go away once this gets into leaflet main branch:
    // https://github.com/jfirebaugh/Leaflet/commit/4bc36d4c1926d7c68c966264f3cbf179089bd998
    var layerToGeometry = function(layer) {
      var json, type, latlng, latlngs = [], i;

      if (L.Marker && (layer instanceof L.Marker)) {
        type = 'Point';
        latlng = LatLngToCoords(layer._latlng);
        return JSON.stringify({"type": type, "coordinates": latlng});

      } else if (L.Polygon && (layer instanceof L.Polygon)) {
        type = 'Polygon';
        latlngs = LatLngsToCoords(layer._latlngs, 1);
        return JSON.stringify({"type": type, "coordinates": [latlngs]});

      } else if (L.Polyline && (layer instanceof L.Polyline)) {
        type = 'LineString';
        latlngs = LatLngsToCoords(layer._latlngs);
        return JSON.stringify({"type": type, "coordinates": latlngs});

      }
    }

    var LatLngToCoords = function (LatLng, reverse) { // (LatLng, Boolean) -> Array
      var lat = parseFloat(reverse ? LatLng.lng : LatLng.lat),
        lng = parseFloat(reverse ? LatLng.lat : LatLng.lng);

      return [lng,lat];
    }

    var LatLngsToCoords = function (LatLngs, levelsDeep, reverse) { // (LatLngs, Number, Boolean) -> Array
      var coord,
        coords = [],
        i, len;

      for (i = 0, len = LatLngs.length; i < len; i++) {
          coord = levelsDeep ?
                  LatLngToCoords(LatLngs[i], levelsDeep - 1, reverse) :
                  LatLngToCoords(LatLngs[i], reverse);
          coords.push(coord);
      }

      return coords;
    }

}(jQuery));
