/**
 * Festive Animations Script
 * Loads settings from database and starts animations immediately when enabled
 */
(function() {
    'use strict';
    
    let animationContainer = null;
    let animationInterval = null;
    let currentSettings = null;
    
    // Load settings from API
    async function loadSettings() {
        try {
            const response = await fetch('api/content/festive-animations.php');
            const text = await response.text();
            
            // Check if we got PHP code instead of JSON
            if (text.trim().startsWith('<?php') || text.trim().startsWith('<!')) {
                // PHP server not available - animations disabled
                return;
            }
            
            const data = JSON.parse(text);
            
            if (data && !data.error) {
                currentSettings = {
                    enabled: data.enabled == 1 || data.enabled === true || data.enabled === '1',
                    opacity: parseFloat(data.opacity) || 0.9,
                    speed: parseFloat(data.speed) || 0.6,
                    size: parseFloat(data.size) || 2.0,
                    intensity: parseFloat(data.intensity) || 1.0,
                    colors: (() => {
                        if (typeof data.colors === 'string') {
                            try {
                                return JSON.parse(data.colors);
                            } catch (e) {
                                return ['#FFFFFF'];
                            }
                        } else if (Array.isArray(data.colors)) {
                            return data.colors;
                        }
                        return ['#FFFFFF'];
                    })()
                };
                
                // Start animations if enabled
                if (currentSettings.enabled) {
                    startAnimations();
                } else {
                    stopAnimations();
                }
            }
        } catch (error) {
            // Silently fail - animations are optional
            console.warn('Festive animations not available');
        }
    }
    
    // Create animation container
    function createContainer() {
        if (animationContainer) {
            return;
        }
        
        animationContainer = document.createElement('div');
        animationContainer.id = 'festive-animations-container';
        animationContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
        `;
        document.body.appendChild(animationContainer);
    }
    
    // Start animations
    function startAnimations() {
        if (!currentSettings || !currentSettings.enabled) {
            return;
        }
        
        createContainer();
        clearInterval(animationInterval);
        
        const settings = currentSettings;
        const colors = settings.colors && settings.colors.length > 0 ? settings.colors : ['#FFFFFF'];
        
        // Calculate number of snowflakes based on intensity and screen size
        const baseDensity = (window.innerWidth * window.innerHeight) / 15000;
        const count = Math.max(1, Math.floor(baseDensity * settings.intensity));
        
        // Create initial snowflakes
        for (let i = 0; i < count; i++) {
            createSnowflake(settings, colors);
        }
        
        // Create new snowflakes periodically
        animationInterval = setInterval(() => {
            if (animationContainer && currentSettings && currentSettings.enabled) {
                createSnowflake(settings, colors);
            }
        }, 1000 / settings.speed);
    }
    
    // Create a single snowflake
    function createSnowflake(settings, colors) {
        if (!animationContainer) {
            return;
        }
        
        const snowflake = document.createElement('div');
        snowflake.innerHTML = '❄';
        
        const color = colors[Math.floor(Math.random() * colors.length)];
        const baseSize = (Math.random() * 8 + 4) * settings.size;
        const left = Math.random() * 100;
        const duration = (Math.random() * 3 + 2) * (2 / settings.speed);
        const delay = Math.random() * 5;
        
        snowflake.style.cssText = `
            position: absolute;
            left: ${left}%;
            top: -50px;
            font-size: ${baseSize}px;
            color: ${color};
            opacity: ${settings.opacity};
            animation: festive-snowfall ${duration}s linear infinite;
            animation-delay: ${delay}s;
            user-select: none;
            pointer-events: none;
        `;
        
        animationContainer.appendChild(snowflake);
        
        // Remove snowflake after animation completes
        setTimeout(() => {
            if (snowflake.parentNode) {
                snowflake.parentNode.removeChild(snowflake);
            }
        }, (duration + delay) * 1000);
    }
    
    // Stop animations
    function stopAnimations() {
        clearInterval(animationInterval);
        if (animationContainer) {
            animationContainer.innerHTML = '';
        }
    }
    
    // Add CSS animation
    if (!document.getElementById('festive-animations-style')) {
        const style = document.createElement('style');
        style.id = 'festive-animations-style';
        style.textContent = `
            @keyframes festive-snowfall {
                0% {
                    transform: translateY(-100vh) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0.3;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Load settings when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadSettings);
    } else {
        loadSettings();
    }
    
    // Reload settings when page becomes visible (in case settings changed in another tab)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            loadSettings();
        }
    });
    
    // Reload on window focus (in case settings changed)
    window.addEventListener('focus', loadSettings);
    
    // Listen for broadcast messages from admin panel
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('festive-animations');
        channel.addEventListener('message', function(event) {
            if (event.data.action === 'reload') {
                loadSettings();
            }
        });
    }
    
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (currentSettings && currentSettings.enabled) {
                stopAnimations();
                startAnimations();
            }
        }, 250);
    });
})();
