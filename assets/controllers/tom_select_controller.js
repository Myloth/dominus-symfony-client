import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
    connect() {
        this.select = new TomSelect(this.element, {
            plugins: ['remove_button']
        });
        
        this.select.on('change', () => {
            this.element.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    disconnect() {
        if (this.select) {
            this.select.destroy();
        }
    }
}
