// Source: https://github.com/JasonSanford/GeoJSON-to-Google-Maps
var GeoJSON = function( geojson, options ){
  var _geometryToGoogleMaps = function( geojsonGeometry, opts, geojsonProperties ){
    
    var googleObj;

    switch ( geojsonGeometry.type ){
      case "Point":
        opts.position = new google.maps.LatLng(geojsonGeometry.coordinates[1], geojsonGeometry.coordinates[0]);
        var bounds = new google.maps.LatLngBounds();
        bounds.extend(opts.position);
        googleObj = new google.maps.Marker(opts);
        googleObj.set('bounds', bounds);
        if (geojsonProperties) {
          googleObj.set("geojsonProperties", geojsonProperties);
        }
        break;
        
      case "MultiPoint":
        googleObj = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < geojsonGeometry.coordinates.length; i++){
          opts.position = new google.maps.LatLng(geojsonGeometry.coordinates[i][1], geojsonGeometry.coordinates[i][0]);
          bounds.extend(opts.position);
          googleObj.push(new google.maps.Marker(opts));
        }
        if (geojsonProperties) {
          for (var k = 0; k < googleObj.length; k++){
            googleObj[k].set("geojsonProperties", geojsonProperties);
          }
        }
        for (var k = 0; k < googleObj.length; k++) {
          googleObj[k].set('bounds', bounds);
        }
        break;
        
      case "LineString":
        var path = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < geojsonGeometry.coordinates.length; i++){
          var coord = geojsonGeometry.coordinates[i];
          var ll = new google.maps.LatLng(coord[1], coord[0]);
          bounds.extend(ll);
          path.push(ll);
        }
        opts.path = path;
        googleObj = new google.maps.Polyline(opts);
        googleObj.set('bounds', bounds);
        if (geojsonProperties) {
          googleObj.set("geojsonProperties", geojsonProperties);
        }
        break;
        
      case "MultiLineString":
        googleObj = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < geojsonGeometry.coordinates.length; i++){
          var path = [];
          for (var j = 0; j < geojsonGeometry.coordinates[i].length; j++){
            var coord = geojsonGeometry.coordinates[i][j];
            var ll = new google.maps.LatLng(coord[1], coord[0]);
            bounds.extend(ll);
            path.push(ll);
          }
          opts.path = path;
          googleObj.push(new google.maps.Polyline(opts));
        }
        if (geojsonProperties) {
          for (var k = 0; k < googleObj.length; k++){
            googleObj[k].set("geojsonProperties", geojsonProperties);
          }
        }
        for (var k = 0; k < googleObj.length; k++) {
          googleObj[k].set('bounds', bounds);
        }
        break;
        
      case "Polygon":
        var paths = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < geojsonGeometry.coordinates.length; i++){
          var path = [];
          for (var j = 0; j < geojsonGeometry.coordinates[i].length; j++){
            var ll = new google.maps.LatLng(geojsonGeometry.coordinates[i][j][1], geojsonGeometry.coordinates[i][j][0]);
            bounds.extend(ll);
            path.push(ll)
          }
          paths.push(path);
        }
        opts.paths = paths;
        googleObj = new google.maps.Polygon(opts);
        googleObj.set('bounds', bounds);
        if (geojsonProperties) {
          googleObj.set("geojsonProperties", geojsonProperties);
        }
        break;
        
      case "MultiPolygon":
        googleObj = [];
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < geojsonGeometry.coordinates.length; i++){
          var paths = [];
          for (var j = 0; j < geojsonGeometry.coordinates[i].length; j++){
            var path = [];
            for (var k = 0; k < geojsonGeometry.coordinates[i][j].length; k++){
              var ll = new google.maps.LatLng(geojsonGeometry.coordinates[i][j][k][1], geojsonGeometry.coordinates[i][j][k][0]);
              bounds.extend(ll);
              path.push(ll);
            }
            paths.push(path);
          }
          opts.paths = paths;
          googleObj.push(new google.maps.Polygon(opts));
        }
        if (geojsonProperties) {
          for (var k = 0; k < googleObj.length; k++){
            googleObj[k].set("geojsonProperties", geojsonProperties);
          }
        }
        for (var k = 0; k < googleObj.length; k++) {
          googleObj[k].set('bounds', bounds);
        }
        break;
        
      case "GeometryCollection":
        googleObj = [];
        if (!geojsonGeometry.geometries){
          googleObj = _error("Invalid GeoJSON object: GeometryCollection object missing \"geometries\" member.");
        }else{
          for (var i = 0; i < geojsonGeometry.geometries.length; i++){
            googleObj.push(_geometryToGoogleMaps(geojsonGeometry.geometries[i], opts, geojsonProperties || null));
          }
        }
        break;
        
      default:
        googleObj = _error("Invalid GeoJSON object: Geometry object must be one of \"Point\", \"LineString\", \"Polygon\" or \"MultiPolygon\".");
    }
    
    return googleObj;
    
  };
  
  var _error = function( message ){
  
    return {
      type: "Error",
      message: message
    };
  
  };
    
  var obj;
  
  var opts = options || {};
  
  switch ( geojson.type ){
  
    case "FeatureCollection":
      if (!geojson.features){
        obj = _error("Invalid GeoJSON object: FeatureCollection object missing \"features\" member.");
      }else{
        obj = [];
        for (var i = 0; i < geojson.features.length; i++){
          obj.push(_geometryToGoogleMaps(geojson.features[i].geometry, opts, geojson.features[i].properties));
        }
      }
      break;
    
    case "GeometryCollection":
      if (!geojson.geometries){
        obj = _error("Invalid GeoJSON object: GeometryCollection object missing \"geometries\" member.");
      }else{
        obj = [];
        for (var i = 0; i < geojson.geometries.length; i++){
          obj.push(_geometryToGoogleMaps(geojson.geometries[i], opts, geojson.geometries[i].properties));
        }
      }
      break;
    
    case "Feature":
      if (!( geojson.properties && geojson.geometry )){
        obj = _error("Invalid GeoJSON object: Feature object missing \"properties\" or \"geometry\" member.");
      }else{
        obj = _geometryToGoogleMaps(geojson.geometry, opts, geojson.properties);
      }
      break;
    
    case "Point": case "MultiPoint": case "LineString": case "MultiLineString": case "Polygon": case "MultiPolygon":
      obj = geojson.coordinates
        ? obj = _geometryToGoogleMaps(geojson, opts, geojson.properties)
        : _error("Invalid GeoJSON object: Geometry object missing \"coordinates\" member.");
      break;
    
    default:
      obj = _error("Invalid GeoJSON object: GeoJSON object must be one of \"Point\", \"LineString\", \"Polygon\", \"MultiPolygon\", \"Feature\", \"FeatureCollection\" or \"GeometryCollection\".");
  
  }
  
  return obj;
};
