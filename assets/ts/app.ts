import Routing from "fos-router";
import routes from '../../public/routes/fos_js_routes.json';
import "select2";
import '@fortawesome/fontawesome-free/css/all.css';
import 'select2/dist/css/select2.min.css';
import 'datatables.net-dt/css/dataTables.dataTables.min.css';
import '../styles/app.scss';
import $ from "jquery";
import Translator from "bazinga-translator";
import translations from '../../public/translations/fr.json';

Routing.setRoutingData(routes);
fetch('/translations/fr.json')
    .then(response => response.text())
    .then(translations => {
        console.log(translations)
        Translator.fromJSON(translations);
    })
    .catch(() => console.error('Unable to get translations'))



// start the Stimulus application
$(document).ready(function() {
    $('.select2').select2({});
})