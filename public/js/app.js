(function ($) {

    //App setup
    window.Piplin = {};

    Piplin.project_id = Piplin.project_id || null;

    Piplin.statuses = {
        // Project and Environment
        FINISHED:     0,
        PENDING:      1,
        RUNNING:    2,
        FAILED:       3,
        NOT_DEPLOYED: 4,

        // Task status
        TASK_DRAFT:    -1,
        TASK_COMPLETED: 0,
        TASK_PENDING:   1,
        TASK_RUNNING: 2,
        TASK_FAILED:    3,
        TASK_ERRORS:    4,
        TASK_CANCELLED: 5,
        TASK_ABORTED:   6,

        // Server log status
        SVRLOG_COMPLETED: 0,
        SVRLOG_PENDING:   1,
        SVRLOG_RUNNING:   2,
        SVRLOG_FAILED:    3,
        SVRLOG_CANCELLED: 4
    };

    Piplin.events = {
        // Common events
        MODEL_CREATED: 'Piplin\\Bus\\Events\\ModelCreatedEvent',
        MODEL_CHANGED: 'Piplin\\Bus\\Events\\ModelChangedEvent',
        MODEL_TRASHED: 'Piplin\\Bus\\Events\\ModelTrashedEvent',

        // Server log changed
        SVRLOG_CHANGED: 'Piplin\\Bus\\Events\\ServerLogChangedEvent',
        OUTPUT_CHANGED: 'Piplin\\Bus\\Events\\ServerOutputChangedEvent'

    };

    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
        jqXHR.setRequestHeader('X-CSRF-Token', $('meta[name="token"]').attr('content'));
    });

    // Prevent double form submission
    $('form').submit(function () {
        var $form = $(this);
        $form.find(':submit').prop('disabled', true);
    });

    // Don't need to try and connect to the web socket when not logged in
    if (window.location.href.match(/login|password/) != null) {
        return;
    }

    var locale = $('meta[name="locale"]').attr('content');

    Lang.setLocale(locale);

    moment.locale(locale);

    $('[data-toggle="tooltip"]').tooltip();

    Piplin.select2_options = {
        width: '100%',
        minimumResultsForSearch: Infinity
    };

    $(".select2").select2(Piplin.select2_options);

    //Clipboard
    new Clipboard('.clipboard').on('success', function(e){
        Piplin.toast(trans('app.copied'));
    });

    // Socket.io
    Piplin.listener = io.connect($('meta[name="socket_url"]').attr('content'), {
        query: 'jwt=' + $('meta[name="jwt"]').attr('content')
    });

    Piplin.connection_error = false;

    Piplin.listener.on('connect_error', function(error) {
        if (!Piplin.connection_error) {
            $('#socket_offline').show();
        }

        Piplin.connection_error = true;
    });

    Piplin.listener.on('connect', function() {
        $('#socket_offline').hide();
        Piplin.connection_error = false;
    });

    Piplin.listener.on('reconnect', function() {
        $('#socket_offline').hide();
        Piplin.connection_error = false;
    });

    // Load livestamp
    Piplin.loadLivestamp = function () {
        $('abbr.timeago').each(function () {
            var $el = $(this);
            $el.livestamp($el.data('timeago')).tooltip();
        });
    };

    // Format the project status
    Piplin.formatProjectStatus = function (task_status) {
        var data = {};

        data.icon_class = 'help';
        data.label_class = 'default';
        data.label = trans('projects.not_deployed');

        if (task_status === Piplin.statuses.FINISHED) {
            data.icon_class = 'check';
            data.label_class = 'success';
            data.label = trans('projects.finished');
        } else if (task_status === Piplin.statuses.RUNNING) {
            data.icon_class = 'load piplin-spin';
            data.label_class = 'warning';
            data.label = trans('projects.running');
        } else if (task_status === Piplin.statuses.FAILED) {
            data.icon_class = 'close';
            data.label_class = 'danger';
            data.label = trans('projects.failed');
        } else if (task_status === Piplin.statuses.PENDING) {
            data.icon_class = 'clock';
            data.label_class = 'info';
            data.label = trans('projects.pending');
        }

        return data;
    };

    // Format the deployment status
    Piplin.formatDeploymentStatus = function (task_status) {
        var data = {};

        data.icon_class = 'clock';
        data.label_class = 'info';
        data.label = trans('tasks.pending');
        data.done = false;
        data.success = false;

        if (task_status === Piplin.statuses.TASK_COMPLETED) {
            data.icon_class = 'check';
            data.label_class = 'success';
            data.label = trans('tasks.completed');
            data.done = true;
            data.success = true;
        } else if (task_status === Piplin.statuses.TASK_RUNNING) {
            data.icon_class = 'load piplin-spin';
            data.label_class = 'warning';
            data.label = trans('tasks.running');
        } else if (task_status === Piplin.statuses.TASK_FAILED) {
            data.icon_class = 'close';
            data.label_class = 'danger';
            data.label = trans('tasks.failed');
            data.done = true;
        } else if (task_status === Piplin.statuses.TASK_ERRORS) {
            data.icon_class = 'close';
            data.label_class = 'success';
            data.label = trans('tasks.completed_with_errors');
            data.done = true;
            data.success = true;
        } else if (task_status === Piplin.statuses.TASK_CANCELLED) {
            data.icon_class = 'warning';
            data.label_class = 'danger';
            data.label = trans('tasks.cancelled');
            data.done = true;
        } else if (task_status === Piplin.statuses.TASK_DRAFT) {
            data.icon_class = 'edit';
            data.label_class = 'danger';
            data.label = trans('tasks.draft');
        }

        return data;
    };

    Piplin.toast = function (content, title, caller) {
        title = title || '';
        caller = caller || 'not_in_progress';

        if (!Config.get('piplin.toastr') && caller == 'not_in_progress') {
            return;
        }

        if (caller == 'not_in_progress') {
            toastr.options.positionClass = 'toast-top-center';
            toastr.options.progressBar = false;
            toastr.options.preventDuplicates = true;
            toastr.options.closeDuration = 1000;
            toastr.options.timeOut = 3000;
            toastr.options.extendedTimeOut = 1000;
        } else {
            toastr.options.closeButton = true;
            toastr.options.progressBar = true;
            toastr.options.preventDuplicates = true;
            toastr.options.closeMethod = 'fadeOut';
            toastr.options.closeDuration = 3000;
            toastr.options.closeEasing = 'swing';
            toastr.options.positionClass = 'toast-bottom-right';
            toastr.options.timeOut = 5000;
            toastr.options.extendedTimeOut = 7000;
        }

        if (caller == 'error') {
            return toastr.error(content, title);
        } else if(caller == 'warning') {
            return toastr.warning(content, title);
        } else if(caller == 'info') {
            toastr.options.closeButton = false;
            toastr.options.progressBar = false;
            return toastr.info(content, title);
        } else {
            return toastr.success(content, title);
        }
    };

})(jQuery);
(function ($) {

    Piplin.loadLivestamp();

    Piplin.listener.on('task:' + Piplin.events.MODEL_CHANGED, function (data) {

        // Update todo bar
        updateTodoBar(data);

        if ($('#timeline').length > 0) {
            updateTimeline();
        }

        var task  = $('#task_' + data.model.id);

        if (task.length > 0) {

            $('td.committer', task).text(data.model.committer);

            if (data.model.commit_url) {
                $('td.commit', task).html('<a href="' + data.model.commit_url + '" target="_blank">' + data.model.short_commit + '</a>'+'('+data.model.branch+')');
            }

            var status_bar = $('td.status span', task);

            var status_data = Piplin.formatDeploymentStatus(parseInt(data.model.status));

            if (status_data.done) {
                $('button#deploy_project:disabled').removeAttr('disabled');
                $('td a.btn-cancel', task).remove();

                if (status_data.success) {
                    $('button.btn-rollback').removeClass('hide');
                }
            }

            status_bar.attr('class', 'text-' + status_data.label_class);
            $('i', status_bar).attr('class', 'piplin piplin-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);
        } else {
            var toast_title = trans('tasks.deploy_title', {
                'id': data.model.id
            });

            if (data.model.status === Piplin.statuses.TASK_COMPLETED) {
                Piplin.toast(toast_title + ' - ' + trans('tasks.completed'), data.model.project_name, 'success');
            } else if (data.model.status === Piplin.statuses.TASK_FAILED) {
                Piplin.toast(toast_title + ' - ' + trans('tasks.failed'), data.model.project_name, 'error');
            } else if (data.model.status === Piplin.statuses.TASK_ERRORS) {
                Piplin.toast(toast_title + ' - ' + trans('tasks.completed_with_errors'), data.model.project_name, 'warning');
            } // FIXME: Add cancelled
        }
    });

    Piplin.listener.on('project:' + Piplin.events.MODEL_CHANGED, function (data) {

        var project = $('#project_' + data.model.id);

        if (project.length > 0) {
            var status_bar = $('td.status span', project);

            var status_data = Piplin.formatProjectStatus(parseInt(data.model.status));

            $('td.name', project).text(data.model.name);
            $('td.time', project).text(moment(data.model.last_run).fromNow());
            status_bar.attr('class', 'text-' + status_data.label_class)
            $('i', status_bar).attr('class', 'piplin piplin-' + status_data.icon_class);
            $('span', status_bar).text(status_data.label);
        }
    });

    Piplin.listener.on('project:' + Piplin.events.MODEL_TRASHED, function (data) {

        if (parseInt(data.model.id) === parseInt(Piplin.project_id)) {
            window.location.href = '/';
        }
    });

    Piplin.listener.on('task:' + Piplin.events.MODEL_CREATED, function (data) {
        var userId = parseInt($('meta[name="user_id"]').attr('content'));

        if (data.model.user_id == userId) {
            Piplin.toast(trans('tasks.create_success'), '', 'info').on('click', function(){
                window.location.href = '/task/' + data.model.id;
            });
        }
    });

    function updateTimeline() {
        $.ajax({
            type: 'GET',
            url: '/timeline'
        }).done(function (response) {
            $('#timeline').html(response);
            Piplin.loadLivestamp();
        });
    }

    function updateTodoBar(data) {
        data.model.time = moment(data.model.started_at).fromNow();
        data.model.url = '/task/' + data.model.id;

        $('#task_info_' + data.model.id).remove();

        var template = _.template($('#task-list-template').html());
        var html = template(data.model);

        if (data.model.status === Piplin.statuses.TASK_RUNNING) {
            $('.running_menu').append(html);
        }

        var running = $('.running_menu li.todo_item').length;
        var pending = $('.pending_menu li.todo_item').length;

        var todo_count = running+pending;

    
        if(todo_count > 0) {
            $('#todo_menu span.label').html(todo_count).addClass('label-success');
            $('#todo_menu .dropdown-toggle i.ion').addClass('text-danger');
        } else {
            $('#todo_menu span.label').html('').removeClass('label-success');
            $('#todo_menu .dropdown-toggle i.ion').removeClass('text-danger')
        }

        var empty_template = _.template($('#todo-item-empty-template').html());

        if(running > 0) {
            $('.running_header i').addClass('piplin-spin');
            $('.running_menu li.item_empty').remove();
        } else {
            $('.running_header i').removeClass('piplin-spin');
            $('.running_menu li.item_empty').remove();
            $('.running_menu').append(empty_template({empty_text:trans('dashboard.running_empty')}));
        }

        var pending_label = Lang.choice('dashboard.pending', pending, {
            'count': pending
        });
        var running_label = Lang.choice('dashboard.running', running, {
            'count': running
        });

        $('.running_header span').text(running_label);
        $('.pending_header span').text(pending_label);
    }

})(jQuery);
var iframeCount = 0;

function Uploader(options) {
  if (!(this instanceof Uploader)) {
    return new Uploader(options);
  }
  if (isString(options)) {
    options = {trigger: options};
  }

  var settings = {
    trigger: null,
    name: null,
    action: null,
    data: null,
    accept: null,
    change: null,
    error: null,
    multiple: true,
    success: null
  };
  if (options) {
    $.extend(settings, options);
  }
  var $trigger = $(settings.trigger);

  settings.action = settings.action || $trigger.data('action') || '/upload';
  settings.name = settings.name || $trigger.attr('name') || $trigger.data('name') || 'file';
  settings.data = settings.data || parse($trigger.data('data'));
  settings.accept = settings.accept || $trigger.data('accept');
  settings.success = settings.success || $trigger.data('success');
  this.settings = settings;

  this.setup();
  this.bind();
}

// initialize
// create input, form, iframe
Uploader.prototype.setup = function() {
  this.form = $(
    '<form method="post" enctype="multipart/form-data"'
    + 'target="" action="' + this.settings.action + '" />'
  );

  this.iframe = newIframe();
  this.form.attr('target', this.iframe.attr('name'));

  var data = this.settings.data;
  this.form.append(createInputs(data));
  if (window.FormData) {
    this.form.append(createInputs({'_uploader_': 'formdata'}));
  } else {
    this.form.append(createInputs({'_uploader_': 'iframe'}));
  }

  var input = document.createElement('input');
  input.type = 'file';
  input.name = this.settings.name;
  if (this.settings.accept) {
    input.accept = this.settings.accept;
  }
  if (this.settings.multiple) {
    input.multiple = true;
    input.setAttribute('multiple', 'multiple');
  }
  this.input = $(input);

  var $trigger = $(this.settings.trigger);
  this.input.attr('hidefocus', true).css({
    position: 'absolute',
    top: 0,
    right: 0,
    opacity: 0,
    outline: 0,
    cursor: 'pointer',
    height: $trigger.outerHeight(),
    fontSize: Math.max(64, $trigger.outerHeight() * 5)
  });
  this.form.append(this.input);
  this.form.css({
    position: 'absolute',
    top: $trigger.offset().top,
    left: $trigger.offset().left,
    overflow: 'hidden',
    width: $trigger.outerWidth(),
    height: $trigger.outerHeight(),
    zIndex: findzIndex($trigger) + 10
  }).appendTo('body');
  return this;
};

// bind events
Uploader.prototype.bind = function() {
  var self = this;
  var $trigger = $(self.settings.trigger);
  $trigger.mouseenter(function() {
    self.form.css({
      top: $trigger.offset().top,
      left: $trigger.offset().left,
      width: $trigger.outerWidth(),
      height: $trigger.outerHeight()
    });
  });
  self.bindInput();
};

Uploader.prototype.bindInput = function() {
  var self = this;
  self.input.change(function(e) {
    // ie9 don't support FileList Object
    // http://stackoverflow.com/questions/12830058/ie8-input-type-file-get-files
    self._files = this.files || [{
      name: e.target.value
    }];
    var file = self.input.val();
    if (self.settings.change) {
      self.settings.change.call(self, self._files);
    } else if (file) {
      return self.submit();
    }
  });
};

// handle submit event
// prepare for submiting form
Uploader.prototype.submit = function() {
  var self = this;
  if (window.FormData && self._files) {
    // build a FormData
    var form = new FormData(self.form.get(0));
    // use FormData to upload
    form.append(self.settings.name, self._files);

    var optionXhr;
    if (self.settings.progress) {
      // fix the progress target file
      var files = self._files;
      optionXhr = function() {
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
          xhr.upload.addEventListener('progress', function(event) {
            var percent = 0;
            var position = event.loaded || event.position; /*event.position is deprecated*/
            var total = event.total;
            if (event.lengthComputable) {
                percent = Math.ceil(position / total * 100);
            }
            self.settings.progress(event, position, total, percent, files);
          }, false);
        }
        return xhr;
      };
    }
    $.ajax({
      url: self.settings.action,
      type: 'post',
      processData: false,
      contentType: false,
      data: form,
      xhr: optionXhr,
      context: this,
      success: self.settings.success,
      error: self.settings.error
    });
    return this;
  } else {
    // iframe upload
    self.iframe = newIframe();
    self.form.attr('target', self.iframe.attr('name'));
    $('body').append(self.iframe);
    self.iframe.one('load', function() {
      // https://github.com/blueimp/jQuery-File-Upload/blob/9.5.6/js/jquery.iframe-transport.js#L102
      // Fix for IE endless progress bar activity bug
      // (happens on form submits to iframe targets):
      $('<iframe src="javascript:false;"></iframe>')
        .appendTo(self.form)
        .remove();
      var response;
      try {
        response = $(this).contents().find("body").html();
      } catch (e) {
        response = "cross-domain";
      }
      $(this).remove();
      if (!response) {
        if (self.settings.error) {
          self.settings.error(self.input.val());
        }
      } else {
        if (self.settings.success) {
          self.settings.success(response);
        }
      }
    });
    self.form.submit();
  }
  return this;
};

Uploader.prototype.refreshInput = function() {
  //replace the input element, or the same file can not to be uploaded
  var newInput = this.input.clone();
  this.input.before(newInput);
  this.input.off('change');
  this.input.remove();
  this.input = newInput;
  this.bindInput();
};

// handle change event
// when value in file input changed
Uploader.prototype.change = function(callback) {
  if (!callback) {
    return this;
  }
  this.settings.change = callback;
  return this;
};

// handle when upload success
Uploader.prototype.success = function(callback) {
  var me = this;
  this.settings.success = function(response) {
    me.refreshInput();
    if (callback) {
      callback(response);
    }
  };

  return this;
};

// handle when upload success
Uploader.prototype.error = function(callback) {
  var me = this;
  this.settings.error = function(response) {
    if (callback) {
      me.refreshInput();
      callback(response);
    }
  };
  return this;
};

// enable
Uploader.prototype.enable = function(){
  this.input.prop('disabled', false);
  this.input.css('cursor', 'pointer');
};

// disable
Uploader.prototype.disable = function(){
  this.input.prop('disabled', true);
  this.input.css('cursor', 'not-allowed');
};

// Helpers
// -------------

function isString(val) {
  return Object.prototype.toString.call(val) === '[object String]';
}

function createInputs(data) {
  if (!data) return [];

  var inputs = [], i;
  for (var name in data) {
    i = document.createElement('input');
    i.type = 'hidden';
    i.name = name;
    i.value = data[name];
    inputs.push(i);
  }
  return inputs;
}

function parse(str) {
  if (!str) return {};
  var ret = {};

  var pairs = str.split('&');
  var unescape = function(s) {
    return decodeURIComponent(s.replace(/\+/g, ' '));
  };

  for (var i = 0; i < pairs.length; i++) {
    var pair = pairs[i].split('=');
    var key = unescape(pair[0]);
    var val = unescape(pair[1]);
    ret[key] = val;
  }

  return ret;
}

function findzIndex($node) {
  var parents = $node.parentsUntil('body');
  var zIndex = 0;
  for (var i = 0; i < parents.length; i++) {
    var item = parents.eq(i);
    if (item.css('position') !== 'static') {
      zIndex = parseInt(item.css('zIndex'), 10) || zIndex;
    }
  }
  return zIndex;
}

function newIframe() {
  var iframeName = 'iframe-uploader-' + iframeCount;
  var iframe = $('<iframe name="' + iframeName + '" />').hide();
  iframeCount += 1;
  return iframe;
}

function MultipleUploader(options) {
  if (!(this instanceof MultipleUploader)) {
    return new MultipleUploader(options);
  }

  if (isString(options)) {
    options = {trigger: options};
  }
  var $trigger = $(options.trigger);

  var uploaders = [];
  $trigger.each(function(i, item) {
    options.trigger = item;
    uploaders.push(new Uploader(options));
  });
  this._uploaders = uploaders;
}
MultipleUploader.prototype.submit = function() {
  $.each(this._uploaders, function(i, item) {
    item.submit();
  });
  return this;
};
MultipleUploader.prototype.change = function(callback) {
  $.each(this._uploaders, function(i, item) {
    item.change(callback);
  });
  return this;
};
MultipleUploader.prototype.success = function(callback) {
  $.each(this._uploaders, function(i, item) {
    item.success(callback);
  });
  return this;
};
MultipleUploader.prototype.error = function(callback) {
  $.each(this._uploaders, function(i, item) {
    item.error(callback);
  });
  return this;
};
MultipleUploader.prototype.enable = function (){
  $.each(this._uploaders, function (i, item){
    item.enable();
  });
  return this;
};
MultipleUploader.prototype.disable = function (){
  $.each(this._uploaders, function (i, item){
    item.disable();
  });
  return this;
};
MultipleUploader.Uploader = Uploader;