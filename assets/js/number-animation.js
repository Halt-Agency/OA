(function () {
  function initNumberAnimations() {
    var counters = document.querySelectorAll('.number-animation');
    if (!counters.length) {
      return;
    }

    var observer = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) {
          return;
        }

        var el = entry.target;
        var target = parseInt(el.textContent.trim(), 10);
        if (isNaN(target)) {
          return;
        }

        el.textContent = '';
        var duration = 2000;
        var startTime = performance.now();

        function animate(time) {
          var progress = Math.min((time - startTime) / duration, 1);
          var value = Math.floor(progress * target);
          el.textContent = value;

          if (progress < 1) {
            requestAnimationFrame(animate);
          }
        }

        requestAnimationFrame(animate);
        obs.unobserve(el);
      });
    }, { threshold: 0.4 });

    counters.forEach(function (counter) {
      observer.observe(counter);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNumberAnimations);
  } else {
    initNumberAnimations();
  }
})();
