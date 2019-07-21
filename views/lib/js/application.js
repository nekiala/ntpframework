function gfile(id) {
    document.getElementById(id).click();
    return false;
}

var content, state = 0;

var _system = {
    select_n_type: function (url, param, page, div) {

        loader.start();

        if (!page) page = 1;

        if (!div) div = "content";

        $('#' + div).load(url, {id: param, page: page}, function () {
            loader.stop();
        });
    },
    select_type: function (url, change_url) {

        loader.start();

        var _url = (typeof url !== "string") ? url.href : url;

        $('#content').load(_url, function (e) {

            if (change_url) {
                window.history.pushState(e, "", _url);
            }
            loader.stop();
        });
    },
    type: {
        add: {
            initiate: function (e) {

                loader.start();

                $('#ntpModal').modal({
                    remote: (typeof e !== "string") ? e.href : e,
                    show: true,
                    keyboard: true
                });

                loader.stop();

                return false;
            }
        },
        edit: {
            initiate: function (e) {
                return _system.type.add.initiate(e);
            },
            process: function (e, url, add) {

                var form = $(e).serialize();

                $.post(e.action, form).done(function (data) {

                    if (/^[\d]+$/.test(data)) {
                        if (add) {
                            e.reset();
                        } else {
                            $('#ntpModal').modal('hide');
                        }
                        param.type.refresh_list(url);
                    } else {
                        alert('Une erreur est survenue. Veuillez réessayer.');
                    }
                });

                return false;
            }
        },
        config: {
            add: function (e, url, clas) {
                if (clas) {
                    return param.type.add.process(e, url, 1, clas);
                }
                return param.type.add.process(e, url, 1);
            }
        },
        exchange: function (url, next) {

            $.get(url).done(function (data) {

                if (/^[\d]+$/.test(data)) {
                    param.type.refresh_list(next);
                } else {
                    alert('Une erreur est survenue. Veuillez réessayer.');
                }
            });

            return false;
        }
    },
    licence: {
        activation: {
            initiate: function (u) {
                return _system.type.add.initiate(u);
            }
        }
    },
    close_modal: function () {
        $('#ntpModal').modal('hide');
    }
};

var jax = {
    post: {
        get_modal: function (url, t) {

            loader.start();
            $.post(url, {token: t}).done(function (e) {

                if (e) {

                    $('#ntpModal').modal({
                        data: e,
                        show: true,
                        keyboard: true
                    });
                }
            });

            loader.stop();
            return false;
        },
        //envoie en POST et applique un refresh sur l'élement
        send_exchange: function (url, data, elem) {
            loader.start();
            $.post(url, data).done(function (e) {

                if (e) {

                    $(elem).hide();
                }
            });
            loader.stop();
            return false;
        }
    }
};

var param = {
    type: {
        add: {
            initiate: function (e) {
                $('#ntpModal').modal({
                    remote: (typeof e !== "string") ? e.href : e,
                    show: true,
                    keyboard: true
                });
                loader.stop();
                return false;
            },
            process: function (e, url, add, clas) {

                loader.start();

                var form = $(e).serialize();

                $.post(e.action, form).done(function (data) {

                    if (/^[\d]+$/.test(data)) {
                        if (add) {
                            e.reset();
                        } else {
                            $('#ntpModal').modal('hide');
                        }
                        if (clas) {
                            param.type.refresh_list(url, clas);
                        } else {
                            param.type.refresh_list(url);
                        }
                    } else {
                        alert('Une erreur est survenue. Veuillez réessayer.');
                        loader.stop();
                    }

                });

                return false;
            },
            //process with form data
            process_w_data: function (e, url, add, clas) {

                loader.start();

                var formData = new FormData(e);

                var xhr = new XMLHttpRequest();

                xhr.open("POST", e.action);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var data = xhr.responseText;

                        if (/^[\d]+$/.test(data)) {
                            if (add) {
                                e.reset();
                            } else {
                                $('#ntpModal').modal('hide');
                            }
                            if (clas) {
                                param.type.refresh_list(url, clas);
                            } else {
                                param.type.refresh_list(url);
                            }
                        } else {
                            alert('Une erreur est survenue. Veuillez réessayer.');
                            loader.stop();
                        }
                    }
                };

                xhr.send(formData);

                return false;
            }
        },
        refresh_list: function (url, clas) {
            loader.start();
            if (!clas) {
                clas = '.table-responsive';
            }
            $(clas).load(url, function () {
                loader.stop();
            });
        },
        edit: {
            initiate: function (e) {
                return _system.type.add.initiate(e);
            },
            process: function (e, url) {

                return param.type.add.process(e, url);
            }
        },
        exchange: function (url, next) {
            loader.start();
            $.get(url).done(function (data) {

                if (/^[\d]+$/.test(data)) {
                    param.type.refresh_list(next);
                } else {
                    alert('Une erreur est survenue. Veuillez réessayer.');
                }
            });
            loader.stop();
            return false;
        },
        param: {
            config: {
                initiate: function (url) {
                    loader.start();
                    return param.type.add.initiate(url);
                }
            },
            remove: {
                initiate: function (url) {
                    //url.parentNode.parentNode.style.display = 'none';
                    return param.type.edit.initiate(url);
                },
                process: function (e, url) {

                    return param.type.add.process(e, url);
                }
            },
            edit: {
                initiate: function (url, clas) {

                    loader.start();
                    if (state === 0) {
                        if (clas) {
                            content = $(clas).html();
                        } else {
                            content = $('.col-lg-3').html();
                        }
                        state = 1;
                    }

                    if (clas) {
                        $(clas).load(url, function () {
                            loader.stop();
                        });
                    } else {
                        $('.col-lg-3').load(url, function () {
                            loader.stop();
                        });
                    }

                    return false;
                },
                process: function (e, url, clas, clascancel) {

                    loader.start();

                    var form = $(e).serialize();

                    $.post(e.action, form).done(function (data) {

                        if (/^[\d]+$/.test(data)) {

                            if (clas) {
                                param.type.refresh_list(url, clas);
                            } else {
                                param.type.refresh_list(url);
                            }

                            if (clascancel) {
                                return param.type.param.edit.cancel(clascancel);
                            }
                            return param.type.param.edit.cancel();

                        } else {
                            alert('Une erreur est survenue. Veuillez réessayer.');

                            loader.stop();
                        }
                    });

                    return false;
                },
                cancel: function (clas) {

                    if (clas) {
                        $(clas).html(content);
                        $(clas + ' form')[0].reset();
                    } else {
                        $('.col-lg-3').html(content);
                        $('.col-lg-3 form')[0].reset();
                    }

                    state = 0;
                    return false;
                }
            },
            exchange: function (e, url) {

                return param.type.add.process(e, url);
            },
            copy: {
                initiate: function (url) {
                    if (url.value) {
                        loader.start();
                        $(url).attr('disabled', 'disabled');
                        $('.dataTable').load(url.value, function () {
                            loader.stop();
                            $(url).removeAttr('disabled');
                        });
                    } else {
                        $('.dataTable').empty();
                    }
                },
                process: function (e, f, clas) {
                    loader.start();
                    var url = $('.c');
                    var form = $(e).serialize();

                    $.post($(url).attr('id'), form).done(function (e) {
                        if (e) {
                            if (clas) {
                                param.type.refresh_list(f, clas);
                            } else {
                                param.type.refresh_list(f);
                            }
                            $('#ntpModal').modal('hide');
                        } else {
                            alert('Une erreur est survenue.');
                            loader.stop();
                        }
                    });

                    return false;
                }
            },
            child: {
                edit: {
                    initiate: function (e) {

                        return e;
                    },
                    process: function (e) {
                        return e;
                    }
                },
                remove: {
                    initiate: function (e, f, g) {
                        return param.type.param.copy.process(e, f, g);
                    }
                }
            }
        }
    }
};

var loader = {

    _typeof: typeof $('.loader'),

    start: function () {

        if (loader._typeof !== 'undefined') {

            $('.loader').css('visibility', 'visible');
        }
    },
    stop: function () {

        if (loader._typeof !== 'undefined') {

            $('.loader').css('visibility', 'hidden');
        }
    }
};


var ntp = {
    //the function return a link to be loaded with select_type function
    do_get_and_load_number: function (url, div) {
        loader.start();
        $.get(url).done(function (_url) {

            if (/^[\d]+$/.test(_url)) {

                $(div).load(_url, function (e) {
                    loader.stop();
                });
            } else {
                alert('Une erreur est survenue. Veuillez réessayer.');
                loader.stop();
            }
        });
    },
    //load using POST method
    //201602 jan 1415
    do_post_load_data_to_specified_element: function (url, data, element, history) {

        loader.start();

        $(element).load(url, data, function (data) {

            if (history) {

                window.history.pushState(data, "", url);
            }
            loader.stop();
        });

        return false;
    },
    //load using POST method
    //201613 mar 1925
    do_post_set_data_to_specified_input_element: function (url, data, element) {

        loader.start();

        $.post(url, data, function (response) {

            $(element).val(response);
            loader.stop();
        });

        return false;
    },
    //load using POST method and return data
    //201628 mar 0236
    do_post_and_return_xml_data: function (url, data, elements) {

        loader.start();

        var _return = false;

        $.post(url, data, function (response) {

            loader.stop();

            var data = JSON.parse(response);

            $(elements[0]).val(data.total);
            $(elements[1]).val(data.coast);
            $(elements[2]).val(data.estimation);
            $(elements[3]).val(data.unit);
            $(elements[4]).val(data.estimation);

            //alert($(elements[4]).val());
        });

        return _return;
    },
    do_get_and_load: function (url, div) {
        loader.start();
        $.get(url).done(function (_url) {

            if (/^\/(.+)$/.test(_url)) {

                $(div).load(_url, function (e) {
                    loader.stop();
                });
            } else {
                alert('Une erreur est survenue. Veuillez réessayer.');
                loader.stop();
            }
        });
    },
    do_post_and_load: function (e, _class) {
        loader.start();

        var form = $(e).serialize();

        $.post(e.action, form).done(function (data) {

            $(_class).html(data);

            loader.stop();
        });

        return false;
    },
    do_post_form_and_load: function (e, close) {

        loader.start();

        var form = $(e).serialize();

        $.post(e.action, form).done(function (data) {

            if (/^\/(.+)$/.test(data)) {
                if (close) {
                    $('#ntpModal').modal('hide');
                }
                _system.select_type(data, 1);
            } else {
                alert('Une erreur est survenue. Veuillez réessayer.');
                loader.stop();
            }
        });

        return false;
    },
    do_post_element_and_load: function (e, values, close) {

        loader.start();

        $.post(e.href, values).done(function (data) {

            if (/^\/(.+)$/.test(data)) {
                if (close) {
                    $('#ntpModal').modal('hide');
                }
                _system.select_type(data, 1);
            } else {
                alert('Une erreur est survenue. Veuillez réessayer.');
                loader.stop();
            }
        });

        return false;
    },
    do_post_element_and_callback: function (e, values, callback_link, close) {

        loader.start();

        $.post(e.href, values).done(function (data) {

            if (/^[\d]+$/.test(data)) {
                if (close) {
                    $('#ntpModal').modal('hide');
                }
                _system.select_type(callback_link, 1);
            } else {
                alert('Une erreur est survenue. Veuillez réessayer.');
                loader.stop();
            }
        });

        return false;
    },
    do_post_form_and_load_data: function (e, element) {

        loader.start();

        var form = $(e).serialize();

        $.post(e.action, form).done(function (data) {

            $(element).html(data);
            $('#ntpModal').modal('hide');

            //window.history.pushState(data,"", e.action);

            loader.stop();
        });

        return false;
    },
    do_load_data_to_specified_element: function (url, element, history) {

        loader.start();

        $(element).load(url, function (data) {

            if (history) {

                window.history.pushState(data, "", url);
            }
            loader.stop();
        });

        return false;
    },
    //auto complete without select
    auto_complete_wt_select: function (options) {
        $(options.id).autocomplete({
            source: function (request, response) {
                $.getJSON(options.url, {query: request.term, _token: options.token}, function (data) {
                    response(data);
                });
            },
            minLength: options.min
        });
    },

    check_password: function (password1, password2) {

        if (/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{6,}$/.test(password1)) {
            if (password2 && (password1 != password2)) {
                alert("Les deux mots de passe ne sont pas identiques");
                return false;
            }

            return true;
        }

        alert("Le mot de passe ne respecte pas les règles.");

        return false;
    },

    do_history: {
        go: function(where) {
            history.go(where);
            return false;
        },
        back: function(where) {
            history.back(where);
            return false;
        }
    }
};

var shifting = {
    control: function (e, url, bool_value, element) {
        var start_ = $('#start').val(), end_ = $('#end').val()/*, uptake = $('#uptake').val()*/;

        if (/^[\d]+$/.test(start_) && /^[\d]+$/.test(end_)/* && /^[\d]+$/.test(uptake)*/) {
            if (parseFloat(start_) > parseFloat(end_) && parseFloat(start_) == 900/* && parseFloat(uptake) > 0*/) {
                return param.type.add.process(e, url, bool_value, element);
            }
            if (parseFloat(start_) < parseFloat(end_)/* && parseFloat(uptake) > 0*/) {

                return param.type.add.process(e, url, bool_value, element);
            }
        }

        return false;
    },
    uptake_control: function (e, element) {
        var period1 = $("#p1").val(), period2 = $("#p2").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    uptake_control_dynamic_one_control: function (e, element, div1, div2) {
        var period1 = $("#" + div1).val(), period2 = $("#" + div2).val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    uptake_control_dynamic_two_control: function (e, element, div1, div2) {
        var period1 = $("#" + div1).val(), period2 = $("#" + div2).val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    uptake_control1a: function (e, element) {
        var period1 = $("#p1").val(), period2 = $("#p2").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    diary_control1a: function (e, element) {
        var period1 = $("#p3").val(), period2 = $("#p4").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    diary_control1b: function (e, element) {
        var period1 = $("#p_3").val(), period2 = $("#p_4").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    diary_control1c: function (e, element) {
        var period1 = $("#p5").val(), period2 = $("#p_5").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        } else if ((period1 == "____-__-__" || period1 == "") && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    uptake_control2: function (e, element) {
        var period1 = $("#p_1").val(), period2 = $("#p_2").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        } else if ((period1 == "____-__-__" || period1 == "") && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    diary_control2: function (e, element) {
        var period1 = $("#p_1").val(), period2 = $("#p_2").val();

        if (period1 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period1) && period2 != "____-__-__" && /^[\d]{4}(-[\d]{2}){2}$/.test(period2)) {
            ntp.do_post_form_and_load_data(e, element);
        } else if (/^[\d]{4}(-[\d]{2}){2}$/.test(period1) && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        } else if ((period1 == "____-__-__" || period1 == "") && (period2 == "____-__-__" || period2 == "")) {
            ntp.do_post_form_and_load_data(e, element);
        }

        return false;
    },
    do_form_post_and_nothing: function (e) {

        var form = $(e).serialize();

        loader.start();

        //if data contain something, alert data
        $.post(e.action, form).done(function (data) {
            if (data) {
                alert(data);
            }

            loader.stop();
        });

        return false;
    }
};