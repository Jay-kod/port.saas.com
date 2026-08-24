/**
 * DevFolio SaaS Platform - Universal Intelligent Floating Tooltip Engine
 * 
 * High-performance, zero-clipping singleton tooltip system.
 * Features:
 * - Dynamic positioning with auto-flip collision detection
 * - Zero layout clipping (attached to document root)
 * - Browser OS native title suppression to prevent duplicate tooltips
 * - Keyboard focus accessibility (WCAG 2.1 AA)
 * - Workspace-aware theme glow indicators (Emerald, Teal, Amber, Slate)
 * - Livewire/Volt DOM morph resilience via event delegation
 */

(function () {
    'use strict';

    let tooltipEl = null;
    let arrowEl = null;
    let textEl = null;
    let kbdEl = null;
    let currentTarget = null;
    let hideTimeout = null;
    let showTimeout = null;

    function createTooltipElement() {
        if (tooltipEl) return tooltipEl;

        tooltipEl = document.createElement('div');
        tooltipEl.id = 'global-tooltip-container';
        tooltipEl.setAttribute('role', 'tooltip');
        tooltipEl.setAttribute('aria-hidden', 'true');
        tooltipEl.className = 'fixed z-[99999] pointer-events-none transition-all duration-150 ease-out opacity-0 scale-95 origin-center select-none';

        tooltipEl.innerHTML = `
            <div id="global-tooltip-inner" class="relative px-3 py-1.5 rounded-xl bg-slate-950/95 backdrop-blur-md border border-white/15 text-slate-100 text-xs font-semibold shadow-2xl shadow-black/90 flex items-center gap-2 max-w-xs leading-snug">
                <span id="global-tooltip-dot" class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                <span id="global-tooltip-text" class="truncate"></span>
                <kbd id="global-tooltip-kbd" class="hidden px-1.5 py-0.5 rounded bg-slate-800 border border-white/10 text-[10px] font-mono text-slate-300"></kbd>
            </div>
            <div id="global-tooltip-arrow" class="absolute w-2 h-2 bg-slate-950 border-r border-b border-white/15 rotate-45 pointer-events-none"></div>
        `;

        document.body.appendChild(tooltipEl);

        arrowEl = tooltipEl.querySelector('#global-tooltip-arrow');
        textEl = tooltipEl.querySelector('#global-tooltip-text');
        kbdEl = tooltipEl.querySelector('#global-tooltip-kbd');

        return tooltipEl;
    }

    function getTooltipTarget(target) {
        if (!target || !(target instanceof Element)) return null;
        return target.closest('[data-tooltip], [title], [data-stored-title]');
    }

    function getThemeAccent(target) {
        if (target.dataset.tooltipTheme) return target.dataset.tooltipTheme;
        if (document.body.classList.contains('theme-agency') || window.location.pathname.startsWith('/agency')) return 'teal';
        if (document.body.classList.contains('theme-super-admin') || window.location.pathname.startsWith('/super-admin')) return 'amber';
        if (window.location.pathname.startsWith('/developer') || window.location.pathname.startsWith('/dashboard')) return 'emerald';
        return 'emerald';
    }

    function updateTooltipStyle(target) {
        const inner = tooltipEl.querySelector('#global-tooltip-inner');
        const dot = tooltipEl.querySelector('#global-tooltip-dot');
        const theme = getThemeAccent(target);

        // Reset previous theme borders & dot colors
        inner.className = 'relative px-3 py-1.5 rounded-xl bg-slate-950/95 backdrop-blur-md border text-slate-100 text-xs font-semibold shadow-2xl shadow-black/90 flex items-center gap-2 max-w-xs leading-snug';

        if (theme === 'teal') {
            inner.classList.add('border-teal-500/30', 'text-teal-100');
            dot.className = 'w-1.5 h-1.5 rounded-full bg-teal-400 shrink-0 shadow-sm shadow-teal-400/50';
        } else if (theme === 'amber') {
            inner.classList.add('border-amber-500/30', 'text-amber-100');
            dot.className = 'w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0 shadow-sm shadow-amber-400/50';
        } else if (theme === 'rose') {
            inner.classList.add('border-rose-500/30', 'text-rose-100');
            dot.className = 'w-1.5 h-1.5 rounded-full bg-rose-400 shrink-0 shadow-sm shadow-rose-400/50';
        } else {
            inner.classList.add('border-emerald-500/30', 'text-emerald-100');
            dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0 shadow-sm shadow-emerald-400/50';
        }
    }

    function showTooltip(target) {
        if (!target) return;
        clearTimeout(hideTimeout);
        clearTimeout(showTimeout);

        createTooltipElement();

        // Suppress browser native title if present to prevent clashing OS tooltip
        if (target.hasAttribute('title')) {
            const rawTitle = target.getAttribute('title');
            if (rawTitle) {
                target.setAttribute('data-stored-title', rawTitle);
                target.removeAttribute('title');
            }
        }

        const text = target.getAttribute('data-tooltip') || target.getAttribute('data-stored-title');
        if (!text || !text.trim()) return;

        currentTarget = target;
        textEl.textContent = text.trim();

        const kbd = target.getAttribute('data-tooltip-kbd');
        if (kbd) {
            kbdEl.textContent = kbd;
            kbdEl.classList.remove('hidden');
        } else {
            kbdEl.classList.add('hidden');
        }

        updateTooltipStyle(target);

        // Position calculation
        showTimeout = setTimeout(() => {
            if (currentTarget !== target) return;

            const rect = target.getBoundingClientRect();
            let preferredPos = target.getAttribute('data-tooltip-pos') || 'top';

            tooltipEl.style.display = 'block';
            tooltipEl.style.left = '0px';
            tooltipEl.style.top = '0px';

            const tipRect = tooltipEl.getBoundingClientRect();
            const gap = 8;
            let top, left;

            // Auto flip if near edge
            if (preferredPos === 'top' && rect.top - tipRect.height - gap < 8) {
                preferredPos = 'bottom';
            } else if (preferredPos === 'bottom' && rect.bottom + tipRect.height + gap > window.innerHeight - 8) {
                preferredPos = 'top';
            }

            if (preferredPos === 'bottom') {
                top = rect.bottom + gap;
                left = rect.left + (rect.width / 2) - (tipRect.width / 2);
                arrowEl.style.top = '-4px';
                arrowEl.style.bottom = 'auto';
                arrowEl.style.left = 'calc(50% - 4px)';
                arrowEl.style.borderRight = 'none';
                arrowEl.style.borderBottom = 'none';
                arrowEl.style.borderLeft = '1px solid rgba(255,255,255,0.15)';
                arrowEl.style.borderTop = '1px solid rgba(255,255,255,0.15)';
            } else if (preferredPos === 'left') {
                top = rect.top + (rect.height / 2) - (tipRect.height / 2);
                left = rect.left - tipRect.width - gap;
                arrowEl.style.top = 'calc(50% - 4px)';
                arrowEl.style.left = 'auto';
                arrowEl.style.right = '-4px';
                arrowEl.style.borderLeft = 'none';
                arrowEl.style.borderBottom = 'none';
                arrowEl.style.borderRight = '1px solid rgba(255,255,255,0.15)';
                arrowEl.style.borderTop = '1px solid rgba(255,255,255,0.15)';
            } else if (preferredPos === 'right') {
                top = rect.top + (rect.height / 2) - (tipRect.height / 2);
                left = rect.right + gap;
                arrowEl.style.top = 'calc(50% - 4px)';
                arrowEl.style.left = '-4px';
                arrowEl.style.right = 'auto';
                arrowEl.style.borderRight = 'none';
                arrowEl.style.borderTop = 'none';
                arrowEl.style.borderLeft = '1px solid rgba(255,255,255,0.15)';
                arrowEl.style.borderBottom = '1px solid rgba(255,255,255,0.15)';
            } else {
                // Default 'top'
                top = rect.top - tipRect.height - gap;
                left = rect.left + (rect.width / 2) - (tipRect.width / 2);
                arrowEl.style.top = 'auto';
                arrowEl.style.bottom = '-4px';
                arrowEl.style.left = 'calc(50% - 4px)';
                arrowEl.style.borderLeft = 'none';
                arrowEl.style.borderTop = 'none';
                arrowEl.style.borderRight = '1px solid rgba(255,255,255,0.15)';
                arrowEl.style.borderBottom = '1px solid rgba(255,255,255,0.15)';
            }

            // Viewport clamping
            const padding = 10;
            if (left < padding) {
                const diff = padding - left;
                left = padding;
                arrowEl.style.left = `calc(50% - 4px - ${diff}px)`;
            } else if (left + tipRect.width > window.innerWidth - padding) {
                const diff = (left + tipRect.width) - (window.innerWidth - padding);
                left = window.innerWidth - padding - tipRect.width;
                arrowEl.style.left = `calc(50% - 4px + ${diff}px)`;
            }

            tooltipEl.style.top = `${Math.round(top)}px`;
            tooltipEl.style.left = `${Math.round(left)}px`;
            tooltipEl.style.opacity = '1';
            tooltipEl.style.transform = 'scale(1)';
            tooltipEl.setAttribute('aria-hidden', 'false');
        }, 50);
    }

    function hideTooltip(target) {
        clearTimeout(showTimeout);
        clearTimeout(hideTimeout);

        if (!tooltipEl) return;

        if (target && target.hasAttribute('data-stored-title')) {
            target.setAttribute('title', target.getAttribute('data-stored-title'));
            target.removeAttribute('data-stored-title');
        }

        tooltipEl.style.opacity = '0';
        tooltipEl.style.transform = 'scale(0.95)';
        tooltipEl.setAttribute('aria-hidden', 'true');

        hideTimeout = setTimeout(() => {
            if (tooltipEl && tooltipEl.style.opacity === '0') {
                tooltipEl.style.display = 'none';
                currentTarget = null;
            }
        }, 150);
    }

    // Delegated Global Listeners
    document.addEventListener('mouseenter', (e) => {
        const target = getTooltipTarget(e.target);
        if (target) showTooltip(target);
    }, true);

    document.addEventListener('mouseleave', (e) => {
        const target = getTooltipTarget(e.target);
        if (target) hideTooltip(target);
    }, true);

    document.addEventListener('focusin', (e) => {
        const target = getTooltipTarget(e.target);
        if (target) showTooltip(target);
    }, true);

    document.addEventListener('focusout', (e) => {
        const target = getTooltipTarget(e.target);
        if (target) hideTooltip(target);
    }, true);

    document.addEventListener('click', () => {
        hideTooltip(currentTarget);
    }, true);

    document.addEventListener('scroll', () => {
        if (currentTarget) hideTooltip(currentTarget);
    }, { passive: true });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && currentTarget) {
            hideTooltip(currentTarget);
        }
    });

    // Expose utility globally
    window.DevFolioTooltip = {
        show: showTooltip,
        hide: hideTooltip
    };
})();
