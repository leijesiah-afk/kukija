(function () {
    if (window.__kukijaValidationBooted) return;
    window.__kukijaValidationBooted = true;

    function get$() {
        return window.jQuery || window.$ || null;
    }

    function setFieldError($form, name, message) {
        const $ = get$();
        if (!$) return;

        const $el = $form.find(`.kukija-field-error[data-error-for="${name}"]`).first();
        if (!$el.length) return;

        if (message) {
            $el.text(message);
            $el.show();
        } else {
            $el.text('');
            $el.hide();
        }
    }

    function setFormError($form, message) {
        const $ = get$();
        if (!$) return;

        const $el = $form.find('.kukija-form-error').first();
        if (!$el.length) return;

        if (message) {
            $el.text(message);
            $el.show();
        } else {
            $el.text('');
            $el.hide();
        }
    }

    function isEmail(value) {
        const v = String(value || '').trim();
        if (!v) return false;
        if (!v.includes('@')) return false;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    function isName(value, { allowSpaces } = { allowSpaces: false }) {
        const v = String(value || '').trim();
        if (!v) return false;
        const re = allowSpaces ? /^[A-Za-z ]+$/ : /^[A-Za-z]+$/;
        return re.test(v);
    }

    function normalizePhone(value) {
        return String(value || '')
            .trim()
            .replace(/[\s\-()]/g, '');
    }

    function isPHPhone(value) {
        const v = normalizePhone(value);
        return /^(09\d{9}|\+639\d{9})$/.test(v);
    }

    function validateRequired($form, name, value, message) {
        if (!String(value || '').trim()) {
            setFieldError($form, name, message);
            return false;
        }
        setFieldError($form, name, null);
        return true;
    }

    function validateLoginForm($form) {
        const $ = get$();
        if (!$) return false;

        setFormError($form, null);

        const email = $form.find('input[name="email"]').val();
        const password = $form.find('input[name="password"]').val();

        let ok = true;

        ok = validateRequired($form, 'email', email, 'Email is required.') && ok;
        if (String(email || '').trim() && !isEmail(email)) {
            setFieldError($form, 'email', 'Please enter a valid email address.');
            ok = false;
        }

        ok = validateRequired($form, 'password', password, 'Password is required.') && ok;

        return ok;
    }

    function validateSignupForm($form) {
        const $ = get$();
        if (!$) return false;

        setFormError($form, null);

        const first = $form.find('input[name="first_name"]').val();
        const last = $form.find('input[name="last_name"]').val();
        const email = $form.find('input[name="email"]').val();
        const address = $form.find('input[name="address"]').val();
        const contactNo = $form.find('input[name="contact_no"]').val();
        const password = $form.find('input[name="password"]').val();
        const passwordConfirmation = $form.find('input[name="password_confirmation"]').val();

        let ok = true;

        ok = validateRequired($form, 'first_name', first, 'First name is required.') && ok;
        if (String(first || '').trim() && !isName(first)) {
            setFieldError($form, 'first_name', 'First name must contain letters only.');
            ok = false;
        }

        ok = validateRequired($form, 'last_name', last, 'Last name is required.') && ok;
        if (String(last || '').trim() && !isName(last)) {
            setFieldError($form, 'last_name', 'Last name must contain letters only.');
            ok = false;
        }

        ok = validateRequired($form, 'email', email, 'Email is required.') && ok;
        if (String(email || '').trim() && !isEmail(email)) {
            setFieldError($form, 'email', 'Please enter a valid email address.');
            ok = false;
        }

        ok = validateRequired($form, 'address', address, 'Address is required.') && ok;

        ok = validateRequired($form, 'contact_no', contactNo, 'Phone number is required.') && ok;
        if (String(contactNo || '').trim() && !isPHPhone(contactNo)) {
            setFieldError($form, 'contact_no', 'Please use 09XXXXXXXXX or +639XXXXXXXXX');
            ok = false;
        }

        ok = validateRequired($form, 'password', password, 'Password is required.') && ok;

        ok = validateRequired($form, 'password_confirmation', passwordConfirmation, 'Please retype your password.') && ok;
        if (String(password || '') && String(passwordConfirmation || '') && String(password) !== String(passwordConfirmation)) {
            setFieldError($form, 'password_confirmation', 'Passwords do not match.');
            ok = false;
        } else {
            setFieldError($form, 'password_confirmation', null);
        }

        return ok;
    }

    function validateCheckoutForm($form) {
        const $ = get$();
        if (!$) return false;

        setFormError($form, null);

        const name = $form.find('input[name="name"]').val();
        const email = $form.find('input[name="email"]').val();
        const address = $form.find('input[name="address"]').val();
        const phone = $form.find('input[name="phone"]').val();
        const paymentMethod = $form.find('[name="payment_method"]').val();
        const paymentReference = $form.find('input[name="payment_reference"]').val();

        let ok = true;

        ok = validateRequired($form, 'name', name, 'Name is required.') && ok;
        if (String(name || '').trim() && !isName(name, { allowSpaces: true })) {
            setFieldError($form, 'name', 'Name must contain letters only.');
            ok = false;
        }

        ok = validateRequired($form, 'email', email, 'Email is required.') && ok;
        if (String(email || '').trim() && !isEmail(email)) {
            setFieldError($form, 'email', 'Please enter a valid email address.');
            ok = false;
        }

        ok = validateRequired($form, 'address', address, 'Address is required.') && ok;

        ok = validateRequired($form, 'payment_method', paymentMethod, 'Please select a payment method.') && ok;

        if (String(paymentMethod || '').trim() && String(paymentMethod) !== 'cash') {
            ok = validateRequired($form, 'payment_reference', paymentReference, 'Please enter your reference / account.') && ok;
        } else {
            setFieldError($form, 'payment_reference', null);
        }

        if (String(phone || '').trim()) {
            if (!isPHPhone(phone)) {
                setFieldError($form, 'phone', 'Please use 09XXXXXXXXX or +639XXXXXXXXX');
                ok = false;
            } else {
                setFieldError($form, 'phone', null);
            }
        } else {
            setFieldError($form, 'phone', null);
        }

        return ok;
    }

    function bind() {
        const $ = get$();
        if (!$) return;

        $(document).off('.kukijaValidation');

        // About page legacy link behavior
        $(document).on('click.kukijaValidation', '.legacy-about a[href="kukija.php"]', function (e) {
            e.preventDefault();
            try {
                const baseUrl = (window.__kukijaBaseUrl || '/').replace(/\/?$/, '/');
                if (window.history && window.history.pushState) {
                    window.history.pushState({}, '', baseUrl);
                    window.dispatchEvent(new PopStateEvent('popstate'));
                } else {
                    window.location.assign(baseUrl);
                }
            } catch {
            }
        });

        // Input filtering: phone only
        $(document).on('input.kukijaValidation', 'input[name="contact_no"], input[name="phone"]', function () {
            const v = String($(this).val() || '');
            // allow digits and leading +
            const cleaned = v.replace(/(?!^)\+/g, '').replace(/[^0-9+]/g, '').slice(0, 13);
            if (cleaned !== v) $(this).val(cleaned);
        });

        // Name fields: letters only
        $(document).on('input.kukijaValidation', 'input[name="first_name"], input[name="last_name"]', function () {
            const v = String($(this).val() || '');
            const cleaned = v.replace(/[^A-Za-z]/g, '');
            if (cleaned !== v) $(this).val(cleaned);
        });

        // Toggle password visibility (eye button)
        $(document).on('click.kukijaValidation', '[data-toggle-password]', function () {
            try {
                const selector = String($(this).attr('data-toggle-password') || '');
                if (!selector) return;
                const $input = $(selector);
                if (!$input.length) return;
                const current = String($input.attr('type') || 'password');
                $input.attr('type', current === 'password' ? 'text' : 'password');
            } catch {
            }
        });

        // Validate on input
        $(document).on('input.kukijaValidation', '#kukijaLoginForm input', function () {
            validateLoginForm($(this).closest('form'));
        });
        $(document).on('input.kukijaValidation', '#kukijaSignupForm input', function () {
            validateSignupForm($(this).closest('form'));
        });
        $(document).on('input.kukijaValidation', '#kukijaCheckoutForm input', function () {
            validateCheckoutForm($(this).closest('form'));
        });
        $(document).on('change.kukijaValidation', '#kukijaCheckoutForm select', function () {
            validateCheckoutForm($(this).closest('form'));
        });

        // Submit: login
        $(document).on('submit.kukijaValidation', '#kukijaLoginForm', async function (e) {
            e.preventDefault();

            const $form = $(this);
            if (!validateLoginForm($form)) return;

            const email = String($form.find('input[name="email"]').val() || '').trim();
            const password = String($form.find('input[name="password"]').val() || '');

            setFormError($form, null);

            try {
                const baseUrl = (window.__kukijaBaseUrl || '/').replace(/\/?$/, '/');
                const data = await new Promise((resolve, reject) => {
                    $.ajax({
                        method: 'POST',
                        url: `${baseUrl}api/auth/login`,
                        dataType: 'json',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        data: { email, password, device_name: 'storefront' },
                        success: (payload) => resolve(payload),
                        error: (xhr) => reject(xhr),
                    });
                });

                window.dispatchEvent(new CustomEvent('kukija:auth-updated', { detail: data }));
                $('#kukijaLoginModal').removeClass('active');
            } catch (xhr) {
                const payload = xhr?.responseJSON;
                const errors = payload?.errors;

                if (errors && typeof errors === 'object') {
                    if (errors.email) setFieldError($form, 'email', Array.isArray(errors.email) ? errors.email[0] : errors.email);
                    if (errors.password) setFieldError($form, 'password', Array.isArray(errors.password) ? errors.password[0] : errors.password);
                }

                setFormError($form, payload?.message || 'Login failed.');
            }
        });

        // Submit: signup
        $(document).on('submit.kukijaValidation', '#kukijaSignupForm', async function (e) {
            e.preventDefault();

            const $form = $(this);
            if (!validateSignupForm($form)) return;

            const first = String($form.find('input[name="first_name"]').val() || '').trim();
            const last = String($form.find('input[name="last_name"]').val() || '').trim();
            const email = String($form.find('input[name="email"]').val() || '').trim();
            const address = String($form.find('input[name="address"]').val() || '').trim();
            const contactNo = normalizePhone($form.find('input[name="contact_no"]').val());
            const password = String($form.find('input[name="password"]').val() || '');
            const passwordConfirmation = String($form.find('input[name="password_confirmation"]').val() || '');

            setFormError($form, null);

            try {
                const baseUrl = (window.__kukijaBaseUrl || '/').replace(/\/?$/, '/');
                const data = await new Promise((resolve, reject) => {
                    $.ajax({
                        method: 'POST',
                        url: `${baseUrl}api/auth/register`,
                        dataType: 'json',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        data: {
                            first_name: first,
                            last_name: last,
                            email,
                            address,
                            contact_no: contactNo,
                            password,
                            password_confirmation: passwordConfirmation,
                            device_name: 'storefront',
                        },
                        success: (payload) => resolve(payload),
                        error: (xhr) => reject(xhr),
                    });
                });

                window.dispatchEvent(new CustomEvent('kukija:auth-updated', { detail: data }));
                $('#kukijaSignupModal').removeClass('active');
            } catch (xhr) {
                const payload = xhr?.responseJSON;
                const errors = payload?.errors;

                if (errors && typeof errors === 'object') {
                    if (errors.first_name) setFieldError($form, 'first_name', Array.isArray(errors.first_name) ? errors.first_name[0] : errors.first_name);
                    if (errors.last_name) setFieldError($form, 'last_name', Array.isArray(errors.last_name) ? errors.last_name[0] : errors.last_name);
                    if (errors.email) setFieldError($form, 'email', Array.isArray(errors.email) ? errors.email[0] : errors.email);
                    if (errors.address) setFieldError($form, 'address', Array.isArray(errors.address) ? errors.address[0] : errors.address);
                    if (errors.contact_no) setFieldError($form, 'contact_no', Array.isArray(errors.contact_no) ? errors.contact_no[0] : errors.contact_no);
                    if (errors.password) setFieldError($form, 'password', Array.isArray(errors.password) ? errors.password[0] : errors.password);
                    if (errors.password_confirmation) {
                        setFieldError(
                            $form,
                            'password_confirmation',
                            Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : errors.password_confirmation,
                        );
                    }
                }

                setFormError($form, payload?.message || 'Sign up failed.');
            }
        });

        // Submit: checkout (gate opening the confirm modal)
        $(document).on('submit.kukijaValidation', '#kukijaCheckoutForm', function (e) {
            e.preventDefault();

            const $form = $(this);
            if (!validateCheckoutForm($form)) return;

            try {
                window.dispatchEvent(new Event('kukija:checkout-valid'));
            } catch {
            }
        });
    }

    function bootWhenReady() {
        const $ = get$();
        if (!$) {
            setTimeout(bootWhenReady, 50);
            return;
        }

        try {
            window.__kukijaBaseUrl = (import.meta && import.meta.env && import.meta.env.VITE_BASE_URL) ? import.meta.env.VITE_BASE_URL : (window.__kukijaBaseUrl || '/');
        } catch {
        }

        bind();
    }

    window.KukijaValidation = {
        validateLoginForm,
        validateSignupForm,
        validateCheckoutForm,
        bind,
    };

    bootWhenReady();
})();
