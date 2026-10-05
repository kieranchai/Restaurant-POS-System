    </main>

    <script>
        /* Preserve scroll position across POST reloads so the page does not
           jump back to the top after adding an item, applying a promo, etc.
           We disable the browser's own restoration and re-apply the saved
           position a few times to beat any late reflow. */
        (function () {
            var KEY = "emberScrollY";
            if ("scrollRestoration" in history) { history.scrollRestoration = "manual"; }

            document.addEventListener("submit", function () {
                try { sessionStorage.setItem(KEY, String(window.scrollY)); } catch (e) {}
            }, true);

            var y = null;
            try { y = sessionStorage.getItem(KEY); } catch (e) {}
            if (y === null) { return; }
            y = parseInt(y, 10) || 0;
            try { sessionStorage.removeItem(KEY); } catch (e) {}

            function restore() { window.scrollTo(0, y); }
            // Apply as early as possible and again after parse/layout/images.
            restore();
            document.addEventListener("DOMContentLoaded", restore);
            window.addEventListener("load", restore);
            var n = 0, timer = setInterval(function () {
                restore();
                if (++n > 6) { clearInterval(timer); }
            }, 60);
        })();

        /* Auto dismiss toast notifications after a short delay. */
        window.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".toast").forEach(function (toast) {
                setTimeout(function () {
                    toast.style.transition = "opacity .4s ease";
                    toast.style.opacity = "0";
                    setTimeout(function () { toast.remove(); }, 400);
                }, 2600);
            });
        });

        /* Keep an action button disabled until all required fields are filled.
           Fields and their button share the same data-vgroup value. Grouping is
           flat (document wide) so it survives table and form nesting.
           Mark inputs with [data-validate-field], the button with [data-validate-btn]. */
        window.addEventListener("DOMContentLoaded", function () {
            var groups = {};
            document.querySelectorAll("[data-vgroup]").forEach(function (el) {
                var name = el.getAttribute("data-vgroup");
                groups[name] = groups[name] || { fields: [], btn: null };
                if (el.hasAttribute("data-validate-field")) { groups[name].fields.push(el); }
                if (el.hasAttribute("data-validate-btn")) { groups[name].btn = el; }
            });
            Object.keys(groups).forEach(function (name) {
                var g = groups[name];
                if (!g.btn || !g.fields.length) { return; }
                function check() {
                    var ok = g.fields.every(function (f) { return f.value.trim() !== ""; });
                    g.btn.disabled = !ok;
                    g.btn.classList.toggle("btn-disabled", !ok);
                }
                g.fields.forEach(function (f) {
                    f.addEventListener("input", check);
                    f.addEventListener("change", check);
                });
                check();
            });
        });

        /* Logout confirmation modal. */
        window.addEventListener("DOMContentLoaded", function () {
            var modal = document.getElementById("logoutModal");
            var form = document.getElementById("logoutForm");
            if (!modal) { return; }
            var open = document.querySelector("[data-logout-open]");
            var cancel = modal.querySelector("[data-logout-cancel]");
            var confirmBtn = modal.querySelector("[data-logout-confirm]");
            if (open) { open.addEventListener("click", function () { modal.hidden = false; }); }
            if (cancel) { cancel.addEventListener("click", function () { modal.hidden = true; }); }
            if (confirmBtn) { confirmBtn.addEventListener("click", function () { if (form) { form.submit(); } }); }
            modal.addEventListener("click", function (e) { if (e.target === modal) { modal.hidden = true; } });
            document.addEventListener("keydown", function (e) { if (e.key === "Escape") { modal.hidden = true; } });
        });

        /* Demo login quick fill: click fills the staff username and password. */
        window.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".demo-fill").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    var u = document.getElementById("username");
                    var p = document.getElementById("userpassword");
                    if (u) { u.value = btn.getAttribute("data-user"); u.dispatchEvent(new Event("input", { bubbles: true })); }
                    if (p) { p.value = btn.getAttribute("data-pass"); p.dispatchEvent(new Event("input", { bubbles: true })); }
                    if (u) { u.focus(); }
                });
            });
        });

        /* Menu scrollspy: highlight the category in the left rail that matches
           the section currently in view. */
        window.addEventListener("DOMContentLoaded", function () {
            var sections = document.querySelectorAll("[data-spy-section]");
            var links = document.querySelectorAll("[data-spy-link]");
            if (!sections.length || !links.length) { return; }

            function setActive(slug) {
                links.forEach(function (l) {
                    l.classList.toggle("is-active", l.getAttribute("data-spy-link") === slug);
                });
            }

            if ("IntersectionObserver" in window) {
                var visible = {};
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        visible[e.target.getAttribute("data-spy-section")] = e.isIntersecting ? e.boundingClientRect.top : null;
                    });
                    // Pick the top-most section that is currently intersecting.
                    var best = null, bestTop = Infinity;
                    Object.keys(visible).forEach(function (slug) {
                        var top = visible[slug];
                        if (top !== null && top < bestTop) { bestTop = top; best = slug; }
                    });
                    if (best) { setActive(best); }
                }, { rootMargin: "-150px 0px -60% 0px", threshold: 0 });
                sections.forEach(function (s) { observer.observe(s); });
            }

            // Highlight immediately on click as well.
            links.forEach(function (l) {
                l.addEventListener("click", function () { setActive(l.getAttribute("data-spy-link")); });
            });
            setActive(sections[0].getAttribute("data-spy-section"));
        });
    </script>
</body>

</html>
