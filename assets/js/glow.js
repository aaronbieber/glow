Backbone.emulateHTTP = true;

var app = {};

$(document).ready(function() {

  app.util = {
    get_template: function(template_name) {
      return $('#t-' + template_name).html();
    }
  }

  app.NavigationLink = Backbone.Model.extend({
    defaults: {
      alias: '',
      name: '',
      active: false
    }
  });

  app.Scene = Backbone.Model.extend({
    defaults: {
      id: 0,
      name: '',
      lights: []
    }
  });

  app.Light = Backbone.Model.extend({
    defaults: {
      id: 0,
      name: '',
      power: false,
      colormode: 'ct',
      ct: 400,
      hue: 0,
      sat: 0,
      bri: 0,
      hex: '#000000'
    },
    urlRoot: '/light'
  });

  app.AppView = Backbone.View.extend({
    el: $('#container'),

    initialize: function() {
      app.sceneCollection.on('add', this.addScene, this);
      app.sceneCollection.on('reset', this.addAllScenes, this);
      app.lightCollection.on('add', this.addLight, this);
      app.lightCollection.on('reset', this.addAllLights, this);

      app.navigationView.render();
    },

    addScene: function(scene) {
      var view = new app.SceneView({ model: scene });
      this.$el.append(view.render().el);
    },

    addAllScenes: function(scenes) {
      this.$el.html('');
      app.sceneCollection.each(this.addScene, this);
    },

    addLight: function(light) {
      var view = new app.LightRowView({ model: light });
      this.$el.append(view.render().el);
    },

    addAllLights: function(lights) {
      this.$el.html('');
      app.lightCollection.each(this.addLight, this);
    }
  });

  app.NavigationLinkCollection = Backbone.Collection.extend({
    model: app.NavigationLink,
    select: function(link_alias) {
      this.each(function(model) {
        if(model.get('alias') == link_alias) {
          model.set('active', true);
        } else {
          model.set('active', false);
        }
      });
    }
  });

  app.NavigationView = Backbone.View.extend({
    el: $('#navigation'),
    template: app.util.get_template('navigation'),
    render: function() {
      this.$el.html(Mustache.render(this.template, this.collection.toJSON()));
      return this;
    }
  });

  app.SceneView = Backbone.View.extend({
    tagName: 'div',
    template: app.util.get_template('scene-row'),

    events: {
      'click .js-button-scene': 'choose'
    },

    choose: function(e) {
      console.log(this.model.get('name'));

      app.loadingToast.fadeIn();
      $.ajax('/scene/' + this.model.get('id') + '/choose', {
        type: 'post',
        data: {},
        success: function(data) {
          app.loadingToast.fadeOut();
          app.lightCollection.fetch();
        }
      });
    },

    render: function() {
      this.$el.html(Mustache.render(this.template, this.model.toJSON()));
      return this;
    }
  });

  app.LightRowView = Backbone.View.extend({
    tagName: 'div',
    template: app.util.get_template('light-row'),

    initialize: function() {
      this.model.bind('sync', this.render, this);
    },

    events: {
      'click .js-button-light': 'toggle'
    },

    toggle: function(e) {
      this.model.set('power', !this.model.get('power'));
      this.model.save();
    },

    render: function() {
      var light = this.model.toJSON();
      light.power_text = light.power ? 'true' : 'false';
      this.$el.html(Mustache.render(this.template, light));
      return this;
    }
  });

  app.LightView = Backbone.View.extend({
    tagName: 'div',
    template: app.util.get_template('light'),

    initialize: function() {
      this.model.bind('sync', this.render, this);
    },

    events: {
      'click .js-button-light': 'toggle',
      'change .js-slider-bri': 'brightness',
      'change .js-slider-ct': 'temperature'
    },

    toggle: function() {
      this.model.set('power', !this.model.get('power'));
      this.model.save();
    },

    brightness: function(e) {
      var brightness = e.target.value;
      this.model.set('colormode', 'ct');
      this.model.set('bri', brightness);
      this.model.save();
    },

    temperature: function(e) {
      var ct = e.target.value;
      this.model.set('colormode', 'ct');
      this.model.set('ct', ct);
      this.model.save();
    },

    picker_change: function() {
      window.clearTimeout(app.picker_timer);
      app.picker_timer = window.setTimeout(
        _.bind(this.hsl, this),
        500
      );
    },

    hsl: function() {
      this.model.set('colormode', 'hs');
      color = {
        hue: Math.round(app.picker.hsl[0] * 65535),
        sat: Math.round(app.picker.hsl[1] * 255),
        bri: Math.round(app.picker.hsl[2] * 255)
      }
      this.model.set('hue', color.hue);
      this.model.set('sat', color.sat);
      this.model.set('bri', color.bri);
      this.model.save();
    },

    render: function() {
      var light = this.model.toJSON();
      this.$el.html(Mustache.render(this.template, light));
      app.picker = $.farbtastic(this.$el.find('#picker'));
      app.picker.setHSL(
        [ this.model.get('hue')/65535,
          this.model.get('sat')/255,
          this.model.get('bri')/255 ]
      );
      app.picker.linkTo(_.bind(this.picker_change, this));
      return this;
    }
  });

  app.SceneCollection = Backbone.Collection.extend({
    model: app.Scene,
    url: '/scenes'
  });
  app.sceneCollection = new app.SceneCollection([]);

  app.LightCollection = Backbone.Collection.extend({
    model: app.Light,
    url: '/lights'
  });
  app.lightCollection = new app.LightCollection([]);

  app.Router = Backbone.Router.extend({
    routes: {
      '':             'scenes',
      'scenes':       'scenes',
      'lights':       'lights',
      'light/:light': 'light'
    },

    scenes: function() {
      app.appView.$el.html('');
      app.sceneCollection.fetch();
      app.sceneCollection.trigger('reset');

      app.navigation.select('scenes');
      app.navigationView.render();
    },

    lights: function() {
      app.appView.$el.html('');
      app.lightCollection.fetch({ success: function() {}});
      app.lightCollection.trigger('reset');

      app.navigation.select('lights');
      app.navigationView.render();
    },

    light: function(light_id) {
      app.appView.$el.html('');

      if (!app.lightCollection.length) {
        var model = new app.Light({ id: light_id });
        model.fetch();
      } else {
        var model = app.lightCollection.get(light_id);
      }
      var view = new app.LightView({ model: model });
      app.appView.$el.append(view.render().el);

      app.navigation.select('');
      app.navigationView.render();
    }
  });

  // Start everything
  app.navigation = new app.NavigationLinkCollection([
    (new app.NavigationLink({ alias: 'scenes', name: 'Scenes', active: true })),
    (new app.NavigationLink({ alias: 'lights', name: 'Lights' }))
  ]);
  app.navigationView = new app.NavigationView({ collection: app.navigation });

  app.loadingToast = $('#loading');
  app.appView = new app.AppView();
  app.router = new app.Router();
  Backbone.history.start();
});
