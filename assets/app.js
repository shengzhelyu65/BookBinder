/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

import 'bootstrap/dist/css/bootstrap.min.css';

require('bootstrap');

require('bootstrap-icons/font/bootstrap-icons.css');

require('select2/dist/css/select2.min.css')

require('select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css')

import $ from 'jquery';

import 'select2';

$(document).ready(function () {
    $('.multiple-select-field').select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        closeOnSelect: false,
        maximumSelectionLength: 5,
    });
});