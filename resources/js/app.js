import './bootstrap';

import Alpine from 'alpinejs';

// // FullCalendarのインポート
// import { Calendar } from '@fullcalendar/core';
// import dayGridPlugin from '@fullcalendar/daygrid';
// import interactionPlugin from '@fullcalendar/interaction';
// import '@fullcalendar/core/styles.css';  // 正しいCSSのインポート方法
// import '@fullcalendar/daygrid/styles.css';  // dayGridのスタイル

// // FullCalendarを設定
// document.addEventListener('DOMContentLoaded', function () {
//     let calendarEl = document.getElementById('calendar');
    
//     let calendar = new Calendar(calendarEl, {  // FullCalendarのインスタンス化
//         plugins: [ dayGridPlugin, interactionPlugin ],
//         initialView: 'dayGridMonth',
//         events: '/shifts/events', // シフトデータを取得するURLを指定
//         eventClick: function(info) {
//             alert('シフト: ' + info.event.title);
//         }
//     });

//     calendar.render();
// });

window.Alpine = Alpine;

Alpine.start();
