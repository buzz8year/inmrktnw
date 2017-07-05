var hint = document.querySelector('.hint-wrap'),
    info = document.querySelector('.info-wrap'),
    cform = document.querySelector('.contact-form'),
    tarea = cform.querySelector('textarea'),
    bwrap = document.querySelector('#qbutton'),
    hintlink = document.querySelector('.hint-wrap div'),
    openButton = document.querySelector('#hamburger'),
    hint = document.querySelector('.hint-wrap'),
    info = document.querySelector('.info-wrap'),
    cform = document.querySelector('.contact-form'),
    tarea = cform.querySelector('textarea'),
    inmail = document.querySelector('.contact-form form .input-email'),
    inphone = document.querySelector('.contact-form form .input-phone'),
    inmess = document.querySelector('.contact-form form .text-message'),
    thank = document.querySelector('.thank-wrap'),
    messicons = document.querySelectorAll('.messenger-icon'),
    wrapfilter = document.querySelector('.wrap-filter');
    mql = window.matchMedia( 'only screen and (min-device-width : 0) and (max-device-width : 1024px) and (orientation : landscape)' ),
    mqp = window.matchMedia( 'only screen and (min-device-width : 0) and (max-device-width : 1024px) and (orientation : portrait)' );


if (!mql.matches && !mqp.matches) {

    openButton.addEventListener('click', function (e) {

        if (openButton.classList.contains('is-open')) {
            burgerTime();
            closeForm();
        }

    });

    bwrap.addEventListener('click', function () {
        openForm();
    });

    hintlink.addEventListener('click', function () {
        openForm();
    });

} else {

    bwrap.addEventListener('touchend', function () {
        openForm();
    });

    // hintlink.addEventListener('touchend', function () {
    //     openForm();
    // });

    openButton.addEventListener('touchend', function () {
        burgerTime();
        closeForm();
    });

    // document.addEventListener('touchend', function (e) {
    //
    //     if (e.target != openButton && openButton.classList.contains('is-open')) {
    //
    //         burgerTime();
    //
    //         closeForm();
    //
    //     }
    //
    // });

}


function burgerTime() {

    if (openButton.classList.contains('is-open')) {
        openButton.classList.remove('is-open');
        openButton.classList.remove('button-opacity');
        openButton.classList.add('is-closed');
    } else {
        openButton.classList.remove('is-closed');
        openButton.classList.add('is-open');
        openButton.classList.add('button-opacity');
    }

}

function openForm() {

    cform.classList.add('m');
    info.classList.add('o');
    hint.classList.add('o');

    // for (var i = 0; i < messicons.length; i++) {
    //     messicons[i].classList.add('icon-color');
    // }

    // setTimeout(function(){
        wrapfilter.classList.add('w');
        tarea.classList.add('l');
        burgerTime();
    // }, 500);

}

function closeForm() {

    // for (var i = 0; i < messicons.length; i++) {
    //     messicons[i].classList.remove('icon-color');
    // }

    wrapfilter.classList.remove('w');

    if (cform.classList.contains('m')) {

        tarea.classList.remove('l');
        cform.classList.remove('m');
        hint.classList.remove('o');
        info.classList.remove('o');

    }

}

function submitForm(contactform) {

    var xhr = new XMLHttpRequest();

    xhr.open(contactform.method, contactform.action, true);

    // xhr.setRequestHeader("Content-type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var json = JSON.parse(xhr.responseText);

            if (json['error']) {

                if (json['error']['error_email']) {
                    inmail.classList.add('error');
                    inmail.classList.add('error-padd');
                    setTimeout(function(){
                        inmail.classList.remove('error-padd');
                    }, 100);
                }
                if (json['error']['error_message']) {
                    inmess.placeholder = json['error']['error_message'];
                    inmess.classList.add('error');
                    inmess.classList.add('error-padd');
                    setTimeout(function(){
                        inmess.classList.remove('error-padd');
                    }, 100);
                }

            } else {

                cform.classList.remove('m');
                thank.classList.add('thank-show');

                burgerTime();

                inmess.value = '';
                inmail.value = '';
                inphone.value = '';

                document.addEventListener('click', function (e) {
                    if (thank.classList.contains('thank-show')) {
                        thank.classList.remove('thank-show');
                        hint.classList.remove('o');
                        info.classList.remove('o');
                    }
                });

            }
        }
    };

    xhr.send(new FormData(contactform));

}


    function thumb_handler(data) {

        console.log(data);

        var icons = document.querySelectorAll('.messenger-wrap a span');

        for (var i = 0; i < icons.length; i++) {

            if (icons[i].getAttribute('class') == data.class) {

                icons[i].style.backgroundImage = 'url(' + data.img + ')';
                icons[i].style.display = 'none';
                icons[i].style.display = 'inline-block';
                icons[i].style.backgroundColor = '#fff';

            }

        }

    }

    function requestServerCall(cls, img) {

        var head = document.head,
            script = document.createElement('script');

        script.src = 'http://inmrkt/general.php?do=load_icons&callback=thumb_handler&class=' + cls + '&img=' + img;

        head.appendChild(script);
        // head.removeChild(script);
    }


    function icon_loader() {

        var icons = document.querySelectorAll('.messenger-wrap a span');

        for (var i = 0; i < icons.length; i++) {

            var img = icons[i].getAttribute('data-img'),
                cls = icons[i].getAttribute('class');

            requestServerCall(cls, img);

        }

    }

    icon_loader();
