import Alpine from 'alpinejs';
import { initAllRichTextEditors } from './richtext-editor.js';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', initAllRichTextEditors);
