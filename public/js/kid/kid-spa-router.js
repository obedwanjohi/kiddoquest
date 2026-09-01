/**
 * KiddoQuest Single Page Application (SPA) Router
 * High-speed dynamic screen swapping, zero page reloads, continuous audio, multi-tap request locking.
 */
window.KidSPA = {
    isNavigating: false,
    cache: new Map(),

    init() {
        // Intercept all link clicks in document
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

            // Check if link is an internal kids route (/kids...)
            const url = new URL(link.href, window.location.origin);
            if (url.origin === window.location.origin && url.pathname.startsWith('/kids')) {
                e.preventDefault();
                this.navigate(url.href);
            }
        });

        // Listen for browser back / forward buttons
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.url) {
                this.navigate(e.state.url, { isPopState: true });
            } else {
                this.navigate(window.location.href, { isPopState: true });
            }
        });

        // Save initial state
        window.history.replaceState({ url: window.location.href }, '', window.location.href);
    },

    async navigate(url, options = {}) {
        // 🔒 MULTI-TAP LOCKING: Ignore rapid repeated taps if already navigating
        if (this.isNavigating && !options.isPopState) {
            console.log('🔒 KidSPA: Multi-tap blocked!');
            return;
        }

        this.isNavigating = true;
        this.showLoader();

        try {
            // Play quick tap sound if sound layer is active
            if (window.KidSoundLayer) {
                window.KidSoundLayer.init();
            }

            // Fetch new page with X-Kid-SPA header
            const response = await fetch(url, {
                headers: {
                    'X-Kid-SPA': '1',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                // Fallback to full page reload if response is not ok
                window.location.href = url;
                return;
            }

            const html = await response.text();

            // Extract content inside #kid-app-viewport or fallback to full document body
            const tempDoc = new DOMParser().parseFromString(html, 'text/html');
            const newViewport = tempDoc.querySelector('#kid-app-viewport');
            const currentViewport = document.querySelector('#kid-app-viewport');

            if (newViewport && currentViewport) {
                currentViewport.innerHTML = newViewport.innerHTML;
                
                // Copy any theme attributes / classes
                if (newViewport.className) {
                    currentViewport.className = newViewport.className;
                }
            } else {
                // Fallback: replace body content
                const newBody = tempDoc.querySelector('body');
                if (newBody) {
                    document.body.innerHTML = newBody.innerHTML;
                }
            }

            // Update browser URL bar unless navigating via back/forward
            if (!options.isPopState) {
                window.history.pushState({ url: url }, '', url);
            }

            // Re-execute scripts in injected HTML
            this.executeInjectedScripts(currentViewport || document.body);

            // Re-initialize Alpine.js on the new DOM tree
            if (window.Alpine) {
                this.$nextTick(() => {
                    window.Alpine.initTree(currentViewport || document.body);
                });
            }

            // Scroll to top cleanly
            window.scrollTo({ top: 0, behavior: 'instant' });

        } catch (err) {
            console.error('KidSPA Navigation Error:', err);
            window.location.href = url;
        } finally {
            this.hideLoader();
            // Unlock navigation after a tiny delay so rapid taps are absorbed
            setTimeout(() => {
                this.isNavigating = false;
            }, 300);
        }
    },

    $nextTick(callback) {
        if (window.Alpine && window.Alpine.nextTick) {
            window.Alpine.nextTick(callback);
        } else {
            setTimeout(callback, 50);
        }
    },

    executeInjectedScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach((script) => {
            if (script.src) {
                // Check if already loaded
                if (!document.querySelector(`script[src="${script.src}"]`)) {
                    const newScript = document.createElement('script');
                    newScript.src = script.src;
                    document.head.appendChild(newScript);
                }
            } else {
                // Execute inline script
                try {
                    const fn = new Function(script.textContent);
                    fn();
                } catch (e) {
                    console.error('Error executing inline script:', e);
                }
            }
        });
    },

    showLoader() {
        let loader = document.getElementById('kid-spa-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'kid-spa-loader';
            loader.innerHTML = `
                <div style="position:fixed; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #3B82F6, #F59E0B, #10B981); z-index:99999; animation: spa-pulse 0.8s infinite alternate;"></div>
            `;
            document.body.appendChild(loader);
        }
        loader.style.display = 'block';
    },

    hideLoader() {
        const loader = document.getElementById('kid-spa-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
};

// Auto-initialize SPA router when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.KidSPA.init());
} else {
    window.KidSPA.init();
}
