import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { initAllRichTextEditors } from './richtext-editor.js';

window.Alpine = Alpine;
// The CMS list pages (gallery, officers, facilities) drag-and-drop to reorder via
// x-crud.reorder-list, which expects window.Sortable.
window.Sortable = Sortable;
Alpine.start();

document.addEventListener('DOMContentLoaded', initAllRichTextEditors);
