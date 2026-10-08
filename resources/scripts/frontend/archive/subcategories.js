const subcategories = document.querySelector('ul.subcategories');

if (subcategories) {
  let isDown = false;
  let startX;
  let scrollLeft;

  subcategories.addEventListener('mousedown', (e) => {
    isDown = true;
    subcategories.classList.add('scrolling');
    startX = e.pageX - subcategories.offsetLeft;
    scrollLeft = subcategories.scrollLeft;
  });

  subcategories.addEventListener('mouseleave', () => {
    isDown = false;
    subcategories.classList.remove('scrolling');
  });

  subcategories.addEventListener('mouseup', () => {
    isDown = false;
    subcategories.classList.remove('scrolling');
  });

  subcategories.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - subcategories.offsetLeft;
    const walk = (x - startX) * 2;
    subcategories.scrollLeft = scrollLeft - walk;
  });
}
