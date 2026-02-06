// ============= footer validation form start ============= //
let form = document.getElementById('footer-form');
let successMessage = document.getElementById('footer-message');

if (form != null) {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        successMessage.innerHTML = 'Subscribe successfully!'
        successMessage.style.display = 'block'
        form.reset()
        setTimeout(() => {
            successMessage.style.display = 'none'
        }, 3000)
    })
}
// ============= End of footer validation form ============= //

// ============= sidebar start ============= // 
const sidebarEvent = document.getElementById('sidebarEvent');
const sidebar = document.querySelector('.sidebar');
sidebarEvent.addEventListener('click', function () {
    sidebar.classList.toggle('sidebar-toggle');
});

const closeBtn = document.getElementById('closeBtn');

// El "if" hedhi hiya el solution: t9ollou "ken l9it el bouton, a3mel el click"
if (closeBtn) {
    closeBtn.addEventListener('click', function () {
        // thabet zeda elli "sidebar" mawjouda kima l'élément hedha
        const sidebar = document.querySelector('.sidebar'); // wala esm el id mte3ha
        if (sidebar) {
            sidebar.classList.toggle('sidebar-toggle');
        }
    });
}
// ============= End of sidebar ============= //

// ============= counter start ============= //
(function (e) {
    "use strict";
    e.fn.counterUp = function (t) {
        const n = e.extend({
            time: 400,
            delay: 10
        }, t);

        return this.each(function () {
            const t = e(this);
            const r = n;

            function incrementValue() {
                const nums = [];
                const duration = r.time / r.delay;
                let value = t.text();
                const hasCommas = /[0-9]+,[0-9]+/.test(value);
                value = value.replace(/,/g, "");
                const isNumber = /^[0-9]+$/.test(value);
                const isFloat = /^[0-9]+\.[0-9]+$/.test(value);
                const decimalPlaces = isFloat ? (value.split(".")[1] || []).length : 0;

                for (let i = duration; i >= 1; i--) {
                    let newValue = parseInt(value / duration * i);
                    if (isFloat) {
                        newValue = parseFloat(value / duration * i).toFixed(decimalPlaces);
                    }
                    if (hasCommas) {
                        while (/(\d+)(\d{3})/.test(newValue.toString())) {
                            newValue = newValue.toString().replace(/(\d+)(\d{3})/, "$1,$2");
                        }
                    }
                    nums.unshift(newValue);
                }

                t.data("counterup-nums", nums);
                t.text("0");

                function updateValue() {
                    t.text(t.data("counterup-nums").shift());
                    if (t.data("counterup-nums").length) {
                        setTimeout(updateValue, r.delay);
                    } else {
                        t.removeData("counterup-nums");
                    }
                }

                t.data("counterup-func", updateValue);
                setTimeout(t.data("counterup-func"), r.delay);
            }

            t.waypoint(incrementValue, {
                offset: "100%",
                triggerOnce: true
            });
        });
    };
})(jQuery);

jQuery(document).ready(function ($) {
    $('.count').counterUp({
        delay: 10,
        time: 2000
    });
});
// ============= End of counter ============= //

// ============= button back to top start ============= //
window.onscroll = function () {
    scrollFunction();
};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("backToTopBtn").style.display = "block";
    } else {
        document.getElementById("backToTopBtn").style.display = "none";
    }
}

function scrollToTop() {
    const scrollToTopBtn = document.documentElement || document.body;
    scrollToTopBtn.scrollIntoView({
        behavior: "smooth"
    });
}

// ============= End of button back to top ============= //

// ============= our partner slider section ============= //
$('.partner-slider').slick({
    dots: false,
    infinite: true,
    autoplay: true,
    speed: 3000,
    slidesToShow: 5,
    autoplaySpeed: 0,
    arrows: false,
    slidesToScroll: 1,
    cssEase: 'linear',
    responsive: [
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
                infinite: true,
                dots: false
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        }
    ]
});

// ============= End of our partner slider section ============= //

// ============= game countdown start ============= //
// first
(function () {
    const second = 1000,
        minute = second * 60,
        hour = minute * 60,
        day = hour * 24;

    function startCountdown(endDate) {
        const countDown = new Date(endDate).getTime();

        const intervalId = setInterval(function () {
            const now = new Date().getTime(),
                distance = countDown - now;

            const Day_1 = document.getElementById("days");
            const Hours_1 = document.getElementById("hours");
            const Minutes_1 = document.getElementById("minutes");
            const Seconds_1 = document.getElementById("seconds");
            const countdownElement = document.getElementById("countdown");
            const content2Element = document.getElementById("content_2");

            if (Day_1 !== null) {
                Day_1.innerText = Math.floor(distance / day).toString().padStart(2, "0");
            }
            if (Hours_1 !== null) {
                Hours_1.innerText = Math.floor((distance % day) / hour).toString().padStart(2, "0");
            }
            if (Minutes_1 !== null) {
                Minutes_1.innerText = Math.floor((distance % hour) / minute).toString().padStart(2, "0");
            }
            if (Seconds_1 !== null) {
                Seconds_1.innerText = Math.floor((distance % minute) / second).toString().padStart(2, "0");
            }

            if (distance < 0 && countdownElement && content2Element) {
                document.getElementById("countdown").style.display = "none";
                document.getElementById("content_2").style.display = "block";
                clearInterval(intervalId);
            }
        }, 0);
    }

    const initialEndDate = new Date("March 29, 2024 17:17:00").getTime();

    startCountdown(initialEndDate);

})();


// second

(function () {
    const second = 1000,
        minute = second * 60,
        hour = minute * 60,
        day = hour * 24;
     
    function startCountdown(endDate) {
        const countDown = new Date(endDate).getTime();

        const intervalId = setInterval(function () {
            const now = new Date().getTime(),
                distance = countDown - now;

                const Day_1 = document.getElementById("days_2");
                 const Hours_1 = document.getElementById("hours_2");
                 const Minutes_1 = document.getElementById("minutes_2");
                 const Seconds_1 = document.getElementById("seconds_2");
                 const countdownElement = document.getElementById("countdown_2");
                 const content2Element = document.getElementById("content_3");

            if (Day_1 !== null) {
                Day_1.innerText = Math.floor(distance / day).toString().padStart(2, "0");
            }
            if (Hours_1 !== null) {
                Hours_1.innerText = Math.floor((distance % day) / hour).toString().padStart(2, "0");
            }
            if (Minutes_1 !== null) {
                Minutes_1.innerText = Math.floor((distance % hour) / minute).toString().padStart(2, "0");
            }
            if (Seconds_1 !== null) {
                Seconds_1.innerText = Math.floor((distance % minute) / second).toString().padStart(2, "0");
            }

            if (distance < 0 && countdownElement && content2Element) {
                document.getElementById("countdown_2").style.display = "none";
                document.getElementById("content_3").style.display = "block";
                clearInterval(intervalId);
            }
        }, 0);
    }

    const initialEndDate = new Date("March 21, 2024 1:18:44").getTime();

    startCountdown(initialEndDate);

})();
// ============= End of game countdown ============= //

// ============= upcoming matches tab ============= //
const matches_buttons = document.querySelectorAll('.matches_buttons button');
const match_card = document.querySelectorAll('.match_date .match_card')
const match_date = document.querySelector('.match_date')
matches_buttons.forEach(button => button.addEventListener('click', (e) => {
    document.querySelector('.activeTwo').classList.remove('activeTwo')
    e.target.classList.add('activeTwo');

    match_card.forEach(card => {
        if (e.target.dataset.name === card.dataset.name || e.target.dataset.name === "all") {
            card.style.display = "flex";
        } else {
            card.style.display = "none";
        }
    });

   // Set the margin-top of the parent container
    const matchesContainer = document.querySelector('.match_date');
    matchesContainer.style.transition = "none";
    matchesContainer.style.marginTop = "600px";
    setTimeout(() => {
        matchesContainer.style.transition = "margin 0.8s ease";

        matchesContainer.style.marginTop = "0px";
    }, );

}));
// ============= End of upcoming matches tab ============= //

// ============= testimonials slider start ============= //
// index page slider
$('.testimonials-slider').slick({
    dots: false,
    infinite: true,
    autoplay: true,
    speed: 300,
    slidesToShow: 1,
    autoplaySpeed: 4000,
    arrows: true,
    slidesToScroll: 1,
    responsive: [
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                dots: false
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
    ]
});

// testimonial page slider
$('.testimonialsPage-slider').slick({
    dots: false,
    infinite: true,
    autoplay: true,
    speed: 300,
    slidesToShow: 1,
    autoplaySpeed: 4000,
    arrows: true,
    slidesToScroll: 1,
    responsive: [
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                dots: false
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
    ]
});
// ============= End of testimonials slider ============= //





// const intervalId = setInterval(function () {
//     const now = new Date().getTime(),
//         distance = adjustedCountDown - now;

//     const Day_1 = document.getElementById("days");
//     const Hours_1 = document.getElementById("hours");
//     const Minutes_1 = document.getElementById("minutes");
//     const Seconds_1 = document.getElementById("seconds");
//     const countdownElement = document.getElementById("countdown");
//     const content2Element = document.getElementById("content_2");

    
//     if (Day_1 && Day_1 !== null) {
//         Day_1.innerText = Math.floor(distance / day);
//     }
//     if (Hours_1 && Hours_1 !== null) {
//         Hours_1.innerText = Math.floor((distance % day) / hour);
//     }
//     if (Minutes_1 && Minutes_1 !== null) {
//         Minutes_1.innerText = Math.floor((distance % hour) / minute);
//     }
//     if (Seconds_1 && Seconds_1 !== null) {
//         Seconds_1.innerText = Math.floor((distance % minute) / second);
//     }

//     if (distance < 0 && countdownElement && content2Element) {
//         countdownElement.style.display = "none";
//         content2Element.style.display = "block";
//         clearInterval(intervalId);
//     }
// }, 1000); 