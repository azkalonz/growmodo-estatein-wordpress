(function () {
  const gallery = document.querySelector('[data-estatein-property-gallery]');

  if (!gallery || !window.wp?.media) {
    return;
  }

  const renderEmptyPreview = (slot) => {
    const preview = slot.querySelector('[data-estatein-gallery-preview]');
    const message = document.createElement('span');

    message.className = 'description';
    message.textContent = estateinPropertyGallery.emptyText;
    preview.replaceChildren(message);
  };

  gallery.addEventListener('click', (event) => {
    const selectButton = event.target.closest('[data-estatein-gallery-select]');
    const removeButton = event.target.closest('[data-estatein-gallery-remove]');
    const button = selectButton || removeButton;

    if (!button) {
      return;
    }

    event.preventDefault();

    const slot = button.closest('[data-estatein-gallery-slot]');
    const input = slot.querySelector('[data-estatein-gallery-input]');
    const preview = slot.querySelector('[data-estatein-gallery-preview]');

    if (removeButton) {
      input.value = '';
      removeButton.hidden = true;
      renderEmptyPreview(slot);
      slot.querySelector('[data-estatein-gallery-select]').focus();
      return;
    }

    const frame = wp.media({
      title: estateinPropertyGallery.dialogTitle,
      button: { text: estateinPropertyGallery.buttonText },
      library: { type: 'image' },
      multiple: false,
    });

    frame.on('select', () => {
      const attachment = frame.state().get('selection').first().toJSON();
      const image = document.createElement('img');
      const thumbnail = attachment.sizes?.thumbnail;

      input.value = attachment.id;
      image.src = thumbnail?.url || attachment.url;
      image.alt = attachment.alt || attachment.title || estateinPropertyGallery.imageAlt;
      image.width = thumbnail?.width || 150;
      image.height = thumbnail?.height || 150;
      preview.replaceChildren(image);
      removeButton.hidden = false;
    });

    frame.open();
  });
})();
