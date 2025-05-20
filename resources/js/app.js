import './bootstrap';

import Alpine from 'alpinejs';

import shiftTableDay from './components/shift-table-day.js';
import shiftTable from './components/shift-table-night';
import shiftFilter from './components/shift-filter';


window.Alpine = Alpine;
Alpine.data('shiftTableDay', shiftTableDay);
Alpine.data('shiftTable', shiftTable);
Alpine.data('shiftFilter', shiftFilter);

Alpine.start();
