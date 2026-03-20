import './bootstrap';
import selectionTable from './selection';

document.addEventListener('alpine:init', () => {
    Alpine.data('selectionTable', selectionTable);
});
