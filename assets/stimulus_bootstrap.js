import { startStimulusApp } from '@symfony/stimulus-bundle';
import TomSelectController from './controllers/tom_select_controller.js';

const app = startStimulusApp();
app.register('tom-select', TomSelectController);

export { app };
