wp.domReady(() => {
  wp.blocks.registerBlockStyle('core/group', [
    {
      name: 'double-title',
      label: 'Fond Blanc',
      isDefault: false,
    },
    {
      name: 'image-mouture',
      label: 'Image Mouture',
      isDefault: false,
    },
    {
      name: 'image-coffee',
      label: 'Image Café',
      isDefault: false,
    },
    {
      name: 'shadow-card',
      label: 'Ombre portée',
      isDefault: false,
    },
    {
      name: 'custom-banner',
      label: 'Bannière',
      isDefault: false,
    },
    {
      name: 'scroll-to',
      label: 'Scroll To',
      isDefault: false,
    },
  ]);
  wp.blocks.registerBlockStyle('core/columns', [
    {
      name: 'grid-mobile-2x2',
      label: 'Mobile 2x2',
      isDefault: false,
    },
  ]);
  wp.blocks.registerBlockStyle('core/button', [
    {
      name: 'border-black',
      label: 'Bordure noir',
      isDefault: false,
    },
  ]);
  wp.blocks.registerBlockStyle('core/cover', [
    {
      name: 'deteriorated-image',
      label: 'Image détériorée',
      isDefault: false,
    },
    {
      name: 'aspect-square',
      label: 'Image carrée',
      isDefault: false,
    },
  ]);
  wp.blocks.registerBlockStyle('core/column', [
    {
      name: 'engagement-col',
      label: 'Colonne engagement',
      isDefault: false,
    },
  ]);
  wp.blocks.registerBlockStyle('core/paragraph', [
    {
      name: 'invisible-link',
      label: 'Lien invisible',
      isDefault: false,
    },
  ]);
});
