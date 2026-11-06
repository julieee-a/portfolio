// Active nav highlighting on scroll
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-link');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (scrollY >= sectionTop - 180) {
            current = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
});

// Basic form validation
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', e => {
        const empty = [...form.querySelectorAll('[required]')].find(f => !f.value.trim());
        if (empty) {
            e.preventDefault();
            empty.focus();
        }
    });
}

// Project lightbox
const modal = document.getElementById('project-modal');
const modalImg = document.getElementById('modal-img');
const modalTitle = document.getElementById('modal-title');
const modalDesc = document.getElementById('modal-desc');
const modalStack = document.getElementById('modal-stack');

function openModal(btn) {
    modalImg.src = btn.dataset.src;
    modalImg.alt = btn.dataset.title;
    modalTitle.textContent = btn.dataset.title;
    modalDesc.textContent = btn.dataset.desc;
    modalStack.textContent = btn.dataset.stack;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modal.hidden = true;
    modalImg.src = '';
    document.body.style.overflow = '';
}

document.querySelectorAll('.project-view-btn').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn));
});

modal?.querySelectorAll('[data-close]').forEach(el => {
    el.addEventListener('click', closeModal);
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal && !modal.hidden) {
        closeModal();
    }
});
