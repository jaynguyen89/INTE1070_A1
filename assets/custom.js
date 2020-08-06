function onExpiry() {
    $('#recaptcha-info').attr('class', 'error');
    $('#recaptcha-info').html('Recaptcha verification has expired.<br />Please click the `I am not a robot` checkbox again.');
    $('#recaptcha-token').val(null);
    disableSubmit();
}

function onError() {
    $('#recaptcha-info').attr('class', 'error');
    $('#recaptcha-info').html('Unable to verify due to network interruption.<br />Please check your internet and retry.');
    $('#recaptcha-token').val(null);
    disableSubmit();
}

function onSuccess(recaptchaToken) {
    $('#recaptcha-info').html('');
    $('#recaptcha-token').val(recaptchaToken);
    validate();
}

function validate(version = 2) {
    let password = $('#password').val();
    let confirm = $('#confirm').val();
    let message = $('#validation');

    if (password.length === 0 && confirm.length === 0) {
        message.html('');
        disableSubmit();
        return;
    }

    let passwordBk = password;
    password = password.trim();
    if (password.length === 0) {
        message.html('Password is blank with spaces.<br/>Please properly enter your password.');
        disableSubmit();
        return;
    }

    if (password !== passwordBk || password.indexOf(' ') !== -1) {
        message.html('Password is not allowed to contain spaces.');
        disableSubmit();
        return;
    }

    if (password.length < 10) {
        message.html(password.length === 0 ?
            'Please enter your Password first.' :
            'Password is too short. Minimum 10 characters.');
        disableSubmit();
        return;
    }

    let checkNumber = /(?=.*\d)/;
    let checkUppercase = /(?=.*[A-Z])/;
    let checkLowercase = /(?=.*[a-z])/;

    let error = '';
    if (!checkNumber.test(password)) error += 'Password must have at least 1 number.';
    if (!checkUppercase.test(password)) error += '<br/>Password must have at least 1 uppercase character.';
    if (!checkLowercase.test(password)) error += '<br/>Password must have at least 1 lowercase character.';

    if (error.length !== 0) {
        message.html(error);
        disableSubmit();
        return;
    }

    if (confirm.length === 0) {
        message.html('');
        disableSubmit();
        return;
    }

    if (password !== confirm) {
        message.html('Password and Confirm do not match.');
        disableSubmit();
        return;
    }

    message.html('');
    if ($('#email').val().length > 0) {
        message.html('');

        if ((version === 2 &&
            $('#recaptcha-token').val() !== null &&
            $('#recaptcha-token').val().length !== 0) || version === 3
        ) enableSubmit(version);
        else
            message.html('Please verify Recaptcha to enable Submit button.');
    }
    else {
        message.html('Email is missing. Please enter your email.');
        disableSubmit();
    }
}

function enableSubmit(version) {
    let submitBtn = version === 2 ? $('#submit') : $('#submit-form');
    submitBtn.removeAttr('disabled');
    submitBtn.removeClass('disabled');
}

function disableSubmit(version) {
    let submitBtn = version === 2 ? $('#submit') : $('#submit-form');
    submitBtn.prop('disabled', true);
    submitBtn.addClass('disabled');
}

function confirmRegistration(token) {
    let registrationForm = $('#registration-v3');
    registrationForm.method = 'post';
    registrationForm.action = './recaptcha_v3.php';

    let tokenInput = document.createElement('input');
    tokenInput.type = 'hidden';
    tokenInput.name = 'recaptcha-token';
    tokenInput.value = token;

    let submitInput = document.createElement('input');
    submitInput.type = 'hidden';
    submitInput.name = 'submit_form';
    submitInput.value = 'recaptcha_v3';

    registrationForm.prepend(tokenInput);
    registrationForm.prepend(submitInput);
    registrationForm.submit();
}

function clearForm(version = 2) {
    $('#email').val(null);
    $('#password').val(null);
    $('#confirm').val(null);
    $('#fname').val(null);
    $('#lname').val(null);

    $('#validation').html('');

    if (version === 2) {
        $('#recaptcha-token').val(null);
        disableSubmit();
    }
    else {
        $('input[type=hidden]').remove();
        disableSubmit(version);
    }
}