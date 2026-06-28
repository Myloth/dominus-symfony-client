import Routing from "fos-router";
import TomSelect from "tom-select";
import '@fortawesome/fontawesome-free/css/all.css';
import 'tom-select/dist/css/tom-select.default.min.css';
import 'datatables.net-dt/css/dataTables.dataTables.min.css';
import '../styles/app.scss';

import Translator from "bazinga-translator";

if ((window as any).Routing) {
    const globalRouting = (window as any).Routing;
    Routing.setBaseUrl(globalRouting.getBaseUrl());
    Routing.setRoutes(globalRouting.getRoutes());
    Routing.setScheme(globalRouting.getScheme());
    Routing.setHost(globalRouting.getHost());
    Routing.setPort(globalRouting.getPort());
    Routing.setLocale(globalRouting.getLocale());
    if ((globalRouting as any).context_ && (globalRouting as any).context_.prefix) {
        Routing.setPrefix((globalRouting as any).context_.prefix);
    }
}


fetch('/translations/fr.json')
    .then(response => response.text())
    .then(translations => {
        console.log(translations)
        Translator.fromJSON(translations);
    })
    .catch(() => console.error('Unable to get translations'))



// Initialize Tom Select
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.select2').forEach((el) => {
        new TomSelect(el, {
            plugins: ['remove_button']
        });
    });
});