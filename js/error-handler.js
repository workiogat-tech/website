/**
 * Centralized Error Handling System
 * Provides robust error handling, dependency checking, and user-friendly error messages
 */

(function() {
    'use strict';

    // Prevent multiple initializations
    if (window.ErrorHandler) {
        return;
    }

    const ErrorHandler = {
        // Error types that should be suppressed from user view
        TECHNICAL_ERROR_PATTERNS: [
            'fatal error',
            'parse error',
            'unexpected token',
            '<?php',
            'syntax error',
            'uncaught',
            'undefined',
            'null reference',
            'identifier.*has already been declared',
            'cannot read property',
            'is not defined',
            'is not a function'
        ],

        // User-friendly error messages
        USER_MESSAGES: {
            'network': 'Unable to connect. Please check your internet connection and try again.',
            'server': 'The server is temporarily unavailable. Your changes have been saved locally.',
            'storage': 'Unable to save data. Please try again or clear your browser cache.',
            'media': 'Unable to load media library. Please refresh the page.',
            'dependency': 'Some features are not available. Please refresh the page.',
            'generic': 'Something went wrong. Please try again or contact support if the problem persists.'
        },

        /**
         * Check if an error is technical (should be suppressed from user view)
         */
        isTechnicalError(error) {
            const errorMsg = (error?.message || error?.toString() || '').toLowerCase();
            return this.TECHNICAL_ERROR_PATTERNS.some(pattern => {
                try {
                    return new RegExp(pattern, 'i').test(errorMsg);
                } catch (e) {
                    return errorMsg.includes(pattern);
                }
            });
        },

        /**
         * Get user-friendly error message
         */
        getUserMessage(error, context = 'generic') {
            // Check if it's a technical error
            if (this.isTechnicalError(error)) {
                return this.USER_MESSAGES[context] || this.USER_MESSAGES.generic;
            }

            // Check for specific error types
            if (error?.message) {
                const msg = error.message.toLowerCase();
                if (msg.includes('network') || msg.includes('fetch') || msg.includes('connection')) {
                    return this.USER_MESSAGES.network;
                }
                if (msg.includes('server') || msg.includes('500') || msg.includes('php')) {
                    return this.USER_MESSAGES.server;
                }
                if (msg.includes('storage') || msg.includes('localstorage') || msg.includes('quota')) {
                    return this.USER_MESSAGES.storage;
                }
            }

            return this.USER_MESSAGES[context] || this.USER_MESSAGES.generic;
        },

        /**
         * Handle and log errors
         */
        handle(error, context = 'generic', showToUser = true) {
            const isTechnical = this.isTechnicalError(error);
            
            // Always log technical details to console for debugging
            if (isTechnical) {
                console.error(`[${context}] Technical error suppressed from user view:`, error);
            } else {
                console.error(`[${context}] Error:`, error);
            }

            // Show user-friendly message if requested
            if (showToUser && typeof window.showToast === 'function') {
                const userMessage = this.getUserMessage(error, context);
                window.showToast(userMessage, 'error');
            }

            return {
                error: error,
                isTechnical: isTechnical,
                userMessage: this.getUserMessage(error, context),
                context: context
            };
        },

        /**
         * Wrap async function with error handling
         */
        async wrapAsync(fn, context = 'operation', showToUser = true) {
            try {
                return await fn();
            } catch (error) {
                return this.handle(error, context, showToUser);
            }
        },

        /**
         * Check if a dependency is available
         */
        checkDependency(name, timeout = 5000) {
            return new Promise((resolve, reject) => {
                const startTime = Date.now();
                
                const check = () => {
                    if (window[name] !== undefined) {
                        resolve(true);
                        return;
                    }
                    
                    if (Date.now() - startTime > timeout) {
                        reject(new Error(`Dependency '${name}' not available after ${timeout}ms`));
                        return;
                    }
                    
                    setTimeout(check, 100);
                };
                
                check();
            });
        },

        /**
         * Ensure dependencies are loaded before executing a function
         */
        async ensureDependencies(dependencies, fn, context = 'operation') {
            try {
                // Check all dependencies
                await Promise.all(
                    dependencies.map(dep => this.checkDependency(dep))
                );
                
                // Execute function
                return await fn();
            } catch (error) {
                return this.handle(error, `dependency:${context}`, true);
            }
        },

        /**
         * Safe JSON parse with error handling
         */
        safeJsonParse(text, defaultValue = null) {
            try {
                // Check for PHP code or HTML error pages
                if (typeof text !== 'string') {
                    return defaultValue;
                }
                
                const trimmed = text.trim();
                if (trimmed.startsWith('<?php') || 
                    trimmed.startsWith('<!DOCTYPE') || 
                    trimmed.startsWith('<!') ||
                    trimmed.includes('Fatal error') ||
                    trimmed.includes('Parse error')) {
                    console.warn('Received non-JSON response (likely PHP/HTML)');
                    return defaultValue;
                }
                
                return JSON.parse(text);
            } catch (error) {
                console.error('JSON parse error:', error);
                return defaultValue;
            }
        },

        /**
         * Safe fetch with error handling and timeout
         */
        async safeFetch(url, options = {}, timeout = 10000) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);
                
                const response = await fetch(url, {
                    ...options,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                const text = await response.text();
                
                // Check if response is valid JSON
                if (response.headers.get('content-type')?.includes('application/json')) {
                    const data = this.safeJsonParse(text);
                    return { success: true, data: data, response: response };
                }
                
                // Check for PHP/HTML errors
                if (text.trim().startsWith('<?php') || 
                    text.trim().startsWith('<!DOCTYPE') ||
                    text.includes('Fatal error') ||
                    text.includes('Parse error')) {
                    return { 
                        success: false, 
                        error: 'Server returned non-JSON response',
                        response: response 
                    };
                }
                
                return { success: true, data: text, response: response };
            } catch (error) {
                if (error.name === 'AbortError') {
                    return { success: false, error: 'Request timeout' };
                }
                return { success: false, error: error.message || 'Network error' };
            }
        }
    };

    // Make it globally available
    window.ErrorHandler = ErrorHandler;

    // Set up global error handlers
    window.addEventListener('error', function(event) {
        const handled = ErrorHandler.handle(event.error || event.message, 'global', false);
        // Prevent default error display for technical errors
        if (handled.isTechnical) {
            event.preventDefault();
        }
    });

    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(event) {
        const handled = ErrorHandler.handle(event.reason, 'promise', false);
        // Prevent default error display for technical errors
        if (handled.isTechnical) {
            event.preventDefault();
        }
    });

})();
