(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

function getButtonText(button) {
  return button.innerHTML ? button.innerHTML : button.value;
}

function setButtonText(button, text) {
  button.innerHTML ? button.innerHTML = text : button.value = text;
}

function Loader(formElement) {
  this.form = formElement;
  this.button = formElement.querySelector('input[type="submit"], button[type="submit"]');
  this.loadingInterval = 0;
  this.character = "\xB7";

  if (this.button) {
    this.originalButton = this.button.cloneNode(true);
  }
}

Loader.prototype.setCharacter = function (c) {
  this.character = c;
};

Loader.prototype.start = function () {
  if (this.button) {
    // loading text
    var loadingText = this.button.getAttribute('data-loading-text');

    if (loadingText) {
      setButtonText(this.button, loadingText);
      return;
    } // Show AJAX loader


    var styles = window.getComputedStyle(this.button);
    this.button.style.width = styles.width;
    setButtonText(this.button, this.character);
    this.loadingInterval = window.setInterval(this.tick.bind(this), 500);
  } else {
    this.form.style.opacity = '0.5';
  }

  if (this.form.classList) {
    this.form.classList.add('mc4wp-loading');
  }
};

Loader.prototype.tick = function () {
  // count chars, start over at 5
  var text = getButtonText(this.button);
  var loadingChar = this.character;
  setButtonText(this.button, text.length >= 5 ? loadingChar : text + " " + loadingChar);
};

Loader.prototype.stop = function () {
  if (this.button) {
    this.button.style.width = this.originalButton.style.width;
    var text = getButtonText(this.originalButton);
    setButtonText(this.button, text);
    window.clearInterval(this.loadingInterval);
  } else {
    this.form.style.opacity = '';
  }

  this.form.className = this.form.className.replace('mc4wp-loading', '');
};

module.exports = Loader;

},{}],2:[function(require,module,exports){
(function (process){
'use strict';

var Loader = require('./_form-loader.js');

var config = window.mc4wp_ajax_vars;
var busy = false;

function submit(form) {
  var loader = new Loader(form.element);
  var loadingChar = config['loading_character'];

  if (loadingChar) {
    loader.setCharacter(loadingChar);
  }

  function start() {
    // Clear possible errors from previous submit
    form.setResponse('');
    loader.start();
    fire();
  }

  function fire() {
    // prepare request
    busy = true;
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
      var response = request; // are we done?

      if (response.readyState >= 4) {
        clean();

        if (response.status >= 200 && response.status < 400) {
          var data; // Request success! :-)

          try {
            data = JSON.parse(response.responseText);
          } catch (error) {
            console.log('Mailchimp for WordPress: failed to parse AJAX response.\n\nError: "' + error + '"'); // Not good..

            form.setResponse('<div class="mc4wp-alert mc4wp-error"><p>' + config['error_text'] + '</p></div>');
            return;
          }

          process(data);
        } else {
          // Error :(
          console.log(response.responseText);
        }
      }
    };

    request.open('POST', config['ajax_url'], true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.setRequestHeader('Accept', 'application/json');
    request.send(form.getSerializedData());
  }

  function process(response) {
    trigger('submitted', [form, null]);

    if (response.error) {
      form.setResponse(response.error.message);
      trigger('error', [form, response.error.errors]);
    } else {
      var data = form.getData(); // trigger events

      trigger('success', [form, data]);
      trigger(response.data.event, [form, data]); // for BC: always trigger "subscribed" event when firing "updated_subscriber" event
      // third boolean parameter indicates this was a BC event

      if (response.data.event === 'updated_subscriber') {
        trigger('subscribed', [form, data, true]);
      }

      if (response.data.hide_fields) {
        form.element.querySelector('.mc4wp-form-fields').style.display = 'none';
      } // show success message


      form.setResponse(response.data.message); // reset form element

      form.element.reset(); // maybe redirect to url

      if (response.data.redirect_to) {
        window.location.href = response.data.redirect_to;
      }
    }
  }

  function trigger(event, args) {
    window.mc4wp.forms.trigger(event, args);
    window.mc4wp.forms.trigger(args[0].id + "." + event, args);
  }

  function clean() {
    loader.stop();
    busy = false;
  } // let's do this!


  if (!busy) {
    start();
  }
} // failsafe against including script twice


if (!config['inited']) {
  window.mc4wp.forms.on('submit', function (form, event) {
    // does this form have AJAX enabled?
    if (form.element.getAttribute('class').indexOf('mc4wp-ajax') < 0) {
      return;
    } // blur active input field


    if (document.activeElement && document.activeElement.tagName === 'INPUT') {
      document.activeElement.blur();
    }

    try {
      submit(form);
    } catch (e) {
      console.error(e);
      return true;
    }

    event.returnValue = false;
    event.preventDefault();
    return false;
  });
  config['inited'] = true;
}

}).call(this,require('_process'))
},{"./_form-loader.js":1,"_process":3}],3:[function(require,module,exports){
"use strict";

// shim for using process in browser
var process = module.exports = {}; // cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
  throw new Error('setTimeout has not been defined');
}

function defaultClearTimeout() {
  throw new Error('clearTimeout has not been defined');
}

(function () {
  try {
    if (typeof setTimeout === 'function') {
      cachedSetTimeout = setTimeout;
    } else {
      cachedSetTimeout = defaultSetTimout;
    }
  } catch (e) {
    cachedSetTimeout = defaultSetTimout;
  }

  try {
    if (typeof clearTimeout === 'function') {
      cachedClearTimeout = clearTimeout;
    } else {
      cachedClearTimeout = defaultClearTimeout;
    }
  } catch (e) {
    cachedClearTimeout = defaultClearTimeout;
  }
})();

function runTimeout(fun) {
  if (cachedSetTimeout === setTimeout) {
    //normal enviroments in sane situations
    return setTimeout(fun, 0);
  } // if setTimeout wasn't available but was latter defined


  if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
    cachedSetTimeout = setTimeout;
    return setTimeout(fun, 0);
  }

  try {
    // when when somebody has screwed with setTimeout but no I.E. maddness
    return cachedSetTimeout(fun, 0);
  } catch (e) {
    try {
      // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
      return cachedSetTimeout.call(null, fun, 0);
    } catch (e) {
      // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
      return cachedSetTimeout.call(this, fun, 0);
    }
  }
}

function runClearTimeout(marker) {
  if (cachedClearTimeout === clearTimeout) {
    //normal enviroments in sane situations
    return clearTimeout(marker);
  } // if clearTimeout wasn't available but was latter defined


  if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
    cachedClearTimeout = clearTimeout;
    return clearTimeout(marker);
  }

  try {
    // when when somebody has screwed with setTimeout but no I.E. maddness
    return cachedClearTimeout(marker);
  } catch (e) {
    try {
      // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
      return cachedClearTimeout.call(null, marker);
    } catch (e) {
      // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
      // Some versions of I.E. have different rules for clearTimeout vs setTimeout
      return cachedClearTimeout.call(this, marker);
    }
  }
}

var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
  if (!draining || !currentQueue) {
    return;
  }

  draining = false;

  if (currentQueue.length) {
    queue = currentQueue.concat(queue);
  } else {
    queueIndex = -1;
  }

  if (queue.length) {
    drainQueue();
  }
}

function drainQueue() {
  if (draining) {
    return;
  }

  var timeout = runTimeout(cleanUpNextTick);
  draining = true;
  var len = queue.length;

  while (len) {
    currentQueue = queue;
    queue = [];

    while (++queueIndex < len) {
      if (currentQueue) {
        currentQueue[queueIndex].run();
      }
    }

    queueIndex = -1;
    len = queue.length;
  }

  currentQueue = null;
  draining = false;
  runClearTimeout(timeout);
}

process.nextTick = function (fun) {
  var args = new Array(arguments.length - 1);

  if (arguments.length > 1) {
    for (var i = 1; i < arguments.length; i++) {
      args[i - 1] = arguments[i];
    }
  }

  queue.push(new Item(fun, args));

  if (queue.length === 1 && !draining) {
    runTimeout(drainQueue);
  }
}; // v8 likes predictible objects


function Item(fun, array) {
  this.fun = fun;
  this.array = array;
}

Item.prototype.run = function () {
  this.fun.apply(null, this.array);
};

process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues

process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) {
  return [];
};

process.binding = function (name) {
  throw new Error('process.binding is not supported');
};

process.cwd = function () {
  return '/';
};

process.chdir = function (dir) {
  throw new Error('process.chdir is not supported');
};

process.umask = function () {
  return 0;
};

},{}]},{},[2]);
 })();