(function($) {
  Drupal.search_api_ajax = {};

  /**
   * We use the following jQuery BBQ hash states:
   *
   * #path: the (facetapi_pretty) path
   * #query: search ?query=<query>
   * #sort: sort field name
   * #order: sort order
   * #items_per_page: Views items_per_page
   * #page: Views paging
   * #f: the regular facetapi f query
   */

  // Content settings
  var blocks = Drupal.settings.search_api_ajax.blocks;
  var content = Drupal.settings.search_api_ajax.content;
  var regions = Drupal.settings.search_api_ajax.regions;
  var facetLocations = Drupal.settings.search_api_ajax.facet_locations ? Drupal.settings.search_api_ajax.facet_locations : 'body';

  // Path setting
  var ajaxPath = Drupal.settings.search_api_ajax_path;

  // Visual settings
  var spinner = Drupal.settings.search_api_ajax.spinner;
  var target = Drupal.settings.search_api_ajax.scrolltarget;
  var fade = Drupal.settings.search_api_ajax.fade;
  var opacity = Drupal.settings.search_api_ajax.opacity;
  var speed = Drupal.settings.search_api_ajax.scrollspeed;

  // Drupal overlay.module trigger helper
  var overlay = false;

  // Read URL and remove Drupal base with RegExp
  Drupal.search_api_ajax.readUrl = function(url) {
    return url.replace(new RegExp('^.*' + Drupal.settings.basePath + ajaxPath + '/' + '?'), '');
  };

  // Translate clicked URL to BBQ state
  Drupal.search_api_ajax.urlToState = function(url) {
    state = Drupal.search_api_ajax.getUrlState(url);

    // Path is a special case
    urlPath = url.split('?');
    path = Drupal.search_api_ajax.readUrl(urlPath[0]);
    if (path != undefined && path != '') {

      // jQuery BBQ adds extra double encoding: we need to undo that once
      state['path'] = decodeURIComponent(path);
    }

    // Use merge_mode: 2 to completely replace state (removing empty fragments)
    $.bbq.pushState(state, 2);
  };

  // Get URL state
  Drupal.search_api_ajax.getUrlState = function(url) {
    var state = {};
    hashes = url.slice(url.indexOf('?') + 1).split('&');
    for ( i = 0; i < hashes.length; i++) {
      hash = hashes[i].split('=');
      if (hash[1] != undefined && hash[1] != '') {
        state[hash[0]] = decodeURIComponent(hash[1]);
      }
    }
    return state;
  };

  // Post request to /search_api_ajax/path?query=
  Drupal.search_api_ajax.requestCallback = function(data) {

    // Avoid trigger on Drupal's #overlay
    if ($.bbq.getState('overlay')) {
      overlay = true;
      return;
    }

    // Avoid trigger after closing overlay
    if (overlay === true) {
      overlay = false;
      return;
    }

    // Visual effect: prepare for new data arrival
    if (content) {
      if (fade) {
        $(content + ':first').fadeTo('fast', opacity);
      }
      if (spinner) {
        $('#content').append('<div id="search-api-ajax-spinner"><img class="spinner" src="' + Drupal.settings.basePath + spinner + '" /></div>')
      }
    }

    // Scroll back to top, when top is out of view
    // Code taken from Views module
    if (target) {
      var offset = $(target).offset();
      var scrollTarget = target;
      while ($(scrollTarget).scrollTop() == 0 && $(scrollTarget).parent()) {
        scrollTarget = $(scrollTarget).parent()
      }
      if (offset.top - 10 < $(scrollTarget).scrollTop()) {
        $(scrollTarget).animate({
          scrollTop: (offset.top - 10)
        }, speed);
      }
    }

    path = '';
    if (data['path'] != undefined && data['path'] != '') {
      path = '/' + data['path'];
    }

    // Properly double re-encode all instances of forward/backward slashes, e.g.
    // #path=category/Audio%252FVideo (versus #path=category/Audio%2FVideo)
    // http://www.jampmark.com/web-scripting/5-solutions-to-url-encoded-slashes-problem-in-apache.html
    path = path.replace(/%2F/g, '%252F');
    path = path.replace(/%5C/g, '%255C');
    if (data['query']) {
      data['query'] = data['query'].replace(/%2F/g, '%252F')
      data['query'] = data['query'].replace(/%5C/g, '%255C');
    }

    // Get AJAX, callback for returned JSON data
    $.get(Drupal.settings.basePath + 'search_api_ajax/' + ajaxPath + path, {
      query: data['query'],
      sort: data['sort'],
      order: data['order'],
      items_per_page: data['items_per_page'],
      page: data['page'],
      f: data['f'],
    }, Drupal.search_api_ajax.responseCallback, 'json');
  };

  // Process received JSON data
  Drupal.search_api_ajax.responseCallback = function(data) {

    // Visual effect: accept data arrival
    if (content) {
      if (fade) {
        $(content + ':first').fadeTo('fast', 1);
      }
      if (spinner) {
        $('#search-api-ajax-spinner').remove();
      }
    }

    for (var setting in data.settings) {
      Drupal.settings[setting] = data.settings[setting];
    }

    var list = [];

    // Add new blocks that have come into existence
    // @see search_api_ajax.pages.inc where we add this blocks variable
    if (data.blocks) {
      for (var new_block in data.blocks) {
        blocks[new_block] = data.blocks[new_block];
      }
    }

    // Schedule items for removal to avoid page jumpiness
    if (blocks) {
      for (var block in blocks) {
        list.push($(blocks[block]));
      }
    }

    // Output/append new data to frontend
    for (var region in data.regions) {
      if (region == 'search_api_ajax') {
        if (content) {
          $(content + ':first').html(data.regions[region]);
        }
      }
      else {
        for (var block in data.regions[region]) {
          if (regions[region] && blocks[block]) {
            $(regions[region]).append(data.regions[region][block]);
          }
        }
      }
    }

    // Remove blocks that were scheduled for removal
    for (var i = 0, l = list.length; i < l; i++) {
      list[i].remove();
    }

    // Re-fire Drupal attachment behaviors
    Drupal.attachBehaviors('body');
  };

  // Helper function to navigate on user actions
  Drupal.search_api_ajax.navigateUrl = function(url) {
    if (url !== undefined) {
      Drupal.search_api_ajax.urlToState(url)
    }
    return false;
  };

  // Helper function to navigate on new query
  Drupal.search_api_ajax.navigateQuery = function(query) {
    if (query !== undefined) {
      var state = {};
      state['query'] = query;

      // merge_mode 2 clears everything else
      $.bbq.pushState(state, 2);
    }
    return false;
  };

  // Helper function to navigate on new range
  // Create Pretty Facet Path like: <field>/<from>/<to>
  Drupal.search_api_ajax.navigateRanges = function(path, field, from, to) {
    var state = {};

    // Get current state, check if state exists
    var exists = false;
    if ($.bbq.getState('path')) {
      path = $.bbq.getState('path');
    }
    if (path != undefined && path != '') {
      var splitStates = path.split('/');
      $.each(splitStates, function(index, value) {
        if (!(index % 2) && value == field) {
          exists = splitStates[index + 1];
        }
      });
    }

    // Decision: replace existing range or add new
    newRange = '[' + from + ' TO ' + to + ']';
    if (exists) {
      state['path'] = path.replace(exists, newRange);
    }
    else if (path != undefined && path != '') {
      state['path'] = path + '/' + field + '/' + newRange;
    }
    else {
      state['path'] = field + '/' + newRange;
    }

    $.bbq.pushState(state);
    return false;
  };

  // Observe and react to user behavior
  // @see http://api.jquery.com/category/selectors/attribute-selectors/
  Drupal.search_api_ajax.ajax = function(selector) {

    // Observe facet and sorts links ^ starts with * contains
    // Check two paths: ^basePath/ajaxPath OR ^search_api_ajax/basePath/ajaxPath
    $(selector + ' a[href^="' + Drupal.settings.basePath + ajaxPath + '"], ' + selector + ' a[href^="' + Drupal.settings.basePath + 'search_api_ajax/' + ajaxPath + '"]').live('click', function() {
      return Drupal.search_api_ajax.navigateUrl($(this).attr('href'));
    });

    // Observe search query forms (or views input forms, must be custom set)
    $(selector + ' form[action*="' + ajaxPath + '"], ' + selector + ' form[action*="search_api_ajax/' + ajaxPath + '"]').live('submit', function() {
      return Drupal.search_api_ajax.navigateQuery($(this).find('input[name*="query"]').val());
    });

    // Observe facet range sliders
    $(selector + ' .search-api-ranges-widget form[action^="' + Drupal.settings.basePath + ajaxPath + '"], ' + selector + ' .search-api-ranges-widget form[action^="' + Drupal.settings.basePath + 'search_api_ajax/' + ajaxPath + '"]').live('submit', function() {
      rangeTarget = Drupal.search_api_ajax.readUrl('/' + $(this).find('input[name="path"]').val());
      rangeField = $(this).find('input[name="range-field"]').val();
      rangeFrom = $(this).find('input[name="range-from"]').val();
      rangeTo = $(this).find('input[name="range-to"]').val();
      return Drupal.search_api_ajax.navigateRanges(rangeTarget, rangeField, rangeFrom, rangeTo);
    });
  };

  // Initialize live() listeners on first page load
  if ( typeof (searchApiAjaxInit) == 'undefined') {
    Drupal.search_api_ajax.ajax(facetLocations);
    searchApiAjaxInit = true;
  }

  // If hash directly entered on page load (e.g. external link)
  data = $.bbq.getState();
  if (data != undefined && !$.isEmptyObject(data)) {
    Drupal.search_api_ajax.requestCallback(data);
  }

  // If hash changed through click
  $(window).bind('hashchange', function(e) {
    data = e.getState();
    Drupal.search_api_ajax.requestCallback(data);
  });
})(jQuery);
