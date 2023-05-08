/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/ 		var executeModules = data[2];
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 		// add entry modules from loaded chunk to deferred list
/******/ 		deferredModules.push.apply(deferredModules, executeModules || []);
/******/
/******/ 		// run deferred modules when all chunks ready
/******/ 		return checkDeferredModules();
/******/ 	};
/******/ 	function checkDeferredModules() {
/******/ 		var result;
/******/ 		for(var i = 0; i < deferredModules.length; i++) {
/******/ 			var deferredModule = deferredModules[i];
/******/ 			var fulfilled = true;
/******/ 			for(var j = 1; j < deferredModule.length; j++) {
/******/ 				var depId = deferredModule[j];
/******/ 				if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 			}
/******/ 			if(fulfilled) {
/******/ 				deferredModules.splice(i--, 1);
/******/ 				result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 			}
/******/ 		}
/******/
/******/ 		return result;
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"admin-scripts": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// add entry module to deferred list
/******/ 	deferredModules.push(["./core/includes/assets/js/main.js","admin-vendor"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./core/includes/assets/js/custom/chat.js":
/*!************************************************!*\
  !*** ./core/includes/assets/js/custom/chat.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function($) {

Object.defineProperty(exports, "__esModule", {
    value: true
});

exports.default = function () {

    //Do nothing if it isn't a chat
    if (gpte.is_chat !== 'yes') {
        return;
    }

    "use strict";

    window.gpteData = {
        mainID: gpte.current_chat,
        currentID: gpte.agent_id > 0 ? gpte.agent_id : gpte.current_chat,
        automode: gpte.automode === 'yes' ? true : false,
        messages: [],
        agents: []
    };

    function formatText(text) {
        // Replace triple backticks with <pre> and <code> tags
        text = text.replace(/```([^`]+)```/g, '<pre><code>$1</code></pre>');

        return text;
    }

    function converHTMLSpecialChars(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function createOrderedList(items) {

        //var items = textList.split('- ');
        var html = '<ol>';

        $.each(items, function (index, item) {
            if (item) {
                html += '<li>' + item + '</li>';
            }
        });

        html += '</ol>';
        return html;
    }

    function getMessaeDetails(chatID, messageID) {

        if (messageID in window.gpteData.messages) {
            var html = '';
            var message = window.gpteData.messages[messageID];

            if ('thoughts' in message) {

                if ('reasoning' in message.thoughts) {
                    html += '<div class="message__reasoning">';
                    html += '<strong>Reasoning:</strong><br />';
                    html += '<p>' + message.thoughts.reasoning + '</p>';
                    html += '</div>';
                }

                if ('plan' in message.thoughts) {
                    html += '<div class="message__plan">';
                    html += '<strong>Plan:</strong><br />';
                    html += createOrderedList(message.thoughts.plan);
                    html += '<p></p>';
                    html += '</div>';
                }

                if ('criticism' in message.thoughts) {
                    html += '<div class="message__criticism">';
                    html += '<strong>Criticism:</strong><br />';
                    html += '<p>' + message.thoughts.criticism + '</p>';
                    html += '</div>';
                }
            }

            if ('command' in message) {

                html += '<div class="message__reasoning">';
                html += '<strong>Command:</strong><br />';

                if (message.command.name) {
                    if ('command_details' in message) {
                        html += '<p>' + message.command_details.name + ': ' + message.command.name + '</p>';
                    } else {
                        html += '<p>' + message.command.name + '</p>';
                    }

                    if (message.command.args) {
                        html += '<pre>' + converHTMLSpecialChars(JSON.stringify(message.command.args, null, 4)) + '</pre>';
                    } else {
                        html += '<pre>No Arguments given.</pre>';
                    }
                } else {
                    html += '<p>No command given.</p>';
                }

                html += '</div>';
            }

            if ('result' in message) {

                html += '<div class="message__reasoning">';
                html += '<strong>Result:</strong><br />';

                if (message.result) {
                    html += '<p>This is the result for the previous command.</p>';
                    html += '<pre>' + JSON.stringify(message.result, null, 4) + '</pre>';
                } else {
                    html += '<p>No result given.</p>';
                }

                html += '</div>';
            }

            $('#message-data').html(html);
        }
    }

    function fetchMessages(chatID) {
        var chat = $('#chat');
        var actionhtml = '';
        window.gpteData.messages = [];

        chat.empty(); // Clear existing chat messages

        $.ajax({
            type: "post",
            dataType: "json",
            url: gpte.ajax_url,
            timeout: 7000,
            data: {
                action: "gpte_fetch_messages",
                chat_id: chatID,
                gpte_nonce: gpte.ajax_nonce
            },
            success: function success(response) {

                if (response.success) {

                    $.each(response.data.messages, function (index, message) {

                        if (!('messages' in window.gpteData)) {
                            window.gpteData.messages = [];
                        }

                        window.gpteData.messages[index] = message;
                        console.log(message);
                        if ('command' in message && message.command && 'name' in message.command && message.command.name !== 'do_nothing' && index == response.data.messages.length - 1) {
                            actionhtml = '<div class="gpte-accept">Accept</div>';
                        }

                        if (message.role !== 'system') {
                            var messageClass = message.role === 'assistant' || message.role === 'system' ? 'chat__message chat__message--bot' : 'chat__message chat__message--user';
                            chat.append(formatText('<div class="' + messageClass + ' ' + message.role + '" data-message-id="' + index + '">' + message.content + actionhtml + '</div>'));
                        }
                    });

                    chat.scrollTop(chat[0].scrollHeight); // Scroll to the bottom
                }
            }
        });
    }

    function fetchAgents(chatID) {
        var agents = $('#agents-wrapper');

        agents.empty(); // Clear existing chat messages

        $.ajax({
            type: "post",
            dataType: "json",
            url: gpte.ajax_url,
            timeout: 7000,
            data: {
                action: "gpte_fetch_agents",
                chat_id: chatID,
                gpte_nonce: gpte.ajax_nonce
            },
            success: function success(response) {

                if (response.success) {

                    $.each(response.data.agents, function (index, agent) {

                        window.gpteData.agents[agent.ID] = agent;

                        agents.append(formatText('<div id="chat-btn-' + agent.ID + '" class="chats__button gpte-btn gpte-btn--sm gpte-btn--secondary w-100 agent" data-chat-id="' + agent.ID + '">' + agent.post_title + '</div>'));
                    });
                }

                $(".chats__button").removeClass("current");
                $("#chat-btn-" + window.gpteData.currentID).addClass("current");
            }
        });
    }

    function switchChat(chatID) {

        var chat = $('#message-data');

        chat.empty();
        window.gpteData.currentID = chatID;

        fetchMessages(chatID);

        $(".chats__button").removeClass("current");
        $("#chat-btn-" + chatID).addClass("current");

        $('#chat-input').focus();
    }

    function reloadMessageTree(chatID) {

        $.ajax({
            type: "post",
            dataType: "json",
            url: gpte.ajax_url,
            timeout: 7000,
            data: {
                action: "gpte_fetch_messages",
                chat_id: chatID,
                gpte_nonce: gpte.ajax_nonce
            },
            success: function success(response) {

                if (response.success) {
                    window.gpteData.messages = [];

                    $.each(response.data.messages, function (index, message) {

                        window.gpteData.messages[index] = message;
                    });
                }
            }
        });
    }

    $('#chatForm').submit(function (event) {
        event.preventDefault();

        var input = $('#chat-input');
        var chat = $('#chat');
        var userInput = input.val();
        var messageIndex = 0;

        if (!userInput) {
            return;
        }

        if (!('messages' in window.gpteData)) {
            window.gpteData.messages = [];
        }

        window.gpteData.messages.push(userInput);
        messageIndex = window.gpteData.messages.length - 1;

        $('.gpte-accept').hide();

        chat.append(formatText('<div class="chat__message chat__message--user" data-message-id="' + messageIndex + '">' + userInput + '</div>'));

        // Show the thinking animation
        setTimeout(function () {
            $('#thinking').show();
        }, 100);

        $.ajax({
            type: "post",
            dataType: "json",
            url: gpte.ajax_url,
            timeout: 70000,
            data: {
                action: "gpte_send_message",
                message: userInput,
                chat_id: window.gpteData.currentID,
                gpte_nonce: gpte.ajax_nonce
            },
            success: function success(response) {

                $('#thinking').hide();

                if (response.success) {

                    var role = '';
                    var actionhtml = '';

                    if ('data' in response && 'message' in response.data) {

                        window.gpteData.messages.push(response.data.message);
                        messageIndex = window.gpteData.messages.length - 1;

                        if ('role' in response.data.message) {
                            role = response.data.message.role;
                        }

                        if ('command' in response.data.message && response.data.message.command && 'name' in response.data.message.command && response.data.message.command.name !== 'do_nothing') {
                            actionhtml = '<div class="gpte-accept">Accept</div>';
                        }
                    }

                    chat.append(formatText('<div class="chat__message chat__message--bot ' + role + '" data-message-id="' + messageIndex + '">' + response.answer + actionhtml + '</div>'));
                    chat.scrollTop(chat[0].scrollHeight);
                    input.val('');

                    //Reload the agents and messages
                    fetchAgents(window.gpteData.mainID);
                    reloadMessageTree(window.gpteData.currentID);

                    //Maybe run it in Automode
                    if (window.gpteData.automode && 'data' in response && 'message' in response.data && 'command' in response.data.message && response.data.message.command) {
                        $('#chat-input').val('yes');
                        $('#chatForm').submit();
                    }
                }
            },
            error: function error() {
                // Hide the thinking animation in case of an error
                $('#thinking').hide();
            }
        });
    });

    $(document).on('click', '.chat__message--bot', function () {
        var $this = $(this);
        var messageID = $this.data('message-id');
        var htmlContent = getMessaeDetails(window.gpteData.currentID, messageID);

        $('#message-data').html(htmlContent);
    });

    $(document).on('click', '.gpte-accept', function () {
        $(this).hide();
        $('#chat-input').val('yes');
        $('#chatForm').submit();
    });

    $(document).on('click', '#automode', function () {
        var $this = $(this);
        var isAutomode = $this.is(":checked");

        window.gpteData.automode = isAutomode;
    });

    $(document).on('click', '.chats__button', function () {
        var $this = $(this);
        var buttonID = $this.data("chat-id");

        if (buttonID) {
            switchChat(buttonID);
        }
    });

    fetchMessages(window.gpteData.currentID);

    //Get agents if there are any
    fetchAgents(window.gpteData.mainID);

    // Set an interval to fetch messages every 5 seconds
    // setInterval(fetchMessages, 5000);

    $('#chat-input').focus();
};
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./core/includes/assets/js/main.js":
/*!*****************************************!*\
  !*** ./core/includes/assets/js/main.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(jQuery, $) {

var _tippy = __webpack_require__(/*! tippy.js */ "./node_modules/tippy.js/dist/tippy.esm.js");

var _tippy2 = _interopRequireDefault(_tippy);

__webpack_require__(/*! bootstrap/js/dist/modal */ "./node_modules/bootstrap/js/dist/modal.js");

__webpack_require__(/*! bootstrap/js/dist/tab */ "./node_modules/bootstrap/js/dist/tab.js");

__webpack_require__(/*! bootstrap/js/dist/scrollspy */ "./node_modules/bootstrap/js/dist/scrollspy.js");

__webpack_require__(/*! bootstrap/js/dist/collapse */ "./node_modules/bootstrap/js/dist/collapse.js");

__webpack_require__(/*! bootstrap/js/dist/dropdown */ "./node_modules/bootstrap/js/dist/dropdown.js");

__webpack_require__(/*! bootstrap/js/dist/alert */ "./node_modules/bootstrap/js/dist/alert.js");

__webpack_require__(/*! select2 */ "./node_modules/select2/dist/js/select2.js");

__webpack_require__(/*! simplebar */ "./node_modules/simplebar/dist/simplebar.esm.js");

__webpack_require__(/*! ./vendor/jsonviewer */ "./core/includes/assets/js/vendor/jsonviewer.js");

__webpack_require__(/*! ./vendor/jquery.matchHeight-min */ "./core/includes/assets/js/vendor/jquery.matchHeight-min.js");

var _insertParamToURL = __webpack_require__(/*! ./vendor/insertParamToURL */ "./core/includes/assets/js/vendor/insertParamToURL.js");

var _insertParamToURL2 = _interopRequireDefault(_insertParamToURL);

var _getUrlParam = __webpack_require__(/*! ./vendor/getUrlParam */ "./core/includes/assets/js/vendor/getUrlParam.js");

var _getUrlParam2 = _interopRequireDefault(_getUrlParam);

var _chat = __webpack_require__(/*! ./custom/chat */ "./core/includes/assets/js/custom/chat.js");

var _chat2 = _interopRequireDefault(_chat);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.$ = jQuery; /**
                    * ---------> assets/js/src/app.js
                    */

// Vendor Plugins

window.tippy = _tippy2.default;
window.insertParamToUrl = _insertParamToURL2.default;
window.getUrlParam = _getUrlParam2.default;

// Custom Imports


/**
 * Custom jQuery Code
 */
jQuery(document).ready(function ($) {

  // Initialize scripts from sub files jQuery ready.
  (0, _chat2.default)();

  // const flows = new Flows();
  // Flows.init();

  // Tippy
  (0, _tippy2.default)('[data-tippy-content]', {
    allowHTML: true,
    popperOptions: {
      strategy: 'fixed',
      modifiers: [{
        name: 'flip',
        options: {
          fallbackPlacements: ['bottom', 'right']
        }
      }, {
        name: 'preventOverflow',
        options: {
          altAxis: true,
          tether: false
        }
      }]
    }
  });

  // Copy to clipboard input
  $('.gpte-copy-wrapper').each(function (i, el) {
    var $thisEl = $(el);

    $thisEl.find('input').on('click', function (e) {
      $(this).trigger('select');
      document.execCommand('copy');
    });

    (0, _tippy2.default)($thisEl[0], {
      arrow: true,
      animation: 'fade',
      trigger: 'click',
      content: $thisEl.data('gpte-tippy-content') || 'copied!',
      offset: [0, 15],
      onShow: function onShow(instance) {
        setTimeout(function () {
          instance.hide();
        }, 1500);
      }
    });
  });
});

// A handler to send a jQuery confirm on a given class and prevent the default
$(document).on('click', '.gpte-confirm', function (e) {

  if (!confirm('Are you sure you want to do this?')) {
    e.preventDefault();
  }
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery"), __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "./core/includes/assets/js/vendor/getUrlParam.js":
/*!*******************************************************!*\
  !*** ./core/includes/assets/js/vendor/getUrlParam.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
/**
 * Get URL Parameter
 *
 * Usage:
 * var searchText = getUrlParam( 'search' );
 *
 * @param  {string} sParam    parameter name, e.g., "search"
 * @param  {string} link      (optional) if you want to get the parameter from
 *                            a different URL then current page url.
 * @return {multiple}         returns the 'value' of parameter
 */
var getUrlParam = function getUrlParam(sParam, link) {
    var sPageURL = link ? link : decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

exports.default = getUrlParam;

/***/ }),

/***/ "./core/includes/assets/js/vendor/insertParamToURL.js":
/*!************************************************************!*\
  !*** ./core/includes/assets/js/vendor/insertParamToURL.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
/**
 * Insert Parameter in URL
 *
 * Dynamically insert or update a parameter in the URL.
 *
 * @param {string} key parameter name
 * @param {string} value parameter value
 * @param {string} base set a custom URL base.
 */
var insertParamToUrl = function insertParamToUrl(key, value) {
  var base = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';

  // Check if both key exists.
  var kvp = document.location.search.substr(1).split('&');

  if (key) {
    key = encodeURI(key);
    value = value ? encodeURI(value) : '';

    var i = kvp.length;var x;
    while (i--) {
      x = kvp[i].split('=');

      if (x[0] == key) {
        x[1] = value;
        kvp[i] = x.join('=');
        break;
      }
    }

    if (i < 0) {
      kvp[kvp.length] = [key, value].join('=');
    }
  }

  // this will reload the page, it's likely better to store this until finished
  // document.location.search = kvp.join( '&' );
  var urlBase = window.location.origin + (base ? base : window.location.pathname);
  var newUrl = urlBase + '?' + kvp.join('&') + window.location.hash;
  window.history.replaceState(null, null, newUrl);
};

exports.default = insertParamToUrl;

/***/ }),

/***/ "./core/includes/assets/js/vendor/jquery.matchHeight-min.js":
/*!******************************************************************!*\
  !*** ./core/includes/assets/js/vendor/jquery.matchHeight-min.js ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/*
* jquery-match-height 0.7.2 by @liabru
* http://brm.io/jquery-match-height/
* License MIT
*/
!function (t) {
  "use strict";
   true ? !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ "jquery")], __WEBPACK_AMD_DEFINE_FACTORY__ = (t),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)) : undefined;
}(function (t) {
  var e = -1,
      o = -1,
      n = function n(t) {
    return parseFloat(t) || 0;
  },
      a = function a(e) {
    var o = 1,
        a = t(e),
        i = null,
        r = [];return a.each(function () {
      var e = t(this),
          a = e.offset().top - n(e.css("margin-top")),
          s = r.length > 0 ? r[r.length - 1] : null;null === s ? r.push(e) : Math.floor(Math.abs(i - a)) <= o ? r[r.length - 1] = s.add(e) : r.push(e), i = a;
    }), r;
  },
      i = function i(e) {
    var o = {
      byRow: !0, property: "height", target: null, remove: !1 };return "object" == (typeof e === "undefined" ? "undefined" : _typeof(e)) ? t.extend(o, e) : ("boolean" == typeof e ? o.byRow = e : "remove" === e && (o.remove = !0), o);
  },
      r = t.fn.matchHeight = function (e) {
    var o = i(e);if (o.remove) {
      var n = this;return this.css(o.property, ""), t.each(r._groups, function (t, e) {
        e.elements = e.elements.not(n);
      }), this;
    }return this.length <= 1 && !o.target ? this : (r._groups.push({ elements: this, options: o }), r._apply(this, o), this);
  };r.version = "0.7.2", r._groups = [], r._throttle = 80, r._maintainScroll = !1, r._beforeUpdate = null, r._afterUpdate = null, r._rows = a, r._parse = n, r._parseOptions = i, r._apply = function (e, o) {
    var s = i(o),
        h = t(e),
        l = [h],
        c = t(window).scrollTop(),
        p = t("html").outerHeight(!0),
        u = h.parents().filter(":hidden");return u.each(function () {
      var e = t(this);e.data("style-cache", e.attr("style"));
    }), u.css("display", "block"), s.byRow && !s.target && (h.each(function () {
      var e = t(this),
          o = e.css("display");"inline-block" !== o && "flex" !== o && "inline-flex" !== o && (o = "block"), e.data("style-cache", e.attr("style")), e.css({ display: o, "padding-top": "0",
        "padding-bottom": "0", "margin-top": "0", "margin-bottom": "0", "border-top-width": "0", "border-bottom-width": "0", height: "100px", overflow: "hidden" });
    }), l = a(h), h.each(function () {
      var e = t(this);e.attr("style", e.data("style-cache") || "");
    })), t.each(l, function (e, o) {
      var a = t(o),
          i = 0;if (s.target) i = s.target.outerHeight(!1);else {
        if (s.byRow && a.length <= 1) return void a.css(s.property, "");a.each(function () {
          var e = t(this),
              o = e.attr("style"),
              n = e.css("display");"inline-block" !== n && "flex" !== n && "inline-flex" !== n && (n = "block");var a = {
            display: n };a[s.property] = "", e.css(a), e.outerHeight(!1) > i && (i = e.outerHeight(!1)), o ? e.attr("style", o) : e.css("display", "");
        });
      }a.each(function () {
        var e = t(this),
            o = 0;s.target && e.is(s.target) || ("border-box" !== e.css("box-sizing") && (o += n(e.css("border-top-width")) + n(e.css("border-bottom-width")), o += n(e.css("padding-top")) + n(e.css("padding-bottom"))), e.css(s.property, i - o + "px"));
      });
    }), u.each(function () {
      var e = t(this);e.attr("style", e.data("style-cache") || null);
    }), r._maintainScroll && t(window).scrollTop(c / p * t("html").outerHeight(!0)), this;
  }, r._applyDataApi = function () {
    var e = {};t("[data-match-height], [data-mh]").each(function () {
      var o = t(this),
          n = o.attr("data-mh") || o.attr("data-match-height");n in e ? e[n] = e[n].add(o) : e[n] = o;
    }), t.each(e, function () {
      this.matchHeight(!0);
    });
  };var s = function s(e) {
    r._beforeUpdate && r._beforeUpdate(e, r._groups), t.each(r._groups, function () {
      r._apply(this.elements, this.options);
    }), r._afterUpdate && r._afterUpdate(e, r._groups);
  };r._update = function (n, a) {
    if (a && "resize" === a.type) {
      var i = t(window).width();if (i === e) return;e = i;
    }n ? o === -1 && (o = setTimeout(function () {
      s(a), o = -1;
    }, r._throttle)) : s(a);
  }, t(r._applyDataApi);var h = t.fn.on ? "on" : "bind";t(window)[h]("load", function (t) {
    r._update(!1, t);
  }), t(window)[h]("resize orientationchange", function (t) {
    r._update(!0, t);
  });
});

/***/ }),

/***/ "./core/includes/assets/js/vendor/jsonviewer.js":
/*!******************************************************!*\
  !*** ./core/includes/assets/js/vendor/jsonviewer.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(jQuery) {

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/**
 * jQuery json-viewer
 * @author: Kevin Olson <acidjazz@gmail.com>
 */
(function ($) {

  /**
   * Check if arg is either an array with at least 1 element, or a dict with at least 1 key
   * @return boolean
   */
  function isCollapsable(arg) {
    return arg instanceof Object && Object.keys(arg).length > 0;
  }

  /**
   * Check if a string represents a valid url
   * @return boolean
   */
  function isUrl(string) {
    var regexp = /^(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    return regexp.test(string);
  }

  /**
   * Transform a json object into html representation
   * @return string
   */
  function json2html(json, options) {
    var html = '';
    if (typeof json === 'string') {
      // Escape tags
      json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      if (isUrl(json)) html += '<a href="' + json + '" class="json-string">' + json + '</a>';else html += '<span class="json-string">"' + json + '"</span>';
    } else if (typeof json === 'number') {
      html += '<span class="json-literal">' + json + '</span>';
    } else if (typeof json === 'boolean') {
      html += '<span class="json-literal">' + json + '</span>';
    } else if (json === null) {
      html += '<span class="json-literal">null</span>';
    } else if (json instanceof Array) {
      if (json.length > 0) {
        html += '[<ol class="json-array">';
        for (var i = 0; i < json.length; ++i) {
          html += '<li>';
          // Add toggle button if item is collapsable
          if (isCollapsable(json[i])) {
            html += '<a href class="json-toggle"></a>';
          }
          html += json2html(json[i], options);
          // Add comma if item is not last
          if (i < json.length - 1) {
            html += ',';
          }
          html += '</li>';
        }
        html += '</ol>]';
      } else {
        html += '[]';
      }
    } else if ((typeof json === 'undefined' ? 'undefined' : _typeof(json)) === 'object') {
      var key_count = Object.keys(json).length;
      if (key_count > 0) {
        html += '{<ul class="json-dict">';
        for (var key in json) {
          if (json.hasOwnProperty(key)) {
            html += '<li>';
            var keyRepr = options.withQuotes ? '<span class="json-string">"' + key + '"</span>' : key;
            // Add toggle button if item is collapsable
            if (isCollapsable(json[key])) {
              html += '<a href class="json-toggle">' + keyRepr + '</a>';
            } else {
              html += keyRepr;
            }
            html += ': ' + json2html(json[key], options);
            // Add comma if item is not last
            if (--key_count > 0) html += ',';
            html += '</li>';
          }
        }
        html += '</ul>}';
      } else {
        html += '{}';
      }
    }
    return html;
  }

  /**
   * jQuery plugin method
   * @param json: a javascript object
   * @param options: an optional options hash
   */
  $.fn.jsonBrowse = function (json, options) {
    options = options || {};

    // jQuery chaining
    return this.each(function () {

      // Transform to HTML
      var html = json2html(json, options);
      if (isCollapsable(json)) html = '<a href class="json-toggle"></a>' + html;

      // Insert HTML in target DOM element
      $(this).html(html);

      // Bind click on toggle buttons
      $(this).off('click');
      $(this).on('click', 'a.json-toggle', function () {
        var target = $(this).toggleClass('collapsed').siblings('ul.json-dict, ol.json-array');
        target.toggle();
        if (target.is(':visible')) {
          target.siblings('.json-placeholder').remove();
        } else {
          var count = target.children('li').length;
          var placeholder = count + (count > 1 ? ' items' : ' item');
          target.after('<a href class="json-placeholder">' + placeholder + '</a>');
        }
        return false;
      });

      // Simulate click on toggle button when placeholder is clicked
      $(this).on('click', 'a.json-placeholder', function () {
        $(this).siblings('a.json-toggle').click();
        return false;
      });

      if (options.collapsed == true) {
        // Trigger click to collapse all nodes
        $(this).find('a.json-toggle').click();
      }
    });
  };
})(jQuery);
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "jquery")))

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ })

/******/ });
//# sourceMappingURL=admin-scripts.js.map