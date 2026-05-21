/**
 *  infiniteSlide
 *  updateClock
 *  cursorTrail
 *  goTop
 *  settingColor
 *  openMbMenu
 *  switchPrice
 *  services_btn
 *  counter
 *  dot
 *  viewbox
 */

(function ($) {
    "use strict";

    /* Go Top
    -------------------------------------------------------------------------*/
    var goTop = function () {
        var $goTop = $("#goTop");
        var $borderProgress = $(".border-progress");
        var $footer = $(".tf-footer");

        $(window).on("scroll", function () {
            var scrollTop = $(window).scrollTop();
            var docHeight = $(document).height() - $(window).height();
            var scrollPercent = (scrollTop / docHeight) * 100;
            var progressAngle = (scrollPercent / 100) * 360;

            $borderProgress.css("--progress-angle", progressAngle + "deg");

            var windowBottom = scrollTop + $(window).height();
            var hasFooter = $footer.length > 0;
            var footerOffset = hasFooter ? $footer.offset().top : Infinity;

            if (scrollTop > 100 && windowBottom < footerOffset) {
                $goTop.addClass("show");
            } else {
                $goTop.removeClass("show");
            }
        });

        $goTop.on("click", function () {
            $("html, body").animate({ scrollTop: 0 }, 100);
        });
    };
    /* Infinite Slide 
    -------------------------------------------------------------------------*/
    var infiniteSlide = function () {
        if ($(".infiniteSlide").length > 0) {
            $(".infiniteSlide").each(function () {
                var $this = $(this);
                var style = $this.data("style") || "left";
                var clone = $this.data("clone") || 2;
                var speed = $this.data("speed") || 50;
                $this.infiniteslide({
                    speed: speed,
                    direction: style,
                    clone: clone,
                    pauseonhover: false,
                });
            });
        }
    };
    /* Update Clock
    -------------------------------------------------------------------------*/
    var updateClock = () => {
        function startClocks(selector = ".clock") {
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString("en-GB");
                document.querySelectorAll(selector).forEach((el) => {
                    el.textContent = timeString;
                });
            }
            updateClock();
            setInterval(updateClock, 1000);
        }

        startClocks(".clock");
    };
    /* Cursor Trail
    -------------------------------------------------------------------------*/
    var cursorTrail = () => {
        const canvas = document.getElementById("trail");
        const ctx = canvas.getContext("2d");
        let w = window.innerWidth,
            h = window.innerHeight;
        canvas.width = w;
        canvas.height = h;

        let points = [];
        let ripples = [];

        window.addEventListener("resize", () => {
            w = window.innerWidth;
            h = window.innerHeight;
            canvas.width = w;
            canvas.height = h;
        });

        window.addEventListener("mousemove", (e) => {
            points.push({ x: e.clientX, y: e.clientY });
            if (points.length > 10) points.shift();
        });

        window.addEventListener("click", (e) => {
            ripples.push({
                x: e.clientX,
                y: e.clientY,
                radius: 0,
                alpha: 1,
            });
        });

        function draw() {
            ctx.clearRect(0, 0, w, h);

            if (points.length > 1) {
                ctx.beginPath();
                ctx.moveTo(points[0].x, points[0].y);
                for (let i = 1; i < points.length; i++) {
                    ctx.lineTo(points[i].x, points[i].y);
                }
                let last = points[points.length - 1];
                let grad = ctx.createLinearGradient(points[0].x, points[0].y, last.x, last.y);
                grad.addColorStop(0, "black");
                grad.addColorStop(1, "white");
                ctx.strokeStyle = grad;
                ctx.lineWidth = 3;
                ctx.lineCap = "round";
                ctx.stroke();
            }

            ripples.forEach((r, i) => {
                ctx.beginPath();
                ctx.arc(r.x, r.y, r.radius, 0, Math.PI * 2);
                ctx.strokeStyle = `rgba(255,255,255,${r.alpha})`;
                ctx.lineWidth = 2;
                ctx.stroke();
                r.radius += 1;
                r.alpha -= 0.02;
            });
            ripples = ripples.filter((r) => r.alpha > 0);

            requestAnimationFrame(draw);
        }
        draw();

        $("#cursor").on("change", function () {
            if ($(this).is(":checked")) {
                $("#trail").css("display", "block", "important");
            } else {
                $("#trail").css("display", "none", "important");
            }
        });
        
    };
    /* Setting Color
    -------------------------------------------------------------------------*/
    const settingColor = () => {
        if (!$(".settings-color").length) return;

        const COLOR_KEY = "selectedColorIndex";

        const savedIndex = localStorage.getItem(COLOR_KEY);

        if (savedIndex !== null) {
            setColor(savedIndex);
            setActiveItem(savedIndex - 1);
        }

        $(".choose-item").on("click", function () {
            const index = $(this).index();
            setColor(index + 1);
            setActiveItem(index);
            localStorage.setItem(COLOR_KEY, index + 1);
        });

        function setColor(index) {
            $("body").attr("data-color-primary", "color-primary-" + index);
        }

        function setActiveItem(index) {
            $(".choose-item").removeClass("active").eq(index).addClass("active");
        }
    };
    /* Open Menu
    -------------------------------------------------------------------------*/
    var openMbMenu = () => {
        var menu = document.querySelector(".offcanvas-menu");

        document.querySelectorAll(".open-mb-menu").forEach(function(btn) {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                menu.classList.add("show");
                document.body.classList.add("overflow-hidden");
            });
        });

        document.querySelectorAll(".close-mb-menu").forEach(function(btn) {
            btn.addEventListener("click", function() {
                menu.classList.remove("show");
                document.body.classList.remove("overflow-hidden");
            });
        });
    };
    /* switchprice
    -------------------------------------------------------------------------*/
    var switchPrice = () => {
        function formatUSD(n) {
            return '$' + Number(n).toLocaleString('en-US');
        }

        function updatePrices(isYearly) {
            $('.price-number').each(function() {
            const $p = $(this);
            const val = isYearly ? $p.data('year') : $p.data('month');
            $p.text(formatUSD(val));
            $p.next('.price-per').text(isYearly ? '/ year' : '/ month');
            });
        }

        $('#pricingSwitch').on('change', function() {
            updatePrices(this.checked);
        });

        if ($('#pricingSwitch').is(':checked')) {
            updatePrices(true);
        } else {
            updatePrices(false);
        }
    };
    /* services_btn
    -------------------------------------------------------------------------*/
    var services_btn = () => {
        $('.services-image-btn').on('click', function(){
            if(!$(this).hasClass('active-img')) {
                $('.services-image-btn').removeClass('active-img');
                $(this).addClass('active-img');
    
                const newImg = $(this).data('img');
                $('.services-image').find('img').css('opacity', 0);
                setTimeout(() => {
                  $('.services-image').find('img').attr('src', newImg).css('opacity', 1);
                }, 200);
            }
        });
    };
    // counter
    var counter = function () {
        if ($(document.body).hasClass("counter-scroll")) {
          var a = 0;
          $(window).scroll(function () {
            var oTop = $(".counter").offset().top - window.innerHeight;
            if (a == 0 && $(window).scrollTop() > oTop) {
              if ($().countTo) {
                $(".counter")
                  .find(".number")
                  .each(function () {
                    var to = $(this).data("to"),
                      speed = $(this).data("speed");
                    $(this).countTo({
                      to: to,
                      speed: speed,
                    });
                  });
              }
              a = 1;
            }
          });
        }
    };
    // dot
    var dot = function () {
        document.querySelectorAll(".pagi-dot").forEach(pagi => {
            const dots = pagi.querySelectorAll("span");
            const activeIndex = [...dots].findIndex(dot =>
                dot.classList.contains("active")
            );
            dots.forEach(dot => dot.classList.remove("active"));
            ScrollTrigger.create({
                trigger: pagi,
                start: "top 95%",
                once: true,
                onEnter: () => {
                    dots.forEach((dot, index) => {
                        if (index <= activeIndex) {
                            gsap.delayedCall(index * 0.4, () => {
                                dots.forEach(d => d.classList.remove("active"));
                                dot.classList.add("active");
                            });
                        }
                    });
                }
            });
        });
    };
    // viewbox
    var viewbox = function () {
        document.querySelectorAll("svg[data-viewbox-desktop]").forEach(svg => {
            const desktopViewBox = svg.dataset.viewboxDesktop;
            const mobileViewBox  = svg.dataset.viewboxMobile;
            const mq = window.matchMedia("(max-width: 767px)");
            const updateViewBox = e => {
                svg.setAttribute(
                    "viewBox",
                    e.matches ? mobileViewBox : desktopViewBox
                );
            };
            updateViewBox(mq);
            mq.addEventListener("change", updateViewBox);
        });
    };
$(document).ready(function () {
    if ($(".team-pagination-slider").length > 0) {
        var swiper = new Swiper(".team-pagination-slider", {
            slidesPerView: 1, // Shows 1 "slide" (which contains 6 people on desktop)
            spaceBetween: 30,
            loop: true,
            speed: 800,
            grabCursor: true,
            allowTouchMove: true,
            
            // NO AUTOPLAY - STRICTLY MANUAL
            
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            observer: true,
            observeParents: true
        });
    }
});
// Call the function
teamSlider();
    // Dom Ready
    $(function () {
        infiniteSlide();
        updateClock();
        cursorTrail();
        goTop();
        settingColor();
        openMbMenu();
        switchPrice();
        services_btn();
        counter();
        dot();
        viewbox();
    });
})(jQuery);

// Contact form — global handler called via onsubmit on each form
window.nexFormSubmit = function (form, msgId, pageName) {
    var btn = form.querySelector('button[type="submit"]');
    var msg = document.getElementById(msgId);
    var originalText = btn.textContent;

    btn.textContent = 'Sending...';
    btn.disabled = true;
    msg.style.display = 'none';

    if (window.location.protocol === 'file:') {
        msg.style.display = 'block';
        msg.style.background = 'rgba(255,165,0,0.12)';
        msg.style.color = '#FFA500';
        msg.style.border = '1px solid #FFA500';
        msg.textContent = 'Form works on a live server. Upload to hosting to send emails.';
        btn.textContent = originalText;
        btn.disabled = false;
        return;
    }

    var formData = new FormData(form);
    formData.append('page', pageName || document.title);

    fetch('mailer.php', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            msg.style.display = 'block';
            if (data.success) {
                msg.style.background = 'rgba(76,175,80,0.12)';
                msg.style.color = '#4CAF50';
                msg.style.border = '1px solid #4CAF50';
                form.reset();
            } else {
                msg.style.background = 'rgba(255,68,68,0.1)';
                msg.style.color = '#ff4444';
                msg.style.border = '1px solid #ff4444';
            }
            msg.textContent = data.message;
            btn.textContent = originalText;
            btn.disabled = false;
        })
        .catch(function () {
            msg.style.display = 'block';
            msg.style.background = 'rgba(255,68,68,0.1)';
            msg.style.color = '#ff4444';
            msg.style.border = '1px solid #ff4444';
            msg.textContent = 'Something went wrong. Please try again.';
            btn.textContent = originalText;
            btn.disabled = false;
        });
};

// Always start at top of page on navigation
if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
window.scrollTo(0, 0);

// Clear body animation after fade-in so position:fixed (mobile menu) works correctly
document.body.addEventListener('animationend', function (e) {
    if (e.animationName === 'pageFadeIn') document.body.style.animation = 'none';
}, { once: true });

// Page transitions — CSS animation handles fade-in, JS handles fade-out on leave
(function () {
    // Re-play fade-in on back/forward (bfcache restore)
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) document.body.classList.remove('page-leaving');
    });

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a');
        if (!link) return;
        var href = link.getAttribute('href');
        if (
            !href || href === '#' ||
            href.startsWith('#') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            href.startsWith('javascript') ||
            link.getAttribute('target') === '_blank' ||
            e.ctrlKey || e.metaKey || e.shiftKey
        ) return;
        e.preventDefault();
        document.body.classList.add('page-leaving');
        setTimeout(function () { window.location.href = href; }, 250);
    });
})();

// Ensure only one accordion panel opens at a time in #accordion-services
(function () {
    var acc = document.getElementById('accordion-services');
    if (!acc) return;
    acc.addEventListener('show.bs.collapse', function (e) {
        acc.querySelectorAll('.collapse.show').forEach(function (openEl) {
            if (openEl !== e.target) {
                var instance = bootstrap.Collapse.getInstance(openEl);
                if (instance) instance.hide();
            }
        });
    });
})();

