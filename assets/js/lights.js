var picker_timer = null;
var scene_picker_timer = null;
var slider_timer = null;
var scene_slider_timer = null;

function update_home_view() {
  $.ajax('/lights', {
    type: 'get',
    data: {},
    success: function(data) {
      for (var index in data) {
        var light = data[index];
        update_light_display(light);
      }

      update_all_light_display(data);
    }
  });
}

function update_all_light_display(lights) {
  // Figure out power setting.
  function on(l) { return l.power == true };
  function off(l) { return l.power == false };

  if (_.all(lights, on)) {
    $('button[data-light-id=0]').removeClass('btn-default').addClass('btn-success');
  } else if (_.all(lights, off)) {
    $('button[data-light-id=0]').addClass('btn-default').removeClass('btn-success');
  } else {
    $('button[data-light-id=0]').removeClass('btn-default').removeClass('btn-success');
  }

  // Figure out color setting.
  function same_color(l) { return l.hex == lights[0].hex }

  if (_.all(lights, same_color)) {
    $('#light_swatch_0').css({ backgroundColor: lights[0].hex }).html('&nbsp;');
  } else {
    $('#light_swatch_0').css({ backgroundColor: '#efefef' }).html('?');
  }
}

function update_light_display(state) {
  $('#light_swatch_' + state.id).css({ backgroundColor: state.hex });
  if (state.power) {
    $('button[data-light-id=' + state.id + ']').addClass('btn-success');
  } else {
    $('button[data-light-id=' + state.id + ']').removeClass('btn-success');
  }
}

function rgbToHsl(r, g, b){
  r /= 255, g /= 255, b /= 255;
  var max = Math.max(r, g, b), min = Math.min(r, g, b);
  var h, s, l = (max + min) / 2;

  if(max == min) {
    h = s = 0; // achromatic
  } else {
    var d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    switch(max) {
      case r: h = (g - b) / d + (g < b ? 6 : 0); break;
      case g: h = (b - r) / d + 2; break;
      case b: h = (r - g) / d + 4; break;
    }
    h /= 6;
  }

  return {
    h: Math.floor(h * 65535),
    s: Math.floor(s * 255),
    l: Math.floor(l * 255)
  };
}

function select_page(page) {
  // Trim off the hash character.
  var page_name = page.substring(1, page.length);

  // Un-highlight all buttons.
  $('ul.nav a').each(function() {
    var href = $(this).attr('href');

    // Be selective.
    if(href != page) {
      var page_name = href.substring(1, href.length);
      $(this).closest('li').removeClass('active');
      $('#page-' + page_name).hide();
    }
  });

  // Highlight the current button.
  $('.js-page-' + page_name).closest('li').addClass('active');
  $('#page-' + page_name).show();

  // Set the page hash.
  if(location.hash != page) {
    location.hash = page;
  }
}

$(document).ready(function() {
  // Configure navigation
  if(   location.hash.length
     && jQuery.inArray(location.hash, ['#home', '#scenes']) > -1
  ) {
    select_page(location.hash);
  } else {
    location.hash = '#home';
    select_page('#home');
  }

  $('ul.nav a').on('click', function() {
    select_page($(this).attr('href'));
  });

  $('.js-toggle-response').on('click', function() {
    $('#response').slideToggle();
  });

  $('.js-toggle-controls').on('click', function() {
    button = $(this);
    light_id = button.data('light-id');
    if ($('#controls_' + light_id).is(':visible')) {
      button.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
      $('#controls_' + light_id).slideUp();
    } else {
      button.removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
      $('#controls_' + light_id).slideDown();
    }
  });

  $('.js-toggle-scene-controls').on('click', function() {
    button = $(this);
    scene_id = button.data('scene-id');
    if ($('#scene_controls_' + scene_id).is(':visible')) {
      button.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
      $('#scene_controls_' + scene_id).slideUp();
    } else {
      button.removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
      $('#scene_controls_' + scene_id).slideDown();
    }
  });

  $('.js-toggle-colormode').on('click', function() {
    button = $(this);
    // Find the other button by looking around.
    other_button = button.siblings('button');

    if (!button.data('active')) {
      // Toggle to the other button.
      button.data('active', true);
      button.removeClass('btn-default').addClass('btn-primary');
      other_button.data('active', false);
      other_button.removeClass('btn-primary').addClass('btn-default');

      row = button.closest('.row');
      if(button.data('mode') == 'ct') {
        row.find('.light-controls-ct').show();
        row.find('.light-controls-hs').hide();
      } else {
        row.find('.light-controls-ct').hide();
        row.find('.light-controls-hs').show();
      }
    }
  });

  $('.js-button-scene').on('click', function() {
    $('#loading').fadeIn();
    var scene_id = $(this).data('scene-id');
    $.ajax('/scene/' + scene_id + '/choose', {
      type: 'post',
      data: {},
      success: function(data) {
        $('#loading').fadeOut();
        $('#response').html(data);
        update_home_view();
      }
    });
  });

  $('.js-button-light').on('click', function() {
    button = $(this);
    light_id = button.data('light-id');

    $('#loading').fadeIn();

    data = {
      power: button.data('power') ? 0 : 1
    }

    if(!button.data('static')) {
      if(button.data('power')) {
        button.removeClass('btn-success');
        button.addClass('btn-default');
      } else {
        button.addClass('btn-success');
        button.removeClass('btn-default');
      }
      button.data('power', !button.data('power'));
    }

    $.ajax('/light/' + light_id, {
      type: 'post',
      data: data,
      success: function(data) {
        $('#loading').fadeOut();
        $('#response').html(data);
        update_home_view();
      }
    });
  });

  $('.js-button-save-scene').on('click', function() {
    $('#loading').fadeIn();
    $.ajax('/scene', {
      type: 'post',
      data: {},
      success: function(data) {
        $('#loading').fadeOut();
      }
    });
  });

  $('.js-button-save-scene-name').on('click', function() {
    var button = $(this);
    var scene_id = button.data('scene-id');

    $('#loading').fadeIn();
    $.ajax('/scene/' + scene_id, {
      type: 'patch',
      data: {
        name: $('#scene_name_input_' + scene_id).val()
      },
      success: function(data) {
        var response = data; //JSON.parse(data);
        $('#scene_name_label_' + response.scene).html(response.name);
        $('#loading').fadeOut();
      }
    });
  })

  /* Sliders for individual lights ***********************************************************************************/
  $('.js-slider-bri').on('change', function() {
    var slider = $(this);
    var light_id = slider.data('light-id');

    if (!$('#loading').is(':visible')) {
      $('#loading').fadeIn();
    }

    change = {
      light: light_id,
      bri: slider.val()
    }

    window.clearTimeout(slider_timer);
    slider_timer = window.setTimeout(slider_process, 500, change);
  });

  $('.js-slider-ct').on('change', function() {
    var slider = $(this);
    var light_id = slider.data('light-id');

    if (!$('#loading').is(':visible')) {
      $('#loading').fadeIn();
    }

    change = {
      light: light_id,
      ct: slider.val()
    }

    window.clearTimeout(slider_timer);
    slider_timer = window.setTimeout(slider_process, 500, change);
  });

  $('.js-light-control-hs').on('change', function() {
    var picker    = $(this);
    var light_id  = picker.data('light-id');
    var hex_color = picker.val();

    if (!$('#loading').is(':visible')) {
      $('#loading').fadeIn();
    }

    hex_r = hex_color.substring(1,3);
    hex_g = hex_color.substring(3,5);
    hex_b = hex_color.substring(5,7);
    dec_r = parseInt(hex_r, 16);
    dec_g = parseInt(hex_g, 16);
    dec_b = parseInt(hex_b, 16);

    hsl = rgbToHsl(dec_r, dec_g, dec_b);

    console.log('RGB: ' + dec_r + ', ' + dec_g + ', ' + dec_b + ', HSL: ' + hsl.h + ', ' + hsl.s + ', ' + hsl.l);

    state = {
      light: light_id,
      hue: hsl.h,
      sat: hsl.s,
      bri: hsl.l
    }

    // Update the swatch in real time.
    $('#light_swatch_' + light_id).css({ backgroundColor: hex_color });

    window.clearTimeout(picker_timer);
    picker_timer = window.setTimeout(picker_process, 500, state);
  });

  /* Sliders for scenes **********************************************************************************************/
  $('.js-scene-slider-bri').on('change', function() {
    slider = $(this);

    if (!$('#loading').is(':visible')) {
      $('#loading').fadeIn();
    }

    component_ids = slider.attr('name').match(/scene_(\d+)_light_(\d+)_(.*)/)
    scene_id = component_ids[1];
    light_id = component_ids[2];
    control  = component_ids[3];

    change = {
      action: 'update-scene',
      scene:  scene_id,
      light:  light_id,
      bri:    slider.val()
    };

    window.clearTimeout(scene_slider_timer);
    scene_slider_timer = window.setTimeout(scene_slider_process, 500, change);
  });

  $('.js-scene-slider-ct').on('change', function() {
    slider = $(this);

    if (!$('#loading').is(':visible')) {
      $('#loading').fadeIn();
    }

    component_ids = slider.attr('name').match(/scene_(\d+)_light_(\d+)_(.*)/)
    scene_id = component_ids[1];
    light_id = component_ids[2];
    control  = component_ids[3];

    change = {
      action:    'update-scene',
      scene:     scene_id,
      light:     light_id,
      colormode: 'ct',
      ct:        slider.val()
    };

    window.clearTimeout(scene_slider_timer);
    scene_slider_timer = window.setTimeout(function(change) {
      $.ajax('/scene/update/' + scene_id, {
        type: 'post',
        data: change,
        success: function() {
          $('#loading').fadeOut();
          update_home_view();
        }
      });
    }, 500, change);
  });

  $('.js-scene-control-hs').on('change', function() {
    var picker    = $(this);
    var scene_id  = picker.data('scene-id');
    var light_id  = picker.data('light-id');
    var hex_color = picker.val();

    if (!$('#loading').is(':visible')) {
      $('#loading').fadeIn();
    }

    hex_r = hex_color.substring(1,3);
    hex_g = hex_color.substring(3,5);
    hex_b = hex_color.substring(5,7);
    dec_r = parseInt(hex_r, 16);
    dec_g = parseInt(hex_g, 16);
    dec_b = parseInt(hex_b, 16);

    hsl = rgbToHsl(dec_r, dec_g, dec_b);

    console.log('RGB: ' + dec_r + ', ' + dec_g + ', ' + dec_b + ', HSL: ' + hsl.h + ', ' + hsl.s + ', ' + hsl.l);

    change = {
      action:    'update-scene',
      scene:     scene_id,
      light:     light_id,
      colormode: 'hs',
      hue:       hsl.h,
      sat:       hsl.s,
      bri:       hsl.l
    }

    window.clearTimeout(scene_picker_timer);
    scene_picker_timer = window.setTimeout(function(change) {
      $.ajax('/scene/update/' + scene_id, {
        type: 'post',
        data: change,
        success: function() {
          $('#loading').fadeOut();
          update_home_view();
        }
      });
    }, 500, change);
  });

  $('.js-scene-light-power').on('click', function() {
    var button = $(this);
    var scene_id = button.data('scene-id'),
        light_id = button.data('light-id'),
        power    = !button.data('power');

    $('#loading').fadeIn();

    if (power) {
      button.addClass('btn-success').removeClass('btn-default');
    } else {
      button.addClass('btn-default').removeClass('btn-success');
    }

    $.ajax('/scene/update/' + scene_id, {
      type: 'post',
      data: {
        light: light_id,
        power: power
      },
      success: function() {
        $('#loading').fadeOut();
      }
    });
  });
});

function scene_slider_process(change) {
  $.ajax('/scene/update/' + change.scene, {
    type: 'post',
    data: change,
    success: function() {
      $('#loading').fadeOut();
      update_home_view();
    }
  });
}

function slider_process(state) {
  if (state.bri) {
    $.extend(state, {
      action: 'update-bri'
    });
  } else if (state.ct) {
    $.extend(state, {
      action: 'update-ct'
    });
  }

  $.ajax('/light/' + state.light, {
    type: 'post',
    data: state,
    success: function(data) {
      $('#loading').fadeOut();
      $('#response').html(data);
      update_home_view();
    }
  });
}

function picker_process(state) {
  $.extend(state, {
    action: 'update-hsl'
  });

  $.ajax('/light/set/' + state.light, {
    type: 'post',
    data: state,
    success: function(data) {
      $('#loading').fadeOut();
      $('#response').html(data);
      update_home_view();
    }
  });
}
