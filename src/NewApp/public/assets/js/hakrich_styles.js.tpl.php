function close(params) {
    var doc = document.getElementById(params) || params;
    if (doc.classList.contains('is-active')) {
        doc.classList.remove('is-active');
    }
}



function getTarget(event) {
    var el = event.target || event.srcElement;
    return el.nodeType == 1 ? el : el.parentNode;
}



function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function uniqid(a = "", b = false) {
    const c = Date.now() / 1000;
    let d = c.toString(16).split(".").join("");
    while (d.length < 14) d += "0";
    let e = "";
    if (b) {
        e = ".";
        e += Math.round(Math.random() * 100000000);
    }
    return a + d + e;
}

function is_Empty(x) {
    if (x.value != undefined) {
        if (x.value == "") {
            return 1;
        } else {
            if (x.value.length > 0) {
                return 0;
            } else {
                return 1;
            }
        }
    }


}



function simplePopup(msg = '', status = true, auto = true) {
    var simplePopup = document.querySelector('.simplePopup'),
        mess = simplePopup.querySelector('.message'),
        msgPlace = simplePopup.querySelector('.message-body');


    if (!simplePopup.classList.contains('display')) {
        if (status) {
            if (!mess.classList.contains('is-success')) {
                mess.classList.remove('is-danger')
                mess.classList.add('is-success');
            }
        } else {
            if (!mess.classList.contains('is-danger')) {
                mess.classList.remove('is-success')
                mess.classList.add('is-danger');
            }
        }
        simplePopup.classList.add('display');
        msgPlace.innerHTML = msg;
        if (auto) {
            setTimeout(() => {
                simplePopup.classList.remove('display');
                msgPlace.innerHTML = '';

            }, 3000);
        }

    }



}



function form_check($) {
    var error = false;
    var error_ = [];
    if ($.length != undefined) {
        for (var i = 0; i < $.length; i++) {
            if ($[i].files) {
                var field = $[i].name;
                if ($[i].files.length == 0) {
                    error = true;
                    error_[field] = "Please select a file to go!!";
                }
            } else {
                var field = $[i].name;
                if ($[i].value == '') {
                    error = true;
                    error_[field] = field + " field is empty!!";
                } else {
                    if (field == 'email') {
                        if (!validateEmail($[i].value)) {
                            error = true;
                            error_[field] = field + " is not valid!";
                        }
                    }
                }

            }
        }

    } else {
        alert("no");
    }
    if (error) {
        return error_;
    } else {
        return false;
    }
}













function formular($, $1 = "null") {
    var f = new FormData(),
        elem = 0;

    if ($.length != undefined) {
        for (var i = 0; i < $.length; i++) {
            if ($[i].files) {
                if ($[i].name != $1) {
                    f.append($[i].name, $[i].files[0]);
                } else {
                    elem = $[i];
                }
            } else {
                f.append($[i].name, $[i].value);
            }
        }
        if (elem != 0) {
            for (var i = 0; i < elem.files.length; i++) {
                f.append(elem.name, elem.files[i]);
            }
        }
    } else {
        if ($.files != undefined) f.append($.name, $.files[0]);
        else if (typeof($) == 'object' && $ instanceof FormData) f = $;
    }


    return f;
};











async function getData(prams = {}, url) {
    const queries = Object.entries(prams).map(param => {
        return `${param[0]}=${param[1]}`;
    }).join('&');
    return fetch(`${url}?${queries}`, {
        method: "GET",
        headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" }
    }).then(res => res.json());
}


async function getData_error(prams = {}, url) {
    const queries = Object.entries(prams).map(param => {
        return `${param[0]}=${param[1]}`;
    }).join('&');
    return fetch(`${url}?${queries}`, {
        method: "GET",
        headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" }
    }).catch($ => $);
}



async function postData(url_add_data, data) {
    const response = fetch(url_add_data, {
        method: 'POST',
        body: formular(data),
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    });
    return response.then(resp => resp.json());
}


async function postData_error(url_add_data, data) {
    const response = fetch(url_add_data, {
        method: 'POST',
        body: formular(data),
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    });
    return response.catch($ => $);
}







function changeWith(e, input) {
    document.getElementById(input).value = e.value;

}




async function shorten_url(url, type, campaign) {
    var form = new FormData();
    form.append('orignal_url', url)
    form.append('cp', campaign)
    return ajax({
        method: 'POST',
        param: form,
        url_add_data: 'shorten_url/url/' + type
    });
}









function readURL({ file, element }) {
    return new Promise((resolve, reject) => {
        var reader = new FileReader();
        reader.onload = function() {
            resolve({
                data: reader.result,
                element: element
            });
        }

        reader.onerror = () => {
            reject("Failed to upload an image");
        }
        reader.readAsDataURL(file);

    })
};



function clearInput(input) {
    if (input.value) {
        try {
            input.value = ''; //for IE11, latest Chrome/Firefox/Opera...
        } catch (err) {}
        if (input.value) { //for IE5 ~ IE10
            var form = document.createElement('form'),
                parentNode = input.parentNode,
                ref = input.nextSibling;
            form.appendChild(input);
            form.reset();
            parentNode.insertBefore(input, ref);
        }
    }
}








class Hkm_ {
    constructor(config) {
        this.config = config;
        this.error = false;
    }

    ajax({ method, param, url_add_data }) {
        const ajaxUrl = this.config.url;
        return new Promise(function(resolve, reject) {
            if (method == 'POST') {
                resolve(postData(ajaxUrl + url_add_data, param));
                reject("error");
            } else if (method == 'GET') {
                resolve(getData(param, ajaxUrl + url_add_data));
                reject("error")
            }

        })
    }


    verifyUser() {
        const VerifClass = this;

        VerifClass.config.inputs.verifyUser.first.addEventListener('keyup', elem => {
            if (elem.target.value.length) {
                VerifClass.config.inputs.verifyUser.second.focus();
            }
            var btn = VerifClass.config.actionsButtons.verifyUser;
            if (!btn.disabled) btn.disabled = true;
        });


        VerifClass.config.inputs.verifyUser.first.addEventListener('keypress', elem => {
            var theEvent = elem || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
        });

        VerifClass.config.inputs.verifyUser.second.addEventListener('keyup', elem => {
            if (elem.target.value.length) {
                VerifClass.config.inputs.verifyUser.third.focus();
            } else {
                VerifClass.config.inputs.verifyUser.first.focus();
            }
            var btn = VerifClass.config.actionsButtons.verifyUser;
            if (!btn.disabled) btn.disabled = true;
        });

        VerifClass.config.inputs.verifyUser.second.addEventListener('keypress', elem => {
            var theEvent = elem || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
        });

        VerifClass.config.inputs.verifyUser.third.addEventListener('keyup', elem => {
            if (elem.target.value.length) {
                VerifClass.config.inputs.verifyUser.fourth.focus();
            } else {
                VerifClass.config.inputs.verifyUser.second.focus();
            }
            var btn = VerifClass.config.actionsButtons.verifyUser;
            if (!btn.disabled) btn.disabled = true;
        });

        VerifClass.config.inputs.verifyUser.third.addEventListener('keypress', elem => {
            var theEvent = elem || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
        });

        VerifClass.config.inputs.verifyUser.fourth.addEventListener('keyup', elem => {
            if (elem.target.value.length) {
                VerifClass.config.inputs.verifyUser.fifth.focus();
            } else {
                VerifClass.config.inputs.verifyUser.third.focus();

            }
            var btn = VerifClass.config.actionsButtons.verifyUser;
            if (!btn.disabled) btn.disabled = true;
        });

        VerifClass.config.inputs.verifyUser.fourth.addEventListener('keypress', elem => {
            var theEvent = elem || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
        });








        VerifClass.config.inputs.verifyUser.fifth.addEventListener('keyup', elem => {
            if (elem.target.value.length) {
                var btn = VerifClass.config.actionsButtons.verifyUser;
                if (btn.disabled) btn.disabled = false;

            } else {
                VerifClass.config.inputs.verifyUser.fourth.focus();
                var btn = VerifClass.config.actionsButtons.verifyUser;
                if (!btn.disabled) btn.disabled = true;
            }
        });

        VerifClass.config.inputs.verifyUser.fifth.addEventListener('keypress', elem => {
            var theEvent = elem || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
        });

        var btn = VerifClass.config.actionsButtons.verifyUser;

        btn.addEventListener('click', event => {

            var one = VerifClass.config.inputs.verifyUser.first.value,
                two = VerifClass.config.inputs.verifyUser.second.value,
                three = VerifClass.config.inputs.verifyUser.third.value,
                four = VerifClass.config.inputs.verifyUser.fourth.value,
                five = VerifClass.config.inputs.verifyUser.fifth.value,
                code = one + two + three + four + five,
                token = VerifClass.config.actionsButtons.resendOTPCode.dataset.token;

            VerifClass.ajax({
                method: "GET",
                param: { act: 'verfy_otp', token: token, code: code },
                url_add_data: "reset_password"
            }).then($ => {
                if ($.error) {
                    VerifClass.error = true;
                    VerifClass.error_msg = $.message;
                    VerifClass.log_error('verifyUser');
                } else {
                    VerifClass.error = false;
                    location.assign($.url);
                }

            }).catch($ => {
                VerifClass.error = true;
                VerifClass.error_msg = 'Something wrong please try again!';
                VerifClass.log_error('verifyUser');
            });
            event.preventDefault();
            return false;
        });
    }

    resetPasswordRequest() {
        const ResetClass = this;
        ResetClass.config.actionsButtons.resetPasswordRequest.addEventListener("click", event => {
            var formData = ResetClass.config.forms.resetPasswordRequest,
                check = form_check(formData);
            if (ResetClass.checkLogin(check, 'resetRequest')) {
                ResetClass.ajax({
                    method: "POST",
                    param: formData,
                    url_add_data: "login?act=reset_password_request"
                }).then($ => {
                    if ($.error) {
                        ResetClass.error = true;
                        ResetClass.error_msg = $.message;
                        ResetClass.log_error('resetRequest');
                    } else {
                        ResetClass.error = false;
                        location.assign($.url);
                    }

                }).catch($ => {
                    ResetClass.error = true;
                    ResetClass.error_msg = 'Something wrong please try again with the correct information!';
                    ResetClass.log_error('resetRequest');
                });
            } else {
                console.log('idontknow');
            }
        });
    }

    resendOTPCode() {
        const OtpClass = this;
        OtpClass.config.actionsButtons.resendOTPCode.addEventListener('click', elem => {
            var token = elem.target.dataset.token;
            OtpClass.ajax({
                method: "GET",
                param: { act: 'resent_otp', token: token },
                url_add_data: "reset_password"
            }).then($ => {
                if ($.error) {
                    OtpClass.error = true;
                    OtpClass.error_msg = $.message;
                    OtpClass.log_error('verifyUser');
                } else {
                    OtpClass.error = false;
                    // location.assign($.url);
                    alert('Resend Code successfully!');
                }

            }).catch($ => {
                OtpClass.error = true;
                OtpClass.error_msg = 'Something wrong please try again!';
                OtpClass.log_error('verifyUser');
            });
        });
    }

    auth() {
        const AuthClass = this;
        AuthClass.config.actionsButtons.auth.addEventListener("click", event => {
            var formData = AuthClass.config.forms.auth,
                check = form_check(formData);
            if (AuthClass.checkLogin(check, 'auth')) {
                AuthClass.ajax({
                    method: "POST",
                    param: formData,
                    url_add_data: "login?act=authentification"
                }).then($ => {
                    if ($.error) {
                        AuthClass.error = true;
                        AuthClass.error_msg = $.message;
                        AuthClass.log_error('auth');
                    } else {
                        AuthClass.error = false;
                        // location.assign($.url);
                        console.log('okay');
                    }

                }).catch($ => {
                    AuthClass.error = true;
                    AuthClass.error_msg = 'Something wrong please try again with the correct information!';
                    AuthClass.log_error('auth');
                });
            } else {
                console.log('idontknow');

            }
        });
    }

    _slid(num) {
        const hkm_slides = document.getElementsByClassName(this.config.slider.element);
        const hkm_slider = document.getElementById(this.config.slider.element);
        const hkm_currentSlide = hkm_slider.getElementsByClassName('current');
        hkm_currentSlide[0].classList.remove("current");
        if (num >= 0 && num < hkm_slides.length) {
            hkm_slides[num].classList.add("current");
        } else {
            console.log('Error: Invalid slider! ');
        }
    }

    forgetPasswordButton() {

        this.config.slider.actionButtons.forgetPassword.btn.addEventListener('click', () => {
            this._slid(this.config.slider.actionButtons.forgetPassword.direction - 1);
        });
        this.config.slider.actionButtons.forgetPassword.backBtn.addEventListener('click', () => {
            this._slid(this.config.slider.actionButtons.forgetPassword.direction - 2);
        });
    }

    _loadWith(st) {
        switch (st) {
            case "Auth":
                this.auth();
                break;
            case "forgetPasswordButton":
                this.forgetPasswordButton();
                break;
            case 'resetPasswordRequest':
                this.resetPasswordRequest();

                break;
            case 'verifyUser':
                this.verifyUser();

                break;
            case 'resendOTPCode':
                this.resendOTPCode();
                break;


            default:
                console.log('Error: This ' + st + ' loader is not defined!');
                break;
        }
    }

    load() {
        this.config.logs.forEach(log => {
            var close = log.querySelector('.delete');
            close.addEventListener('click', event => {
                log.classList.add('off');
            });

        });
        var load = [];


        switch (typeof this.config.load) {
            case 'string':
                load = [this.config.load];
                break;
            case 'object':
                load = this.config.load
                break;
            default:
                console.log("Error: config.load has to be an array or a string not " + typeof this.config.load + "!");
                break;
        }

        load.forEach(loader => {
            this._loadWith(loader);
        });
    }

    log_error(type) {
        var error, msgError;

        switch (type) {
            case "auth":
                error = this.config.logsLoadInfo.auth;
                break;
            case 'resetRequest':
                error = this.config.logsLoadInfo.resetPasswordRequest;
                break;
            case 'verifyUser':
                error = this.config.logsLoadInfo.verifyUser;
                break;

            default:
                break;
        }
        if (this.error) {
            msgError = error.querySelector('#err');
            msgError.innerHTML = this.error_msg;
            if (error.classList.contains('off')) error.classList.remove('off');

        } else {
            if (!error.classList.contains('off')) error.classList.add('off');

        }

    }

    _auth_checking_login_classes(all, value) {
        var box = document.getElementById(this.config.shake.auth),
            passwordInput = document.getElementById(this.config.inputs.auth.password),
            passwordError = document.getElementById(this.config.errors.auth.passwordError),
            emailError = document.getElementById(this.config.errors.auth.usernameError),
            alert_ = document.getElementById(this.config.alert),
            emailInput = document.getElementById(this.config.inputs.auth.username);

        switch (all) {
            case "shakeAll":
                box.classList.add("shakeMe");
                if (!passwordInput.classList.contains("is-danger")) passwordInput.classList.add("is-danger");
                if (!emailInput.classList.contains("is-danger")) emailInput.classList.add("is-danger");
                if (emailError.classList.contains("off")) emailError.classList.remove("off");
                if (value != "none") emailError.innerHTML = value;
                else emailError.innerHTML = "Email Field is Empty!";

                if (passwordError.classList.contains("off")) passwordError.classList.remove("off");

                break;
            case "shakeAllRemain":
                box.classList.remove("shakeMe");
                break;
            case "shakeAllWith":
                box.classList.add("shakeMe");
                if (!passwordInput.classList.contains("is-danger")) passwordInput.classList.add("is-danger");
                if (!emailInput.classList.contains("is-danger")) emailInput.classList.add("is-danger");
                if (value != "none") emailError.innerHTML = value;
                if (emailError.classList.contains("off")) emailError.classList.remove("off");
                if (passwordError.classList.contains("off")) passwordError.classList.remove("off");
                break;
            case "shakeOnlyPassword":
                box.classList.add("shakeMe");
                if (!passwordInput.classList.contains("is-danger")) passwordInput.classList.add("is-danger");
                if (passwordError.classList.contains("off")) passwordError.classList.remove("off");
                if (!emailError.classList.contains("off")) emailError.classList.add("off");
                if (emailInput.classList.contains("is-danger")) emailInput.classList.remove("is-danger");

                break;
            case "shakeOnlyEmail":
                box.classList.add("shakeMe");
                if (!emailInput.classList.contains("is-danger")) emailInput.classList.add("is-danger");
                if (emailError.classList.contains("off")) emailError.classList.remove("off");
                if (!passwordError.classList.contains("off")) passwordError.classList.add("off");
                if (value != "none") emailError.innerHTML = value;
                if (passwordInput.classList.contains("is-danger")) passwordInput.classList.remove("is-danger");

                break;
            case "noShaking":
                box.classList.remove("shakeMe");
                if (emailInput.classList.contains("is-danger")) emailInput.classList.remove("is-danger");
                if (!emailError.classList.contains("off")) emailError.classList.add("off");
                if (!passwordError.classList.contains("off")) passwordError.classList.add("off");
                if (passwordInput.classList.contains("is-danger")) passwordInput.classList.remove("is-danger");

                break;
            default:
                box.classList.add("shakeMe");
                if (!alert_.classList.contains("displayInOut")) alert_.classList.add("displayInOut");

                break;
        }
    }
    _resetRequest_checking_login_classes(all, value) {
        var box = document.getElementById(this.config.shake.resetPasswordRequest),
            userInput = document.getElementById(this.config.inputs.resetPasswordRequest.user),
            userError = document.getElementById(this.config.errors.resetPasswordRequest.userError),
            alert_ = document.getElementById(this.config.alert);
        switch (all) {
            case "shakeAll":
                box.classList.add("shakeMe");
                if (!userInput.classList.contains("is-danger")) userInput.classList.add("is-danger");
                if (userError.classList.contains("off")) userError.classList.remove("off");
                if (value != "none") userError.innerHTML = value;
                else userError.innerHTML = "Invalid value!";
                break;
            case "shakeAllRemain":
                box.classList.remove("shakeMe");
                break;
            case "noShaking":
                box.classList.remove("shakeMe");
                if (userInput.classList.contains("is-danger")) userInput.classList.remove("is-danger");
                if (!userError.classList.contains("off")) userError.classList.add("off");
                break;
            default:
                box.classList.add("shakeMe");
                if (!alert_.classList.contains("displayInOut")) alert_.classList.add("displayInOut");

                break;
        }
    }

    checking_login_classes(all, type, value = "none") {
        switch (type) {
            case 'auth':
                this._auth_checking_login_classes(all, value);
                break;
            case 'resetRequest':
                this._resetRequest_checking_login_classes(all, value);

                break;

            default:
                break;
        }


    }
    _authChecking(check) {
        if (check) {
            if (check[this.config.inputs.auth.username] !== undefined && check[this.config.inputs.auth.password] !== undefined) {
                var em = check[this.config.inputs.auth.username],
                    em2 = em.charAt(0).toUpperCase() + em.slice(1);
                this.checking_login_classes("shakeAll", 'auth', em2);
                setTimeout(() => {
                    this.checking_login_classes("shakeAll", 'auth', em2);
                    this.checking_login_classes("shakeAllRemain", 'auth');
                }, 900);
                return false;

            } else if (check[this.config.inputs.auth.username] === undefined && check[this.config.inputs.auth.password] !== undefined) {
                this.checking_login_classes("shakeOnlyPassword", 'auth');
                setTimeout(() => {
                    this.checking_login_classes("shakeOnlyPassword", "auth");
                    this.checking_login_classes("shakeAllRemain", "auth");
                }, 900);
                return false;
            } else if (check[this.config.inputs.auth.username] !== undefined && check[this.config.inputs.auth.password] === undefined) {
                var em = check[this.config.inputs.auth.username],
                    em2 = em.charAt(0).toUpperCase() + em.slice(1);
                this.checking_login_classes("shakeOnlyEmail", 'auth', em2);
                setTimeout(() => {
                    this.checking_login_classes("shakeOnlyEmail", 'auth', em2);
                    this.checking_login_classes("shakeAllRemain", 'auth');
                }, 900);

                return false;
            }
        } else {
            this.checking_login_classes("noShaking", 'auth');
            return true;
        }
    }

    _resetRequestChecking(check) {
        if (check) {
            if (check[this.config.inputs.resetPasswordRequest.user] !== undefined) {
                var em = check[this.config.inputs.resetPasswordRequest.user],
                    em2 = em.charAt(0).toUpperCase() + em.slice(1);
                this.checking_login_classes("shakeAll", 'resetRequest', em2);
                setTimeout(() => {
                    this.checking_login_classes("shakeAll", 'resetRequest', em2);
                    this.checking_login_classes("shakeAllRemain", 'resetRequest');
                }, 900);
                return false;
            }
        } else {
            this.checking_login_classes("noShaking", 'resetRequest');
            return true;
        }
    }

    checkLogin(check, type) {
        var retun = false;
        switch (type) {
            case 'auth':
                retun = this._authChecking(check);
                break;
            case 'resetRequest':
                retun = this._resetRequestChecking(check);

                break;

            default:
                retun = false;
                break;
        }

        return retun;

    }


}

const Hkm = {
    hkm: (config) => new Hkm_(config),

    /**
     * @method config 
     *
     * {
     *   url: Base Url
     *   authActionBtn: Action button authentication Element
     *   slider: Action object forgetPassword Element
     *   authForm: Authentication form Element
     *   load: Action to load example: Auth
     *   errorLog: Error template Element which contain the Elements with:
     *             class .delete and ID #err
     *   boxToShake: The ID of the Element that can apply shake action
     *   password: The ID of a password Input
     *   passwordError: The ID of a passwordError Element
     *   username: The ID of a username Input
     *   usernameError: The ID of a usernameError Element
     *   alert: The ID of an alert template
     * }
     *
     */


    config: ({
        url,
        inputs,
        forms,
        errors,
        logs,
        logsLoadInfo,
        actionsButtons,
        shake,
        load,
        slider,
        alert
    }) => {

        return {
            url: url,
            inputs: inputs,
            forms: forms,
            errors: errors,
            logs: logs,
            logsLoadInfo: logsLoadInfo,
            actionsButtons: actionsButtons,
            shake: shake,
            load: load,
            slider: slider,
            alert: alert
        }
    }
}