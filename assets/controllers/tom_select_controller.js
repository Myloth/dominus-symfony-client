import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    static values = {
        placeholder: { type: String, default: 'Sélectionner une ou plusieurs options...' },
    };

    connect() {
        if (this.element.tomselect) {
            return;
        }

        this.tomSelect = new TomSelect(this.element, {
            plugins: ['remove_button'],
            placeholder: this.placeholderValue,
            dropdownParent: 'body',
            hidePlaceholder: true,
            closeAfterSelect: false,
            render: {
                no_results: function (data, escape) {
                    return '<div class="no-results">Aucun résultat pour « ' + escape(data.input) + ' »</div>';
                },
            },
        });
    }

    disconnect() {
        if (this.tomSelect) {
            this.tomSelect.destroy();
            this.tomSelect = null;
        }
    }
}
