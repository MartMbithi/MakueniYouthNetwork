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

// Advocacy navigation and accessibility controls.
document.addEventListener('DOMContentLoaded', function () {
  var dropdowns = Array.prototype.slice.call(document.querySelectorAll('.menu-dropdown'));
  document.querySelectorAll('.menu-dropdown-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (event) {
      event.stopPropagation();
      var item = toggle.closest('.menu-dropdown');
      dropdowns.forEach(function (other) {
        if (other !== item) {
          other.classList.remove('open');
          var otherToggle = other.querySelector('.menu-dropdown-toggle');
          if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
        }
      });
      var open = item.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  document.addEventListener('click', function (event) {
    dropdowns.forEach(function (item) {
      if (!item.contains(event.target)) {
        item.classList.remove('open');
        var toggle = item.querySelector('.menu-dropdown-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
    });
  });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      dropdowns.forEach(function (item) {
        item.classList.remove('open');
        var toggle = item.querySelector('.menu-dropdown-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      });
    }
  });

});

