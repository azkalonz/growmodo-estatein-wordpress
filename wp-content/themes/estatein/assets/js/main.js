const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const focusableSelector = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled]):not([type="hidden"])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(',');

function safeSessionGet(key) {
  try {
    return window.sessionStorage.getItem(key);
  } catch {
    return null;
  }
}

function safeSessionSet(key, value) {
  try {
    window.sessionStorage.setItem(key, value);
  } catch {
    // Storage can be unavailable in privacy modes; dismissal still works.
  }
}

function setupAnnouncement() {
  const announcement = document.querySelector('[data-announcement]');
  const close = announcement?.querySelector('[data-announcement-close]');
  if (!announcement || !close) return;

  if (safeSessionGet('estatein-announcement-dismissed') === '1') {
    announcement.hidden = true;
    return;
  }

  close.addEventListener('click', () => {
    announcement.classList.add('is-dismissed');
    safeSessionSet('estatein-announcement-dismissed', '1');
    window.setTimeout(() => {
      announcement.hidden = true;
    }, reducedMotion.matches ? 0 : 320);
  });
}

function setupNavigation() {
  const toggle = document.querySelector('[data-menu-toggle]');
  const navigation = document.querySelector('[data-primary-navigation]');
  if (!toggle || !navigation) return;

  let previousFocus = null;
  const desktop = window.matchMedia('(min-width: 768px)');

  const setOpen = (open, returnFocus = false) => {
    toggle.setAttribute('aria-expanded', String(open));
    navigation.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open && !desktop.matches);

    if (open) {
      previousFocus = document.activeElement;
      window.requestAnimationFrame(() => navigation.querySelector(focusableSelector)?.focus());
    } else if (returnFocus && previousFocus instanceof HTMLElement) {
      previousFocus.focus();
    }
  };

  toggle.addEventListener('click', () => {
    setOpen(toggle.getAttribute('aria-expanded') !== 'true', true);
  });

  navigation.addEventListener('click', (event) => {
    if (event.target.closest('a')) setOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      setOpen(false, true);
      return;
    }

    if (event.key !== 'Tab' || toggle.getAttribute('aria-expanded') !== 'true' || desktop.matches) return;
    const focusable = [...navigation.querySelectorAll(focusableSelector)];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable.at(-1);
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  document.addEventListener('pointerdown', (event) => {
    if (toggle.getAttribute('aria-expanded') !== 'true') return;
    if (!navigation.contains(event.target) && !toggle.contains(event.target)) setOpen(false);
  });

  desktop.addEventListener('change', () => setOpen(false));
}

function setupRails() {
  document.querySelectorAll('[data-rail-controls]').forEach((controls) => {
    const id = controls.dataset.railControls;
    const rail = document.getElementById(id);
    const previous = controls.querySelector('[data-rail-previous]');
    const next = controls.querySelector('[data-rail-next]');
    const current = controls.querySelector('[data-rail-current]');
    if (!rail || !previous || !next) return;

    const cards = [...rail.children].filter((child) => child instanceof HTMLElement);
    if (!cards.length) return;

    let scheduled = false;
    const getIndex = () => {
      const railLeft = rail.getBoundingClientRect().left;
      let best = 0;
      let distance = Number.POSITIVE_INFINITY;
      cards.forEach((card, index) => {
        const candidate = Math.abs(card.getBoundingClientRect().left - railLeft);
        if (candidate < distance) {
          distance = candidate;
          best = index;
        }
      });
      return best;
    };

    const update = () => {
      scheduled = false;
      const index = getIndex();
      if (current) current.textContent = String(index + 1).padStart(2, '0');
      previous.disabled = index === 0;
      next.disabled = index >= cards.length - 1 || rail.scrollWidth <= rail.clientWidth + 2;
    };

    const move = (direction) => {
      const index = Math.max(0, Math.min(cards.length - 1, getIndex() + direction));
      rail.scrollTo({
        left: cards[index].offsetLeft - rail.offsetLeft,
        behavior: reducedMotion.matches ? 'auto' : 'smooth',
      });
    };

    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    rail.addEventListener('scroll', () => {
      if (!scheduled) {
        scheduled = true;
        window.requestAnimationFrame(update);
      }
    }, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  });
}

function setupAccordions() {
  document.querySelectorAll('[data-accordion]').forEach((accordion) => {
    const triggers = [...accordion.querySelectorAll('[data-accordion-trigger]')];

    const setExpanded = (trigger, expanded) => {
      const panel = document.getElementById(trigger.getAttribute('aria-controls'));
      trigger.setAttribute('aria-expanded', String(expanded));
      if (panel) panel.hidden = !expanded;
    };

    triggers.forEach((trigger, index) => {
      setExpanded(trigger, index === 0);
      trigger.addEventListener('click', () => {
        const shouldExpand = trigger.getAttribute('aria-expanded') !== 'true';
        triggers.forEach((candidate) => {
          if (candidate !== trigger) setExpanded(candidate, false);
        });
        setExpanded(trigger, shouldExpand);
      });
    });
  });
}

function setupPropertyGalleries() {
  document.querySelectorAll('[data-property-gallery]').forEach((gallery) => {
    const dialog = gallery.querySelector('[data-gallery-dialog]');
    const data = gallery.querySelector('[data-gallery-images]');
    const stageImage = gallery.querySelector('[data-gallery-dialog-image]');
    const current = gallery.querySelector('[data-gallery-current]');
    const thumbs = gallery.querySelector('.property-gallery__thumbs');
    if (!dialog || !data || !stageImage) return;

    let images = [];
    let index = 0;
    let opener = null;
    try {
      images = JSON.parse(data.textContent || '[]');
    } catch {
      images = [];
    }
    if (!images.length) return;

    const render = () => {
      const image = images[index];
      stageImage.src = image.url;
      stageImage.alt = image.alt || '';
      if (current) current.textContent = String(index + 1).padStart(2, '0');
    };

    const open = (newIndex, trigger) => {
      index = Math.max(0, Math.min(images.length - 1, newIndex));
      opener = trigger;
      render();
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else dialog.setAttribute('open', '');
      window.requestAnimationFrame(() => dialog.querySelector('form[method="dialog"] button')?.focus());
    };

    gallery.querySelectorAll('[data-gallery-open]').forEach((trigger) => {
      trigger.addEventListener('click', () => open(Number.parseInt(trigger.dataset.galleryIndex || '0', 10), trigger));
    });
    gallery.querySelector('[data-gallery-previous]')?.addEventListener('click', () => {
      index = (index - 1 + images.length) % images.length;
      render();
    });
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => {
      index = (index + 1) % images.length;
      render();
    });
    gallery.querySelector('[data-gallery-strip-previous]')?.addEventListener('click', () => {
      thumbs?.scrollBy({ left: -Math.max(120, thumbs.clientWidth * .7), behavior: reducedMotion.matches ? 'auto' : 'smooth' });
    });
    gallery.querySelector('[data-gallery-strip-next]')?.addEventListener('click', () => {
      thumbs?.scrollBy({ left: Math.max(120, thumbs.clientWidth * .7), behavior: reducedMotion.matches ? 'auto' : 'smooth' });
    });
    dialog.addEventListener('click', (event) => {
      const bounds = dialog.getBoundingClientRect();
      const outside = event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom;
      if (outside) dialog.close();
    });
    dialog.addEventListener('close', () => {
      if (opener instanceof HTMLElement) opener.focus();
    });
  });
}

function setupOfficeFilters() {
  document.querySelectorAll('[data-office-filters]').forEach((toolbar) => {
    const grid = toolbar.parentElement?.querySelector('[data-office-grid]');
    if (!grid) return;
    const buttons = [...toolbar.querySelectorAll('[data-office-filter]')];
    const offices = [...grid.querySelectorAll('[data-office-type]')];

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const filter = button.dataset.officeFilter;
        buttons.forEach((candidate) => {
          const active = candidate === button;
          candidate.classList.toggle('is-active', active);
          candidate.setAttribute('aria-pressed', String(active));
        });
        offices.forEach((office) => {
          office.hidden = filter !== 'all' && office.dataset.officeType !== filter;
        });
      });
    });
  });
}

function setupFormValidation() {
  const messageFor = (control) => {
    if (control.validity.valueMissing) return control.type === 'checkbox' ? 'Please confirm the terms to continue.' : 'This field is required.';
    if (control.validity.typeMismatch) return 'Enter a valid email address.';
    if (control.validity.tooShort) return `Enter at least ${control.minLength} characters.`;
    if (control.validity.rangeUnderflow) return `Enter a value of ${control.min} or more.`;
    return 'Review this field and try again.';
  };

  document.querySelectorAll('[data-validate-form]').forEach((form) => {
    const controls = [...form.querySelectorAll('input, select, textarea')].filter((control) => control.willValidate);

    const validate = (control) => {
      const field = control.closest('.field, .checkbox-field');
      const error = field?.querySelector('.field__error');
      const valid = control.checkValidity();
      control.setAttribute('aria-invalid', String(!valid));
      if (error) error.textContent = valid ? '' : messageFor(control);
      return valid;
    };

    controls.forEach((control) => {
      control.addEventListener('blur', () => validate(control));
      control.addEventListener('input', () => {
        if (control.getAttribute('aria-invalid') === 'true') validate(control);
      });
      control.addEventListener('change', () => {
        if (control.getAttribute('aria-invalid') === 'true') validate(control);
      });
    });

    form.addEventListener('submit', (event) => {
      const invalid = controls.filter((control) => !validate(control));
      if (invalid.length) {
        event.preventDefault();
        invalid[0].focus();
        return;
      }

      form.setAttribute('aria-busy', 'true');
      const submit = form.querySelector('[type="submit"]');
      if (submit) {
        submit.disabled = true;
        submit.dataset.originalText = submit.textContent;
        submit.textContent = 'Sending…';
      }
    });
  });

  window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-validate-form]').forEach((form) => {
      form.removeAttribute('aria-busy');
      const submit = form.querySelector('[type="submit"]');
      if (submit) {
        submit.disabled = false;
        if (submit.dataset.originalText) submit.textContent = submit.dataset.originalText;
      }
    });
  });
}

function setupFormStatus() {
  const status = document.querySelector('[data-form-status]');
  if (!status) return;
  window.requestAnimationFrame(() => {
    status.focus({ preventScroll: true });
    status.scrollIntoView({ behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'center' });
  });
}

setupAnnouncement();
setupNavigation();
setupRails();
setupAccordions();
setupPropertyGalleries();
setupOfficeFilters();
setupFormValidation();
setupFormStatus();
