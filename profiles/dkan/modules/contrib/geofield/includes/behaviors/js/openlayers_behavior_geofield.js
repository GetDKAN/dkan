/**
 * @file
 * JS Implementation of OpenLayers behavior.
 */
/**
 * Class: OpenLayers.Control.GeofieldEditingToolbar
 * The GeofieldEditingToolbar is a panel controls to modify or draw polygons, lines,
 * points, or to navigate the map by panning. You can select which tool to enable
 * with options.tools.
 *
 * Inherits from:
 *  - <OpenLayers.Control.Panel>
 */
OpenLayers.Control.GeofieldEditingToolbar = OpenLayers.Class(
  OpenLayers.Control.Panel, {

    /**
     * Constructor: OpenLayers.Control.GeofieldEditingToolbar
     * Create an editing toolbar for a given layer.
     *
     * Parameters:
     * layer - {<OpenLayers.Layer.Vector>}
     * options - {Object}
     */
    initialize: function(layer, options) {
        OpenLayers.Control.Panel.prototype.initialize.apply(this, [options]);

        var controls = [];
        var tools = options.tools;
        var tool = null;

        if (options.allow_edit && options.allow_edit !== 0) {
          // add an Edit feature
          controls.push(new OpenLayers.Control.ModifyFeature(layer, {
            deleteCodes: [46, 68, 100],
            handleKeypress: function(evt) {
              if (this.feature && OpenLayers.Util.indexOf(this.deleteCodes, evt.keyCode) > -1) {
                // We must unselect the feature before we delete it
                var feature_to_delete = this.feature;
                this.selectControl.unselectAll();
                this.layer.removeFeatures([feature_to_delete]);
              }
            }
          }));
        } else {
          controls = [new OpenLayers.Control.Navigation()];
        }

        if (tools && tools.length) {
          for (var i = 0, il = tools.length; i < il; i += 1) {
            // capitalize first letter
            tool = tools[i][0].toUpperCase() + tools[i].slice(1);
            controls.push(
              new OpenLayers.Control.DrawFeature(layer, OpenLayers.Handler[tool], {'displayClass': 'olControlDrawFeature' + tool})
            );
          }
        }

        this.addControls(controls);
    },

    /**
     * Method: draw
     * calls the default draw, and then activates mouse defaults.
     *
     * Returns:
     * {DOMElement}
     */
    draw: function() {
        OpenLayers.Control.Panel.prototype.draw.apply(this, arguments);
        this.div.className += " olControlEditingToolbar";
        if (this.defaultControl === null) {
            this.defaultControl = this.controls[0];
        }
        return this.div;
    },

    CLASS_NAME: "OpenLayers.Control.GeofieldEditingToolbar"
});


(function($) {
  /**
   * Geofield Behavior
   */
  Drupal.behaviors.openlayers_behavior_geofield = {
    'attach': function(context, settings) {
      var data = $(context).data('openlayers'),
          behavior = data && data.map.behaviors['openlayers_behavior_geofield'],
          dataProjection = new OpenLayers.Projection('EPSG:4326'),
          features, wktFormat;

      // helper to create a WKT format object with the right projections
      function initWktFormat (inp, outp) {
        var WktWriter = new OpenLayers.Format.WKT();
        WktWriter.internalProjection = inp;
        WktWriter.externalProjection = outp || dataProjection;
        return WktWriter;
      }

      // populate our wkt input field
      function updateWKTField (features) {
        var WktWriter = initWktFormat(features.object.map.projection);
        // limits are to be checked server-side, not here.
        // for a single shape avoid GEOMETRYCOLLECTION
        var toSerialize = features.object.features;
        // don't serialize empty feature
        if (toSerialize.length) {
          if (toSerialize.length === 1) { toSerialize = toSerialize[0]; }
          this.val(WktWriter.write(toSerialize));
        }
        // but clear the value
        else {
          this.val('');
        }
      }

      // keep only one features for each map input
      function limitFeatures (features) {
        // copy a list of features
        var copyFeatures = features.object.features.slice();
        // only keep the last one
        var lastFeature = copyFeatures.pop();
        // we remove a lot of features, don't trigger events
        features.object.destroyFeatures(copyFeatures, {silient: true});
      }

      if (behavior && !$(context).hasClass('geofield-processed')) {
        // we get the .form-item wrapper which is a slibling of our hidden input
        var $wkt = $(context).closest('.form-item').parent().find('input.geofield_wkt');
        // if there is no form input this shouldn't be activated
        if ($wkt.length) {
          var dataLayer = new OpenLayers.Layer.Vector(Drupal.t('Feature Layer'), {
                projection: dataProjection,
                drupalID: 'openlayers_behavior_geofield'
              });

          dataLayer.styleMap = Drupal.openlayers.getStyleMap(data.map, 'openlayers_behavior_geofield');
          data.openlayers.addLayer(dataLayer);

          // only one feature on each map register before adding our data
          if (Drupal.settings.geofield.data_storage == 'single') {
            dataLayer.events.register('featureadded', $wkt, limitFeatures);
          }

          if ($wkt.val() != '') {
            wktFormat = initWktFormat(data.openlayers.projection);
            features = wktFormat.read($wkt.val());
            dataLayer.addFeatures(features);
          }

          // registering events late, because adding data
          // would result in a reprojection loop
          dataLayer.events.register('featureadded', $wkt, updateWKTField);
          dataLayer.events.register('featureremoved', $wkt, updateWKTField);
          dataLayer.events.register('afterfeaturemodified', $wkt, updateWKTField);

          // transform options object to array
          behavior.tools = [];
          // add a new 'tools' key which is an array of enabled features
          $.each(behavior.feature_types, function (key, value) {
            if (value) {
              behavior.tools.push(key);
            }
          });
          // create toolbar
          var geofieldControl = new OpenLayers.Control.GeofieldEditingToolbar(dataLayer, behavior);
          data.openlayers.addControl(geofieldControl);

          // on submit recalculate everything to be up to date
          var formData = {
            'control': geofieldControl,
            'dataLayer': dataLayer
          };
          function handleSubmit (e) {
            $.map(e.data.control.controls, function(c) { c.deactivate(); });
            dataLayer.events.triggerEvent('featuremodified');
          }
          $(context).parents('form').bind('submit', formData, handleSubmit);
        }
        $(context).addClass('geofield-processed');
      } // if
    }
  };
})(jQuery);
