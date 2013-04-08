
Drupal.behaviors.data_node = function(context) {
  // Link AHAH
  $('a.data-node-remove:not(.processed), a.data-node-add:not(.processed), .data-node-label a.remove-link:not(.processed)').each(function() {
    $(this).addClass('processed');
    $(this).click(function() {
      var url = $(this).attr('href');
      $.getJSON(url, {'ajax': 1}, function(data) {
        if (data['status']) {
          // Generate selectors
          var labels = 'data_node_labels-' + data['table'] + '-' + data['id'];
          var link = 'data_node_link-' + data['table'] + '-' + data['id'] + '-' + data['nid'];

          // Labels are straightforward to replace.
          $('.' + labels).replaceWith(data['labels']);

          // Target links first, so we don't write over our own work in immediate succession.
          $('.' + link).addClass('targeted');
          $('.' + link + '.targeted.data-node-remove').replaceWith(data['remove_link']);
          $('.' + link + '.targeted.data-node-add').replaceWith(data['add_link']);

          // Reattach behaviors
          Drupal.attachBehaviors('a.data-node-remove:not(.processed), a.data-node-add:not(.processed), .data-node-label a.remove-link:not(.processed)');
        }
      });
      return false;
    });
  });

  // Active node
  $('form.data-node-active-form:not(.processed)').each(function() {
    $(this).addClass('processed');
    $('select', this).change(function() {
      var form = $(this).parents('form');
      var value = $(this).val();
      if (value == 'new') {
        $('.new-node', form).show();
      }
      else {
        $('.new-node', form).hide();
        if (value != 0) {
          var stale_nid = 0;
          var stale = [];
          var ajax_url = $('.data-node-ajax-url', form).val() + '/' + value;
          var add_url = $('.data-node-add-url', form).val() + '/';
          var remove_url = $('.data-node-remove-url', form).val() + '/';

          $('a.data-node-add, a.data-node-remove').each(function() {
            // Manipulate the URL to retrieve stale IDs and the current stale nid
            var url = $(this).attr('href').replace(add_url, '').replace(remove_url, '').split('/');
            stale.push(url[0]);
            if (stale_nid == 0) {
              stale_nid = url[1];
            }
          });

          $('span.data-node-placeholder').each(function() {
            var classes = $(this).attr('class').split(' ');
            for (var key in classes) {
              if (classes[key].indexOf('data_node_link') === 0) {
                // 0: table, 1: id, 2: nid
                var split = classes[key].replace('data_node_link-', '').split('-');
                stale.push(split[1]);
                break;
              }
            }
          });

          $.getJSON(ajax_url, {'ajax': 1, 'stale': stale.join('-')}, function(data) {
            if (data['status']) {
              for (var id in data['refresh']) {
                var link = 'data_node_link-' + data['table'] + '-' + id + '-' + stale_nid;
                $('.' + link).replaceWith(data['refresh'][id]);
              }
              // Reattach behaviors
              Drupal.attachBehaviors('a.data-node-remove:not(.processed), a.data-node-add:not(.processed)');
            }
          });
        }
      }
    });
    $('input#edit-submit', this).hide();
  });
}
