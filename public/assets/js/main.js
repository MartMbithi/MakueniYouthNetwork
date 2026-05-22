(function () {
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

  const burger = document.querySelector('.burger');
  const menu = document.querySelector('.menu');
  if (burger && menu) {
    burger.addEventListener('click', () => {
      menu.classList.toggle('is-open');
    });
  }

  setTimeout(() => {
    document.querySelectorAll('.flash').forEach((el) => {
      el.style.transition = 'opacity .4s ease';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    });
  }, 5000);
})();
