import './bootstrap';

import Alpine from 'alpinejs';

import shiftTableDay from './components/shift-table-day.js';
import shiftTable from './components/shift-table-night';


window.Alpine = Alpine;
Alpine.data('shiftTableDay', shiftTableDay);
Alpine.data('shiftTable', shiftTable);

Alpine.start();
