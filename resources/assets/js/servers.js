var app = app || {};

(function ($) {
    var SUCCESSFUL = 0;
    var UNTESTED   = 1;
    var FAILED     = 2;
    var TESTING    = 3;

    $('#server_list table').sortable({
        containerSelector: 'table',
        itemPath: '> tbody',
        itemSelector: 'tr',
        placeholder: '<tr class="placeholder"/>',
        delay: 500,
        onDrop: function (item, container, _super) {
            _super(item, container);

            var ids = [];
            $('tbody tr td:first-child', container.el[0]).each(function (idx, element) {
                ids.push($(element).data('server-id'));
            });

            $.ajax({
                url: '/servers/reorder',
                method: 'POST',
                data: {
                    servers: ids
                }
            });
        }
    });

    // FIXME: This seems very wrong
    $('#server').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var title = Lang.get('servers.create');

        $('.btn-danger', modal).hide();
        $('.callout-danger', modal).hide();
        $('.has-error', modal).removeClass('has-error');
        $('.label-danger', modal).remove();
        $('#add-server-command', modal).hide();

        if (button.hasClass('btn-edit')) {
            title = Lang.get('servers.edit');
            $('.btn-danger', modal).show();
        } else {
            $('#server_id').val('');
            $('#server_name').val('');
            $('#server_address').val('');
            $('#server_port').val('22');
            $('#server_user').val('');
            $('#server_path').val('');
            $('#server_deploy_code').prop('checked', true);
            $('#add-server-command', modal).show();
        }

        modal.find('.modal-title span').text(title);
    });

    // FIXME: This seems very wrong
    $('#server button.btn-delete').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('fa-refresh fa-spin').removeClass('fa-trash');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server = app.Servers.get($('#server_id').val());

        server.destroy({
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('fa-refresh fa-spin').addClass('fa-trash');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            },
            error: function() {
                icon.removeClass('fa-refresh fa-spin').addClass('fa-trash');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // FIXME: This seems very wrong
    $('#server button.btn-save').on('click', function (event) {
        var target = $(event.currentTarget);
        var icon = target.find('i');
        var dialog = target.parents('.modal');

        icon.addClass('fa-refresh fa-spin').removeClass('fa-save');
        dialog.find('input').attr('disabled', 'disabled');
        $('button.close', dialog).hide();

        var server_id = $('#server_id').val();

        if (server_id) {
            var server = app.Servers.get(server_id);
        } else {
            var server = new app.Server();
        }

        server.save({
            name:         $('#server_name').val(),
            ip_address:   $('#server_address').val(),
            port:         $('#server_port').val(),
            user:         $('#server_user').val(),
            path:         $('#server_path').val(),
            deploy_code:  $('#server_deploy_code').is(':checked'),
            project_id:   parseInt($('input[name="project_id"]').val()),
            add_commands: $('#server_commands').is(':checked')
        }, {
            wait: true,
            success: function(model, response, options) {
                dialog.modal('hide');
                $('.callout-danger', dialog).hide();

                icon.removeClass('fa-refresh fa-spin').addClass('fa-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');

                if (!server_id) {
                    app.Servers.add(response);
                }
            },
            error: function(model, response, options) {
                $('.callout-danger', dialog).show();

                var errors = response.responseJSON;

                $('.has-error', dialog).removeClass('has-error');
                $('.label-danger', dialog).remove();

                $('form input', dialog).each(function (index, element) {
                    element = $(element);

                    var name = element.attr('name');

                    if (typeof errors[name] !== 'undefined') {
                        var parent = element.parents('div.form-group');
                        parent.addClass('has-error');
                        parent.append($('<span>').attr('class', 'label label-danger').text(errors[name]));
                    }
                });

                icon.removeClass('fa-refresh fa-spin').addClass('fa-save');
                $('button.close', dialog).show();
                dialog.find('input').removeAttr('disabled');
            }
        });
    });

    // $('#server [data-server-template-id]').on('click', function () {
    //     var server_template_id = $(this).data('server-template-id');
    //     var server_template = app.ServerTemplates.get(server_template_id);
    //     $('#server_name').val(server_template.get('name'));
    //     $('#server_address').val(server_template.get('ip_address'));
    //     $('#server_port').val(server_template.get('port'));
    //     $('.nav-tabs a[href="#server_details"]').tab('show');
    // });

    app.Server = Backbone.Model.extend({
        urlRoot: '/servers'
    });

    var Servers = Backbone.Collection.extend({
        model: app.Server
    });

    app.Servers = new Servers();

    app.ServersTab = Backbone.View.extend({
        el: '#app',
        events: {

        },
        initialize: function() {
            this.$list = $('#server_list tbody');

            $('#no_servers').show();
            $('#server_list').hide();

            this.listenTo(app.Servers, 'add', this.addOne);
            this.listenTo(app.Servers, 'reset', this.addAll);
            this.listenTo(app.Servers, 'remove', this.addAll);
            this.listenTo(app.Servers, 'all', this.render);

            app.listener.on('projectserver:REBELinBLUE\\Deployer\\Events\\ModelChanged', function (data) {
                console.log(data);
                var server = app.Servers.get(parseInt(data.model.id));

                if (server) {
                    server.set(data.model);
                }
            });

            app.listener.on('projectserver:REBELinBLUE\\Deployer\\Events\\ModelCreated', function (data) {
                if (parseInt(data.model.project_id) === parseInt(app.project_id)) {
                    app.Servers.add(data.model);
                }
            });

            app.listener.on('projectserver:REBELinBLUE\\Deployer\\Events\\ModelTrashed', function (data) {
                var server = app.Servers.get(parseInt(data.model.id));

                if (server) {
                    app.Servers.remove(server);
                }
            });
        },
        render: function () {
            if (app.Servers.length) {
                $('#no_servers').hide();
                $('#server_list').show();
            } else {
                $('#no_servers').show();
                $('#server_list').hide();
            }
        },
        addOne: function (server) {
            var view = new app.ServerView({
                model: server
            });

            this.$list.append(view.render().el);
        },
        addAll: function () {
            this.$list.html('');
            app.Servers.each(this.addOne, this);
        }
    });

    app.ServerView = Backbone.View.extend({
        tagName:  'tr',
        events: {
            'click .btn-test': 'testConnection',
            'click .btn-edit': 'editServer',
            'click .btn-view': 'viewLog'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', this.remove);

            this.template = _.template($('#server-template').html());
        },
        render: function () {
            var data = this.model.toJSON();

            data.status_css  = 'primary';
            data.icon_css    = 'question';
            data.status      = Lang.get('servers.untested');
            data.has_log     = false;
            data.deploy_code = data.deploy_code;
            data.name        = data.server.name;
            data.user        = data.user ? data.user : data.server.user;
            data.connect_log = data.connect_log;
            data.order       = data.order;
            data.ip_address  = data.server.ip_address;
            data.port        = data.server.port;
            data.type        = Lang.get('servers.project');
            if (data.server.type === 'shared') {
                data.type = Lang.get('servers.shared');
            }

            if (parseInt(this.model.get('status')) === SUCCESSFUL) {
                data.status_css = 'success';
                data.icon_css   = 'check';
                data.status     = Lang.get('servers.successful');
            } else if (parseInt(this.model.get('status')) === TESTING) {
                data.status_css = 'warning';
                data.icon_css   = 'spinner fa-pulse';
                data.status     = Lang.get('servers.testing');
            } else if (parseInt(this.model.get('status')) === FAILED) {
                data.status_css = 'danger';
                data.icon_css   = 'warning';
                data.status     = Lang.get('servers.failed');
                data.has_log    = data.connect_log ? true : false;
            }

            this.$el.html(this.template(data));

            return this;
        },
        editServer: function() {
            // FIXME: Sure this is wrong?
            $('#server_id').val(this.model.id);
            $('#server_name').val(this.model.get('server').name);
            $('#server_address').val(this.model.get('server').ip_address);
            $('#server_port').val(this.model.get('server').port);

            if (this.model.get('type') === 'shared') {
              $('#server_user').val(this.model.user);
              $('#server_path').val(this.model.path);

              $('server_user').attr('placeholder', this.model.get('server').user);
            } else {
              $('#server_user').val(this.model.get('server').user);
              $('#server_path').val(this.model.get('server').path);
            }

            $('#server_deploy_code').prop('checked', (this.model.get('deploy_code') === true));
        },
        viewLog: function() {
            var modal = $('div.modal#result');
            var title = Lang.get('servers.log_title');

            modal.find('pre').html(parseOutput(this.model.get('connect_log')));
            modal.find('.modal-title span').text(title);
        },
        testConnection: function() {
            if (parseInt(this.model.get('status')) === TESTING) {
                return;
            }

            this.model.set({
                status: TESTING
            });

            var that = this;
            $.ajax({
                type: 'POST',
                url: this.model.urlRoot + '/' + this.model.id + '/test'
            }).fail(function () {
                that.model.set({
                    status: FAILED
                });
            });
        }
    });
})(jQuery);
