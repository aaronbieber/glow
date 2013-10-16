var sliders = {};
var pickers = {};
var picker_timer = null;

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
  $('ul.navbar-nav a').each(function() {
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
  }

  $('ul.navbar-nav a').on('click', function() {
    select_page($(this).attr('href'));
  });

  $('.js-toggle-response').on('click', function() {
    $('#response').slideToggle();
  });

  $('.js-toggle-controls').on('click', function() {
    button = $(this);
    light_id = button.data('id');
    $('#controls_' + light_id).slideToggle();
  });

  $('.js-light-control-hs').on('change', function() {
    var light_name = $(this).data('light');
    var light_id = $(this).data('light-id');
    var hex_color = $(this).val();

    hex_r = hex_color.substring(1,3);
    hex_g = hex_color.substring(3,5);
    hex_b = hex_color.substring(5,7);
    dec_r = parseInt(hex_r, 16);
    dec_g = parseInt(hex_g, 16);
    dec_b = parseInt(hex_b, 16);

    hsl = rgbToHsl(dec_r, dec_g, dec_b);
    console.log('RGB: ' + dec_r + ', ' + dec_g + ', ' + dec_b + ', HSL: ' + hsl.h + ', ' + hsl.s + ', ' + hsl.l);

    pickers[light_name] = {
      id: light_id,
      new: {
        h: hsl.h,
        s: hsl.s,
        b: hsl.l
      }
    }

    window.clearTimeout(picker_timer);
    picker_timer = window.setTimeout(picker_process, 250);
  });
                            
  $('.js-toggle-colormode').on('click', function() {
    button = $(this);
    // Find the other button by looking around.
    other_button = button.siblings('button');

    if (!button.data('active')) {
      // Toggle to the other button.
      button.data('active', true);
      button.addClass('btn-primary');
      other_button.data('active', false);
      other_button.removeClass('btn-primary');

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
    var scene_id = $(this).data('scene-id');
    $.ajax('/', {
      type: 'post',
      data: { scene: scene_id },
      success: function() {}
    });
  });

  $('.js-button-light').on('click', function() {
    button = $(this);
    light_id = button.data('light-id');

    button.closest('tr').addClass('loading');

    data = {
      light: light_id,
      power: button.data('power') ? 'off' : 'on'
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

    $.ajax('/', {
      type: 'post',
      data: data,
      success: function(data) {
        $('#response').html(data);
        button.closest('tr').removeClass('loading');
      }
    });
  });

  $('.js-slider-bri').on('change', function() {
    window.setTimeout(slider_process, 500);
    slider = $(this);
    sliders[slider.attr('name')] = { current: { bri: null }, new: { bri: slider.val() } };
  });

  $('.js-slider-ct').on('change', function() {
    window.setTimeout(slider_process, 500);
    slider = $(this);
    sliders[slider.attr('name')] = { current: { ct: null }, new: { ct: slider.val() } };
  });
});

function slider_process() {
  for(var light in sliders) {
    light_id = light.match(/light_(\d+)_/)[1];
    state_key = light.match(/light_\d+_(.*)/)[1];

    state = {};
    if(sliders[light]['current']['bri'] != sliders[light]['new']['bri']) {
      state['bri'] = sliders[light]['new']['bri'];
      sliders[light]['current']['bri'] = sliders[light]['new']['bri'];
    }

    if(sliders[light]['current']['ct'] != sliders[light]['new']['ct']) {
      state['ct'] = sliders[light]['new']['ct'];
      sliders[light]['current']['ct'] = sliders[light]['new']['ct'];
    }

    if(Object.keys(state).length) {
      state['light'] = light_id;

      $.ajax('/', {
        type: 'post',
        data: state,
        success: function(data) {
          $('#response').html(data);
        }
      });

      window.setTimeout(slider_process, 500);
    }
  }
}

function picker_process() {
  for(var picker in pickers) {
    if(  !pickers[picker].current
      || JSON.stringify(pickers[picker].current) !== JSON.stringify(pickers[picker].new)
    ) {
      // Acknowledge that there was a change that we observed and come back in 250 ms to look again.
      pickers[picker].current = pickers[picker].new;
      window.setTimeout(picker_process, 500);
    } else if(JSON.stringify(pickers[picker].current) === JSON.stringify(pickers[picker].new)) {
      // If the values are the same, it's been 250ms since the last time we observed a change.

      // Store the values in a new temporary object.
      color = {
        hue: pickers[picker].new.h,
        sat: pickers[picker].new.s,
        bri: pickers[picker].new.b
      }

      // Translate the values into the Hue ranges.
      //color.hue = Math.floor((color.hue * 65535) / 360);
      //color.sat = Math.floor((color.sat * 255) / 100);
      //color.bri = Math.floor((color.bri * 255) / 100);

      state = {
        light: pickers[picker].id,
        hue: color.hue,
        sat: color.sat,
        bri: color.bri
      }

      $.ajax('/', {
        type: 'post',
        data: state,
        success: function(data) {
          $('#response').html(data);
        }
      });
    }
  }
}
