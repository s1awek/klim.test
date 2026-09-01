/**
 * AI Bridge Logger
 *
 * Provides environment-gated logging for JavaScript.
 * Mirrors the PHP WPAI_Bridge_Logger class behavior.
 *
 * Logging is controlled by wpai_bridge_config.debug (set via wp_localize_script).
 * When disabled, all log methods become no-ops with zero performance impact.
 */
(function(window) {
    'use strict';

    // Check if debug is enabled via localized config
    // This is set by PHP via wp_localize_script
    var isEnabled = function() {
        return window.wpai_bridge_config && window.wpai_bridge_config.debug === true;
    };

    // Performance timers storage
    var timers = {};

    // Create the logger object
    var WPAILogger = {
        /**
         * Check if logging is enabled
         * @returns {boolean}
         */
        isEnabled: isEnabled,

        /**
         * Log a debug message
         * @param {string} message
         * @param {*} [data] Optional data to log
         */
        debug: function(message, data) {
            if (!isEnabled()) return;
            if (data !== undefined) {
                console.log('[AI Bridge][DEBUG]', message, data);
            } else {
                console.log('[AI Bridge][DEBUG]', message);
            }
        },

        /**
         * Log an info message
         * @param {string} message
         * @param {*} [data] Optional data to log
         */
        info: function(message, data) {
            if (!isEnabled()) return;
            if (data !== undefined) {
                console.info('[AI Bridge][INFO]', message, data);
            } else {
                console.info('[AI Bridge][INFO]', message);
            }
        },

        /**
         * Log a warning message
         * @param {string} message
         * @param {*} [data] Optional data to log
         */
        warn: function(message, data) {
            if (!isEnabled()) return;
            if (data !== undefined) {
                console.warn('[AI Bridge][WARN]', message, data);
            } else {
                console.warn('[AI Bridge][WARN]', message);
            }
        },

        /**
         * Log an error message (always logs, regardless of debug setting)
         * @param {string} message
         * @param {*} [data] Optional data to log
         */
        error: function(message, data) {
            // Errors always log
            if (data !== undefined) {
                console.error('[AI Bridge][ERROR]', message, data);
            } else {
                console.error('[AI Bridge][ERROR]', message);
            }
        },

        /**
         * Start a performance timer
         * @param {string} name Unique name for this timer
         */
        perfStart: function(name) {
            if (!isEnabled()) return;
            timers[name] = {
                startTime: performance.now(),
                startMem: window.performance && window.performance.memory
                    ? window.performance.memory.usedJSHeapSize
                    : 0
            };
        },

        /**
         * End a performance timer and log results
         * @param {string} name Timer name (must match perfStart)
         * @param {Object} [context] Optional additional context to log
         * @returns {Object|null} Performance data or null
         */
        perfEnd: function(name, context) {
            if (!isEnabled()) return null;

            if (!timers[name]) {
                this.warn('Performance timer "' + name + '" was never started.');
                return null;
            }

            var timer = timers[name];
            delete timers[name];

            var elapsed = (performance.now() - timer.startTime).toFixed(2);
            var memUsed = 0;
            var peakMem = 0;

            if (window.performance && window.performance.memory) {
                peakMem = (window.performance.memory.usedJSHeapSize / 1024 / 1024).toFixed(2);
                memUsed = ((window.performance.memory.usedJSHeapSize - timer.startMem) / 1024 / 1024).toFixed(2);
            }

            var perfData = {
                elapsed_ms: parseFloat(elapsed),
                mem_delta_mb: parseFloat(memUsed),
                peak_mem_mb: parseFloat(peakMem)
            };

            var message = '[PERF] ' + name + ': ' + elapsed + 'ms';
            if (peakMem > 0) {
                message += ' | Memory: +' + memUsed + 'MB (peak: ' + peakMem + 'MB)';
            }

            if (context) {
                console.log('[AI Bridge]', message, context);
            } else {
                console.log('[AI Bridge]', message);
            }

            return perfData;
        },

        /**
         * Create a console group (collapsed by default)
         * @param {string} label Group label
         */
        group: function(label) {
            if (!isEnabled()) return;
            console.groupCollapsed('[AI Bridge] ' + label);
        },

        /**
         * End a console group
         */
        groupEnd: function() {
            if (!isEnabled()) return;
            console.groupEnd();
        },

        /**
         * Log a table (useful for arrays/objects)
         * @param {Array|Object} data
         */
        table: function(data) {
            if (!isEnabled()) return;
            console.table(data);
        }
    };

    // Expose globally
    window.WPAILogger = WPAILogger;

})(window);
