export const estateinRoutes = [
  { name: 'Home', path: '/' },
  { name: 'About Us', path: '/about-us/' },
  { name: 'Properties', path: '/properties/' },
  {
    name: 'Property Details',
    path: '/properties/seaside-serenity-villa/',
  },
  { name: 'Services', path: '/services/' },
  { name: 'Contact', path: '/contact/' },
];

export function capturePageErrors(page) {
  const errors = [];

  page.on('console', (message) => {
    if (message.type() === 'error') {
      errors.push(`console: ${message.text()}`);
    }
  });

  page.on('pageerror', (error) => {
    errors.push(`pageerror: ${error.message}`);
  });

  return errors;
}

export function localPathname(baseURL, href) {
  const base = new URL(baseURL);
  const target = new URL(href, base);

  if (target.origin !== base.origin) {
    return null;
  }

  if (
    ['mailto:', 'tel:', 'javascript:'].includes(target.protocol) ||
    target.pathname.startsWith('/wp-admin/') ||
    target.pathname.startsWith('/wp-login.php')
  ) {
    return null;
  }

  target.hash = '';
  return `${target.pathname}${target.search}`;
}
