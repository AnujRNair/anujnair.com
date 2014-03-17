$(document).ready(function () {

    var slogan = {
        fadeSpeed: 700,
        waitSpeed: 12000,
        initialize: function () {
            var anim = setInterval(function () {
                slogan.switcher();
            }, slogan.waitSpeed);
            $(".slogan").hover(function () {
                slogan.removeAutoSwitch(anim);
            }, function () {
                anim = setInterval(function () {
                    slogan.switcher();
                }, slogan.waitSpeed);
            });
        },
        switcher: function () {
            $(".slogan:visible").fadeOut(slogan.fadeSpeed, 'swing', function () {
                if ($(this).next().length > 0) {
                    $(this).next().fadeIn(slogan.fadeSpeed, 'swing');
                } else {
                    $(this).siblings(':first').fadeIn(slogan.fadeSpeed, 'swing');
                }
            });
        },
        removeAutoSwitch: function (animation) {
            if (animation) {
                clearInterval(animation);
            }
        }
    };

    if ($('.slogan').size() > 1) {
        slogan.initialize();
    }

    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-16216873-1', 'anujnair.com');
    ga('send', 'pageview');

});