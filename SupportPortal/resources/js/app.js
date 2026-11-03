import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import '../css/app.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('write-reply-button');
    const textarea = document.getElementById('reply-message');

    if (!button || !textarea) {
        return;
    }

    button.addEventListener('click', () => {
        if (textarea.disabled) {
            return;
        }

        textarea.scrollIntoView({
            block: 'center',
            behavior: 'instant'
        });

        textarea.focus({
            preventScroll: true
        });
    });
});
