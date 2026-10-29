/* Scripts du site ERIS SARL — chargé en "defer" sur toutes les pages publiques. */
(function () {
    'use strict';

    /* ---------- Menu mobile ---------- */
    var openBtn = document.getElementById('mobile-menu-button');
    var closeBtn = document.getElementById('close-menu-button');
    var menu = document.getElementById('mobile-menu');
    var backdrop = document.getElementById('menu-backdrop');

    function setMenu(open) {
        if (!menu) return;
        menu.classList.toggle('open', open);
        if (backdrop) backdrop.classList.toggle('open', open);
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
        if (open) {
            var first = menu.querySelector('a, button');
            if (first) first.focus();
        } else if (openBtn && menu.contains(document.activeElement)) {
            openBtn.focus();
        }
    }

    if (openBtn && menu) {
        // On utilise closest()/contains() : un clic sur l'icône SVG du bouton
        // ne doit pas être pris pour un clic "à l'extérieur" (bug précédent).
        openBtn.addEventListener('click', function (e) { e.stopPropagation(); setMenu(!menu.classList.contains('open')); });
        if (closeBtn) closeBtn.addEventListener('click', function () { setMenu(false); });
        if (backdrop) backdrop.addEventListener('click', function () { setMenu(false); });
        menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menu.classList.contains('open')) setMenu(false); });
        // Repasse en affichage bureau : on referme
        window.matchMedia('(min-width: 1024px)').addEventListener('change', function (mq) { if (mq.matches) setMenu(false); });
    }

    /* ---------- Ombre de l'en-tête au défilement ---------- */
    var header = document.querySelector('.header');
    if (header) {
        var onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 20); };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------- Formulaires : empêche les envois multiples ---------- */
    document.querySelectorAll('form[data-single-submit]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (form.dataset.submitting === '1') { e.preventDefault(); return; }
            if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;
            form.dataset.submitting = '1';
            var buttons = Array.prototype.slice.call(form.querySelectorAll('button[type="submit"]'));
            // Boutons placés hors du formulaire (attribut form="id"), comme dans l'admin
            if (form.id) buttons = buttons.concat(Array.prototype.slice.call(document.querySelectorAll('button[form="' + form.id + '"]')));
            buttons.forEach(function (btn) {
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
                var label = btn.querySelector('[data-label]');
                if (label) label.textContent = 'Envoi en cours…';
            });
        });
    });

    /* ---------- Galerie : visionneuse ---------- */
    var lightbox = document.getElementById('lightbox');
    if (lightbox) {
        var lbImg = document.getElementById('lightbox-image');
        var lbTitle = document.getElementById('lightbox-title');
        var lbDesc = document.getElementById('lightbox-desc');
        var items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
        var current = -1;
        var lastFocus = null;

        var show = function (i) {
            if (!items.length) return;
            current = (i + items.length) % items.length;
            var it = items[current];
            lbImg.src = it.dataset.src;
            lbImg.alt = it.dataset.title || '';
            lbTitle.textContent = it.dataset.title || '';
            lbDesc.textContent = it.dataset.desc || '';
        };
        var open = function (i) {
            lastFocus = document.activeElement;
            show(i);
            lightbox.classList.add('open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            document.getElementById('lightbox-close').focus();
        };
        var close = function () {
            lightbox.classList.remove('open');
            lightbox.setAttribute('aria-hidden', 'true');
            lbImg.removeAttribute('src');
            document.body.style.overflow = '';
            if (lastFocus) lastFocus.focus();
        };

        items.forEach(function (el, i) { el.addEventListener('click', function () { open(i); }); });
        document.getElementById('lightbox-close').addEventListener('click', close);
        document.getElementById('lightbox-prev').addEventListener('click', function () { show(current - 1); });
        document.getElementById('lightbox-next').addEventListener('click', function () { show(current + 1); });
        lightbox.addEventListener('click', function (e) { if (e.target === lightbox) close(); });
        document.addEventListener('keydown', function (e) {
            if (!lightbox.classList.contains('open')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') show(current - 1);
            if (e.key === 'ArrowRight') show(current + 1);
        });
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Apparition des blocs au défilement ---------- */
    var reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && !reduceMotion) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        reveals.forEach(function (el) { io.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('in'); });
    }

    /* ---------- Compteurs animés (chiffres clés) ---------- */
    var counters = document.querySelectorAll('[data-count]');
    var animateCount = function (el) {
        var target = parseInt(el.dataset.count, 10);
        if (isNaN(target) || reduceMotion) return;
        var prefix = el.dataset.prefix || '';
        var start = null;
        var duration = 1400;
        var step = function (ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = prefix + Math.round(target * eased);
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };
    if (counters.length && 'IntersectionObserver' in window) {
        var co = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) { animateCount(entry.target); co.unobserve(entry.target); }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { co.observe(el); });
    }

    /* ---------- Bouton "haut de page" ---------- */
    var toTop = document.getElementById('to-top');
    if (toTop) {
        var onScrollTop = function () { toTop.classList.toggle('visible', window.scrollY > 600); };
        window.addEventListener('scroll', onScrollTop, { passive: true });
        onScrollTop();
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
            var main = document.getElementById('contenu');
            if (main) main.focus({ preventScroll: true });
        });
    }

    /* ---------- Vidéo d'accueil ---------- */
    // Pas de vidéo si l'utilisateur limite les animations ou économise ses données :
    // l'image de remplacement (poster) reste affichée.
    var video = document.getElementById('hero-video');
    if (video) {
        var conn = navigator.connection || {};
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || conn.saveData || /(^|-)2g$/.test(conn.effectiveType || '')) {
            video.removeAttribute('autoplay');
            video.pause();
        } else {
            var src = video.dataset.src;
            if (src && !video.querySelector('source')) {
                var s = document.createElement('source');
                s.src = src;
                s.type = 'video/mp4';
                video.appendChild(s);
                video.load();
                var p = video.play();
                if (p && p.catch) p.catch(function () { /* lecture auto bloquée : le poster reste visible */ });
            }
        }
    }
})();
