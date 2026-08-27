import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

const COMMANDS = {
    bold: (chain) => chain.toggleBold(),
    italic: (chain) => chain.toggleItalic(),
    underline: (chain) => chain.toggleUnderline(),
    strike: (chain) => chain.toggleStrike(),
    h2: (chain) => chain.toggleHeading({ level: 2 }),
    h3: (chain) => chain.toggleHeading({ level: 3 }),
    bulletList: (chain) => chain.toggleBulletList(),
    orderedList: (chain) => chain.toggleOrderedList(),
    blockquote: (chain) => chain.toggleBlockquote(),
};

const ACTIVE_CHECK = {
    bold: (editor) => editor.isActive('bold'),
    italic: (editor) => editor.isActive('italic'),
    underline: (editor) => editor.isActive('underline'),
    strike: (editor) => editor.isActive('strike'),
    h2: (editor) => editor.isActive('heading', { level: 2 }),
    h3: (editor) => editor.isActive('heading', { level: 3 }),
    bulletList: (editor) => editor.isActive('bulletList'),
    orderedList: (editor) => editor.isActive('orderedList'),
    blockquote: (editor) => editor.isActive('blockquote'),
    link: (editor) => editor.isActive('link'),
};

/**
 * Mounts one Tiptap instance inside `root` (an element carrying [data-richtext-root]).
 * Keeps `[data-richtext-input]` (a hidden textarea, the field the form actually submits)
 * in sync with the editor's HTML on every change, so validation/@error/old() on the Blade
 * side never need to know the field is rich text.
 */
export function initRichTextEditor(root) {
    const mount = root.querySelector('[data-richtext-mount]');
    const input = root.querySelector('[data-richtext-input]');
    const toolbar = root.querySelector('[data-richtext-toolbar]');

    if (! mount || ! input) {
        return null;
    }

    const editor = new Editor({
        element: mount,
        extensions: [
            StarterKit.configure({ heading: { levels: [2, 3] } }),
            Link.configure({ openOnClick: false, autolink: true }),
        ],
        content: input.value,
        onUpdate: ({ editor }) => {
            input.value = editor.getHTML();
        },
        onTransaction: () => updateToolbarState(),
    });

    function updateToolbarState() {
        if (! toolbar) return;
        toolbar.querySelectorAll('[data-command]').forEach((button) => {
            const isActive = ACTIVE_CHECK[button.dataset.command]?.(editor) ?? false;
            button.classList.toggle('is-active', isActive);
        });
    }

    if (toolbar) {
        toolbar.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('click', () => {
                const apply = COMMANDS[button.dataset.command];
                if (! apply) return;
                apply(editor.chain().focus()).run();
            });
        });

        const linkButton = toolbar.querySelector('[data-command="link"]');
        if (linkButton) {
            linkButton.addEventListener('click', () => {
                const previousUrl = editor.getAttributes('link').href ?? '';
                const url = window.prompt('Tautan (URL):', previousUrl);

                if (url === null) return;

                if (url === '') {
                    editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    return;
                }

                editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
            });
        }
    }

    updateToolbarState();

    return editor;
}

export function initAllRichTextEditors() {
    document.querySelectorAll('[data-richtext-root]').forEach((root) => initRichTextEditor(root));
}
