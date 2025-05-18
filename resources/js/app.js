import './bootstrap';

import Alpine from 'alpinejs';
import shiftTable from './components/shift-table-night';

window.Alpine = Alpine;
Alpine.data('shiftTable', shiftTable);

Alpine.start();
