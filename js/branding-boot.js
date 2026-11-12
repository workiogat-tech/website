/**
 * Apply saved branding (favicon, school name, logo) as early as possible.
 * Uses localStorage cache first so real data shows immediately — no hardcoded school names.
 */
(function () {
    var CACHE_KEY = 'sjs_branding_chrome';

    function normalizeUrl(url) {
        if (!url) return '';
        var value = String(url).trim();
        if (!value || value.indexOf('data:') === 0) return value;
        if (/^https?:\/\//i.test(value) || value.charAt(0) === '/') return value;
        return '/' + value.replace(/^\.?\//, '');
    }

    window.setSiteFavicon = function (url) {
        var clean = normalizeUrl(url);
        if (!clean || clean.indexOf('data:') === 0) return;
        var path = clean.split('?')[0].split('#')[0];
        var ext = (path.split('.').pop() || '').toLowerCase();
        var type = ext === 'svg' ? 'image/svg+xml'
            : (ext === 'ico' ? 'image/x-icon'
            : (ext === 'jpg' || ext === 'jpeg' ? 'image/jpeg'
            : (ext === 'webp' ? 'image/webp'
            : (ext === 'gif' ? 'image/gif' : 'image/png'))));
        var href = clean + (clean.indexOf('?') >= 0 ? '&' : '?') + 'v=' + Date.now();
        document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"], link[rel="apple-touch-icon"]').forEach(function (el) {
            if (el.parentNode) el.parentNode.removeChild(el);
        });
        [['icon', type], ['shortcut icon', type], ['apple-touch-icon', '']].forEach(function (pair) {
            var link = document.createElement('link');
            link.rel = pair[0];
            if (pair[1]) link.type = pair[1];
            link.href = href;
            document.head.appendChild(link);
        });
    };

    window.saveBrandingChrome = function (branding) {
        if (!branding || typeof branding !== 'object') return;
        try {
            var payload = {
                school_name: branding.school_name || '',
                favicon: branding.favicon || '',
                logo: branding.logo || '',
                primary_color: branding.primary_color || '',
                secondary_color: branding.secondary_color || '',
                font_family: branding.font_family || ''
            };
            localStorage.setItem(CACHE_KEY, JSON.stringify(payload));
        } catch (e) {}
    };

    function pageLabelFromTitle() {
        var title = (document.title || '').trim();
        if (!title) return '';
        return title
            .replace(/\s*[-–|]\s*(Excellence Academy|SJS ACADEMY|SJS|ZION SCHOOLS)\s*$/i, '')
            .replace(/^(Excellence Academy|SJS ACADEMY|SJS|ZION SCHOOLS)\s*[-–|]\s*/i, '')
            .trim();
    }

    function applyTitle(schoolName) {
        if (!schoolName) return;
        var label = pageLabelFromTitle() || 'Home';
        if (/^home$/i.test(label) || !label) {
            document.title = schoolName + ' - Home';
        } else {
            document.title = label + ' - ' + schoolName;
        }
    }

    function applyLogo(branding) {
        if (!branding) return;
        var links = document.querySelectorAll('.logo-link');
        if (!links.length) return;
        var name = branding.school_name || '';
        var logo = normalizeUrl(branding.logo || '');
        links.forEach(function (link) {
            if (logo) {
                link.innerHTML = '<img src="' + logo + '" alt="' + (name || 'School logo') + '" class="logo-image">';
            } else if (name) {
                link.innerHTML = '<div class="logo-placeholder"><span>' + name.charAt(0) + '</span></div>';
            } else {
                link.innerHTML = '';
            }
        });
    }

    function applyColors(branding) {
        if (!branding) return;
        if (branding.primary_color) {
            document.documentElement.style.setProperty('--primary-color', branding.primary_color);
        }
        if (branding.secondary_color) {
            document.documentElement.style.setProperty('--secondary-color', branding.secondary_color);
        }
        if (branding.font_family && document.body) {
            document.body.style.fontFamily = branding.font_family;
        }
    }

    window.applyBrandingChromeNow = function (branding) {
        if (!branding) return;
        if (branding.favicon) window.setSiteFavicon(branding.favicon);
        if (branding.school_name) applyTitle(branding.school_name);
        applyColors(branding);
        if (document.body) applyLogo(branding);
        else document.addEventListener('DOMContentLoaded', function () { applyLogo(branding); applyColors(branding); });
        window.saveBrandingChrome(branding);
    };

    // 1) Instant apply from last saved branding (real data, not hardcoded)
    try {
        var cached = JSON.parse(localStorage.getItem(CACHE_KEY) || 'null');
        if (cached && (cached.favicon || cached.school_name || cached.logo)) {
            window.applyBrandingChromeNow(cached);
        }
    } catch (e) {}

    // 2) Refresh from server
    function useBranding(branding) {
        if (!branding) return;
        window.applyBrandingChromeNow(branding);
    }

    function pullBranding() {
        fetch('/api/public/header-data.php', { cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (result) {
                if (result && result.success && result.data && result.data.branding) {
                    useBranding(result.data.branding);
                }
            })
            .catch(function () {});
    }

    pullBranding();

    if (window.__pageData && typeof window.__pageData.then === 'function') {
        window.__pageData.then(function (result) {
            var branding = result && result.data && result.data.branding;
            if (branding) useBranding(branding);
        }).catch(function () {});
    }
})();
