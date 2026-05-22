(function () {
  // Scroll-reveal animation
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (es) => {
        es.forEach((e, i) => {
          if (e.isIntersecting) {
            setTimeout(() => e.target.classList.add('in'), (i % 4) * 90);
            io.unobserve(e.target);
          }
        });
      },
      { threshold: 0.14 }
    );
    document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
  } else {
    document.querySelectorAll('.reveal').forEach((el) => el.classList.add('in'));
  }

  // Mobile menu drawer
  const burger = document.querySelector('.burger');
  const menu = document.getElementById('primary-menu');
  if (burger && menu) {
    const close = () => {
      menu.classList.remove('is-open');
      burger.classList.remove('is-open');
      burger.setAttribute('aria-expanded', 'false');
    };
    burger.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = menu.classList.toggle('is-open');
      burger.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    // Click outside closes
    document.addEventListener('click', (e) => {
      if (!menu.contains(e.target) && e.target !== burger) close();
    });
    // Esc closes
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });
    // Resizing back up clears it
    window.addEventListener('resize', () => {
      if (window.innerWidth > 680) close();
    });
  }

  // Auto-dismiss flash messages after 5 s
  setTimeout(() => {
    document.querySelectorAll('.flash').forEach((el) => {
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 5000);
})();
