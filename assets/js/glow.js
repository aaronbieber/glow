Backbone.emulateHTTP = true;

var app = {};

app.util = {
    get_template: function(template_name) {
        return $('#t-' + template_name).html();
    },

    move_array_element: function (array, old_index, new_index) {
        array.splice(new_index, 0, array.splice(old_index, 1)[0]);
    }
};

app.NavigationLink = Backbone.Model.extend({
    defaults: {
        alias: '',
        name: '',
        active: false
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

app.Scene = Backbone.Model.extend({
    defaults: {
        id: 0,
        name: '',
        lights: []
    },
    urlRoot: '/scene'
});

app.SceneSaveView = Backbone.View.extend({
    id: 'scene-save-view',

    events: {
        'click .js-scene-save':        'save',
        'click .js-scene-save-cancel': 'cancel',
        'click .js-overwrite-yes':     'overwrite',
        'click .js-overwrite-no':      'overwrite_cancel',

        'keyup .js-scene-name':        'check_name',
        'input .js-scene-name':        'check_name'
    },

    initialize: function() {
        this.template = app.util.get_template('scene-save');
        this.overwrite = false;
    },

    save: function() {
        if (this.overwrite) {
            this.$el.find('.js-save-buttons').hide();
            this.$el.find('.js-overwrite-confirm').show();
        } else {
            // Save a new scene.
            var new_scene = new app.Scene;
            new_scene.set('name', this.$el.find('#scene-save-name').val());
            new_scene.set('lights', app.lightCollection);

            $.ajax('/scene', {
                method: 'POST',
                data: new_scene,
                contentType: 'application/json',
                data: JSON.stringify(new_scene),
                success: function() {
                },
                failure: function() {
                }
            });
        }
    },

    overwrite_cancel: function() {
        this.$el.find('.js-overwrite-confirm').hide();
        this.$el.find('.js-save-buttons').show();
    },

    cancel: function() {
        app.ModalManager.hide();
        return false;
    },

    check_name: function() {
        var scene_exists = function(name) {
            for(idx in app.sceneCollection.models) {
                var model = app.sceneCollection.models[idx];
                if (model.get('name').toLowerCase() == name.toLowerCase()) {
                    return model.get('name');
                }
            }
            return false;
        };

        var name = this.$el.find('#scene-save-name').val().trim();
        var found = scene_exists(name);
        if(found !== false) {
            this.$el.find('.js-scene-save').html('Overwrite ' + '"' + found + '"');
            this.$el.find('.js-overwrite-scene-name').html(found);
            this.overwrite = true;
        } else {
            this.$el.find('.js-scene-save').html('Save');
            this.overwrite = false;
        }
    },

    onShow: function() {
        $(window).on('scroll', function() {
            $('#modal-overlay').css({ top: window.pageYOffset + 'px' });
        });
    },

    close: function() {
        $(window).off('scroll');
    },

    render: function() {
        var scenes = _.map(
            app.sceneCollection.models,
            function(s) {
                return { name: s.get('name') };
            }
        );
        this.$el.html(Mustache.render(this.template, { scenes: scenes }));
    }
});

app.SceneCollection = Backbone.Collection.extend({
    model: app.Scene,
    url: '/scenes',

    save: function() {
        var collection = _.reduce(
            this.models,
            function(memo, model) {
                memo.push(model);
                return memo;
            },
            []
        );
        $.ajax(this.url, {
            method: 'POST',
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            contentType: 'application/json',
            data: JSON.stringify(collection),
            success: function() {
            },
            failure: function() {
            }
        });
    }
});

app.Light = Backbone.Model.extend({
    defaults: {
        id: 0,
        name: '',
        power: false,
        has_ct: true,
        has_hs: true,
        colormode: 'ct',
        ct: 400,
        hue: 0,
        sat: 0,
        bri: 0,
        hex: '#000000'
    },
    urlRoot: '/light'
});

app.LightCollection = Backbone.Collection.extend({
    model: app.Light,
    url: '/lights'
});

app.RegionManager = (function(Backbone, $) {
    var currentView;
    var el = '#container';
    var region = {};

    var closeView = function(view) {
        if(view && view.close) {
            view.close();
        }
    };

    var openView = function(view) {
        view.render();
        $(el).html(view.el);
        if(view.onShow) {
            view.onShow();
        }
    };

    region.show = function(view) {
        closeView(currentView);
        currentView = view;
        openView(currentView);
    };

    return region;
})(Backbone, jQuery);

app.ModalManager = (function(Backbone, $) {
    var currentModal;
    var el = '#container';
    var id = 'modal-overlay';
    var modal = {};

    var closeModal = function(view) {
        if(view && view.close) {
            view.close();
        }
        $(el).find('#'+id).remove();
    };

    var openModal = function(view) {
        view.render();

        var target = $('<div>').attr('id', id).append($('<div>').attr('id', 'modal'));
        target.find('#modal').html(view.el);
        $(el).append(target);

        if(view.onShow) {
            view.onShow();
        }
    };

    modal.show = function(view) {
        closeModal(currentModal);
        currentModal = view;
        openModal(currentModal);
    };

    modal.hide = function() {
        closeModal(currentModal);
    };

    return modal;
})(Backbone, jQuery);

app.NavigationView = Backbone.View.extend({
    el: $('#navigation'),

    initialize: function() {
        this.template = app.util.get_template('navigation');
    },

    render: function() {
        this.$el.html(Mustache.render(this.template, this.collection.toJSON()));
        return this;
    }
});

app.ModalView = Backbone.View.extend({
    id: 'modal',

    render: function() {
        $('#container').append(this.$el);
    }
});

app.ScenePageView = Backbone.View.extend({
    tagName: 'div',
    id: 'scenes-page',

    events: {
        'click .js-sort': 'sort_toggle'
    },

    initialize: function() {
        this.template = app.util.get_template('scenes');
        this.sorting = false;
    },

    onShow: function() {
        Tipped.create('.light-swatch');
    },

    sort_toggle: function() {
        if (!this.sorting) {
            this.$('.scene-commands .js-sort').addClass('btn-success').html('Save Sorting');
            this.$('#scene-list .scene-controls').hide();
            // This is dodgy.
            this.$('#scene-list .scene-name').removeClass('col-xs-5').addClass('col-xs-12');
            this.$('#scene-list .handle').show();
            this.sortable = Sortable.create($('#scene-list')[0], {
                handle: '.handle',
                ghostClass: 'ghost',
                onEnd: function(e) {
                    // Move scene within the models array; we'll re-set the sort properties later.
                    // Javascript is better at re-indexing arrays than I am.
                    app.util.move_array_element(app.sceneCollection.models, e.oldIndex, e.newIndex);
                }
            });
            this.sorting = true;
        } else {
            this.$('.scene-commands .js-sort').removeClass('btn-success').html('Sort Scenes');
            // This is dodgy.
            this.$('#scene-list .scene-name').addClass('col-xs-5').removeClass('col-xs-12');
            this.$('#scene-list .scene-controls').show();
            this.$('#scene-list .handle').hide();
            this.sortable.destroy();
            this.sorting = false;

            // Apply the sort to the collection by setting each model's
            // "sort" property to its index in the array. The "sort"
            // property gives us a concrete representation of the order of
            // things that we can persist into the file, and which is
            // neither implied nor magical.
            for(var i = 0; i < this.collection.models.length; i++) {
                this.collection.models[i].set('sort', i);
            }

            this.collection.save();
        }
    },

    render: function() {
        this.$el.html(Mustache.render(this.template));

        this.collection.each(function(scene) {
            var view = new app.SceneRowView({ model: scene });
            this.$el.find('#scene-list').append(view.render().el);
        }, this);
    }
});

app.SceneRowView = Backbone.View.extend({
    tagName: 'div',
    className: 'scene-row',

    initialize: function() {
        this.template = app.util.get_template('scene-row');
    },

    events: {
        'click .js-button-scene': 'choose'
    },

    choose: function(e) {
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

app.SceneView = Backbone.View.extend({
    tagName: 'div',
    id: 'scene-page',

    initialize: function() {
        this.template = app.util.get_template('scene');
        this.model.on('sync', this.render, this);
    },

    events: {
        'click .js-name-edit':        'edit_start',
        'click .js-name-edit-cancel': 'edit_cancel',
        'click .js-name-edit-save':   'edit_save',
        'click .js-light-edit':       'light_edit'
    },

    edit_start: function() {
        this.$el.find('.name-edit').show();
        this.$el.find('.js-name-edit').hide();
        this.$el.find('.name-label').hide();
    },

    edit_cancel: function() {
        this.$el.find('.name-edit').hide();
        this.$el.find('.name-entry').val(this.model.get('name'));
        this.$el.find('.js-name-edit').show();
        this.$el.find('.name-label').show();
    },

    edit_save: function() {
        this.model.set('name', $('.name-entry').val());
        this.model.save(null, {
            error: _.bind(function(model, response, options) {
                this.$el.find('.name-edit').addClass('has-error');
            }, this)
        });
    },

    light_edit: function(e) {
        var light_id = $(e.currentTarget).data('light-id');
        console.log(light_id);
    },

    render: function() {
        var scene = this.model.toJSON();
        this.$el.html(Mustache.render(this.template, scene));

        return this;
    }
});

app.LightPageView = Backbone.View.extend({
    tagName: 'div',
    id: 'lights-page',

    events: {
        'click .js-save-current': 'save_current'
    },

    initialize: function() {
        this.template = app.util.get_template('lights');
    },

    save_current: function() {
        var tpl = new app.SceneSaveView();
        app.ModalManager.show(tpl);
    },

    render: function() {
        this.$el.html(Mustache.render(this.template));
        var $lights_list = this.$el.find('#light-list');

        this.collection.each(function(light) {
            var view = new app.LightRowView({ model: light });
            $lights_list.append(view.render().el);
        }, this);
    }
});

app.LightRowView = Backbone.View.extend({
    tagName: 'div',

    initialize: function() {
        this.model.on('sync', this.render, this);
        this.template = app.util.get_template('light-row');
    },

    events: {
        'click .js-button-light': 'toggle'
    },

    toggle: function(e) {
        this.model.set('power', !this.model.get('power'));
        this.model.save();
    },

    close: function() {
        this.model.off('sync');
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
    id: 'light-page',

    initialize: function() {
        this.template = app.util.get_template('light');
        this.model.on(
            'sync',
            function() { this.render(); this.onShow(); },
            this
        );
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
        };
        this.model.set('hue', color.hue);
        this.model.set('sat', color.sat);
        this.model.set('bri', color.bri);
        this.model.save();
    },

    onShow: function() {
        if($('#picker').length) {
            delete app.picker;
            app.picker = $.farbtastic($('#picker'));
            app.picker.setHSL(
                [ this.model.get('hue')/65535,
                  this.model.get('sat')/255,
                  this.model.get('bri')/255 ]
            );
            app.picker.linkTo(_.bind(this.picker_change, this));
        }
    },

    close: function() {
        this.model.off('sync');
    },

    render: function() {
        var light = this.model.toJSON();
        this.$el.html(Mustache.render(this.template, light));

        return this;
    }
});

app.Router = Backbone.Router.extend({
    routes: {
        '':             'scenes',
        'scenes':       'scenes',
        'scene/:scene': 'scene',
        'lights':       'lights',
        'light/:light': 'light'
    },

    scenes: function() {
        app.scenePageView = new app.ScenePageView({ collection: app.sceneCollection });
        app.RegionManager.show(app.scenePageView);

        app.navigationLinkCollection.select('scenes');
        app.navigationView.render();
    },

    scene: function(scene_id) {
        if(!app.sceneCollection.length) {
            var model = new app.Scene({ id: scene_id });
            model.fetch();
        } else {
            var model = app.sceneCollection.get(scene_id);
        }

        app.RegionManager.show(new app.SceneView({ model: model }));

        app.navigationLinkCollection.select('');
        app.navigationView.render();
    },

    lights: function() {
        var lightPageView = new app.LightPageView({ collection: app.lightCollection });
        app.RegionManager.show(lightPageView);

        app.navigationLinkCollection.select('lights');
        app.navigationView.render();
    },

    light: function(light_id) {
        if(!app.lightCollection.length) {
            var model = new app.Light({ id: light_id });
            model.fetch();
        } else {
            var model = app.lightCollection.get(light_id);
        }

        app.RegionManager.show(new app.LightView({ model: model }));

        app.navigationLinkCollection.select('');
        app.navigationView.render();
    }
});
