import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import LitepickerImport from 'litepicker';
import 'litepicker/dist/css/litepicker.css';
import { initAllRichTextEditors } from './richtext-editor.js';

window.Sortable = Sortable;
// litepicker ships a UMD bundle whose factory checks `typeof exports` and, finding Vite's
// CJS-interop shim, assigns itself as `exports.Litepicker` instead of `module.exports` —
// so the default import resolves to `{ Litepicker: <constructor> }`, not the constructor
// itself. Unwrap that shape so `new Litepicker(...)` in Blade views gets the real class.
window.Litepicker = LitepickerImport.Litepicker ?? LitepickerImport;
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', initAllRichTextEditors);
