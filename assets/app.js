/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)


//import $ from 'jquery' DOESN'T WORK
const $ = require('jquery');

import 'select2';

import '@fortawesome/fontawesome-free/css/all.css';
import 'select2/dist/css/select2.min.css';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-dt/css/dataTables.dataTables.min.css';
import './styles/app.scss';

// start the Stimulus application
$(document).ready(function() {
    $('.select2').select2({});
})